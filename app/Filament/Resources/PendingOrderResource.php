<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingOrderResource\Pages;
use App\Filament\Resources\PendingOrderResource\RelationManagers;
use App\Models\User;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItems;
use App\Enums\RolesEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Infolists;
use Filament\Infolists\Infolist;


class PendingOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Order Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Active Orders';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')
            ->whereNull('employee_id')
            ->count();
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RolesEnum::Employee);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    protected static function getTableQuery()
    {
        $user = Auth::user();
        $employeeId = Auth::user()->employee->id ?? null;
        if ($user && $employeeId) {
            return Order::query()
                ->whereNull('employee_id')
                ->where('status', 'pending');
        }
        return Order::query()->whereRaw('1 = 0');
    }

    public static function table(Table $table): Table
    {
        $table->defaultSort('order_date', 'desc');
        return $table
            ->query(
                self::getTableQuery()
            )
            ->columns([
                ImageColumn::make('images')
                        ->getStateUsing(function ($record) {
                            return OrderItems::where('order_id', $record->id)->pluck('upload');
                        })
                        ->default('https://fakeimg.pl/600x400?text=No+Image&font=bebas')
                        ->size(40)
                        ->circular()
                        ->stacked()
                        ->limit(3)
                        ->limitedRemainingText(),
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'claimed' => 'gray',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(function (string $state) {
                        return ucfirst($state);
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                    Tables\Actions\ViewAction::make(),
                    // Claim Order action
                    Tables\Actions\Action::make('Claim Order')
                        ->action(function ($record) {
                            // Update the order's employee_id to the current user's employee ID
                            $record->employee_id = Auth::user()->employee->id;
                            $record->status = 'claimed'; // Update the status to 'claimed'
                            $record->save();
                            // Send a notification to all admin user about the order claim
                            Notification::make()
                                ->title('Order Claimed')
                                ->body('Order "' . $record->order_number . '" has been claimed by ' . Auth::user()->name)
                                ->icon('heroicon-o-clipboard-document-check')
                                ->broadcast(User::findAdmin())
                                ->warning()
                                ->sendToDatabase(User::findAdmin(), isEventDispatched: true);
                            // Optionally, you can redirect the user or show a success message
                            Notification::make()
                                ->title('Order Claimed')
                                ->body('You have successfully claimed the order "' . $record->order_number . '"')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->success()
                                ->broadcast(Auth::user())
                                ->sendToDatabase(Auth::user(), isEventDispatched: true);

                        })
                        ->requiresConfirmation()
                        ->color('warning')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->modalHeading(fn ($record): string => 'Confirm Claim for Order "' . $record->order_number . '"')
                        ->modalSubheading('Are you sure you want to claim this order?')
                        ->modalButton('Yes, Claim')
                        ->modalWidth('lg')
                        ->hidden(fn ($record): bool => $record->status !== 'pending' || $record->employee_id !== null),                 
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('Claim Selected Orders')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->employee_id = Auth::user()->employee->id;
                                $record->status = 'claimed';
                                $record->save();
                            }
                            // Send a notification to all admin user about the order claim
                            Notification::make()
                                ->title('Orders Claimed')
                                ->body('Orders have been claimed by ' . Auth::user()->name)
                                ->icon('heroicon-o-clipboard-document-check')
                                ->broadcast(User::findAdmin())
                                ->sendToDatabase(User::findAdmin(), isEventDispatched: true);
                            // Optionally, send notification to the user or show a success message
                            Notification::make()
                                ->title('Orders Claimed')
                                ->body('You have successfully claimed the selected orders')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->success()
                                ->broadcast(Auth::user())
                                ->sendToDatabase(Auth::user(), isEventDispatched: true);
                        })
                        ->requiresConfirmation()
                        ->color('warning')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->modalHeading('Confirm Claim for Selected Orders')
                        ->modalDescription('Are you sure you want to claim these orders?')
                        ->modalWidth('lg')
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Split::make([
                        Infolists\Components\Section::make([
                            Infolists\Components\Grid::make(2)->schema([
                                Infolists\Components\TextEntry::make('order_number')
                                    ->label('Order Number'),
                                Infolists\Components\TextEntry::make('order_date')
                                    ->label('Order Date')
                                    ->date(),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn ($state) => ucfirst($state))
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'claimed' => 'gray',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                    }),
                                Infolists\Components\TextEntry::make('category.name')
                                    ->label('Category'),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes')
                                    ->columnSpanFull(),
                        ]),
                    ]),
                    Infolists\Components\Section::make([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])->grow(false),
                ])->columnSpan(2),
                Infolists\Components\Section::make('Order Images')
                        ->description('Displays the images associated with the order')
                        ->schema([
                            Infolists\Components\ImageEntry::make('order_items')
                                ->label(false)
                                ->getStateUsing(fn ($record) => OrderItems::where('order_id', $record->id)->pluck('upload'))
                                ->default('https://fakeimg.pl/600x400?text=No+Image&font=bebas')
                                ->size(100) // Adjusted size
                                ->circular()
                                ->columnSpanFull(),
                ])->columnSpanFull()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendingOrders::route('/'),
            'create' => Pages\CreatePendingOrder::route('/create'),
            'edit' => Pages\EditPendingOrder::route('/{record}/edit'),
        ];
    }
}
