<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeOrderResource\Pages;
use App\Filament\Resources\EmployeeOrderResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Components;
use Filament\Forms\Components\Card;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\User;
use App\Enums\RolesEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\ImageColumn;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;


class EmployeeOrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'Order Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'My Orders';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $slug = 'my-order';
    protected static ?string $recordTitleAttribute = 'order_number';
    protected static ?string $label = 'My Order';
    protected static ?string $pluralLabel = 'My Orders';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')
        ->where('employee_id', Auth::user()->employee->id)
        ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RolesEnum::Employee);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Order Number')
                            ->disabled(),
                        Forms\Components\TextInput::make('order_date')
                            ->label('Order Date')
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->formatStateUsing(fn ($state) => ucfirst($state))
                            ->disabled(),
                        Forms\Components\TextInput::make('category_id')
                            ->label('Category')
                            ->formatStateUsing(fn ($state) => Category::find($state)->name ?? 'Unknown')
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
            ]);
    }
    
    protected static function getTableQuery()
    {
        $user = Auth::user();
        $employeeId = Auth::user()->employee->id ?? null;
        if ($user && $employeeId) {
            return Order::query()
                ->where('employee_id', $employeeId);
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
                tables\Filters\Filter::make('pending')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending'))
                    ->label('Pending'),
                tables\Filters\Filter::make('claimed')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'claimed'))
                    ->label('Claimed'),
                tables\Filters\Filter::make('processing')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'processing'))
                    ->label('Processing'),
                tables\Filters\Filter::make('completed')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'completed'))
                    ->label('Completed'),
                tables\Filters\Filter::make('cancelled')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'cancelled'))
                    ->label('cancelled'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    // Claim Order action
                    Tables\Actions\Action::make('Claim Order')
                        ->action(function ($record) {
                            $record->update(['status' => 'claimed']);
                            // Send a notification to all admin user about the order claim
                            Notification::make()
                                ->title('Order Claimed')
                                ->body('Order "' . $record->order_number . '" has been claimed by ' . Auth::user()->name)
                                ->icon('heroicon-o-clipboard-document-check')
                                ->warning()
                                ->sendToDatabase(User::findAdmin(), isEventDispatched: true);
                        })
                        ->requiresConfirmation()
                        ->color('warning')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->modalHeading(fn ($record): string => 'Confirm Claim for Order "' . $record->order_number . '"')
                        ->modalSubheading('Are you sure you want to claim this order?')
                        ->modalButton('Yes, Claim')
                        ->modalWidth('lg')
                        ->hidden(fn ($record): bool => $record->status !== 'pending'),
                    // Mark as Processing action
                    Tables\Actions\Action::make('Mark as Processing')
                        ->action(function ($record) {
                            $record->update(['status' => 'processing']);
                            // Send a notification to all admin user about the order processing
                            Notification::make()
                                ->title('Order Processing')
                                ->body('Order "' . $record->order_number . '" is now being processed by ' . Auth::user()->name)
                                ->icon('heroicon-o-clipboard-document-check')
                                ->info()
                                ->sendToDatabase(User::findAdmin(), isEventDispatched: true);
                        })
                        ->requiresConfirmation()
                        ->color('info')
                        ->icon('heroicon-o-arrow-right-start-on-rectangle')
                        ->modalHeading(fn ($record): string => 'Confirm Processing for Order "' . $record->order_number . '"')
                        ->modalSubheading('Are you sure you want to process this order?')
                        ->modalButton('Yes, Process')
                        ->modalWidth('lg')
                        ->hidden(fn ($record): bool => $record->status !== 'claimed'),
                    // Mark as Completed action
                    Tables\Actions\Action::make('Mark as Completed')
                        ->action(function ($record) {
                            $record->update(['status' => 'completed']);
                            // Send a notification to all admin user about the order completion
                            Notification::make()
                                ->title('Order Completed')
                                ->body('Order "' . $record->order_number . '" has been completed by ' . Auth::user()->name)
                                ->icon('heroicon-o-clipboard-document-check')
                                ->success()
                                ->sendToDatabase(User::findAdmin(), isEventDispatched: true);
                        })
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->modalHeading(fn ($record): string => 'Confirm Completed for Order "' . $record->order_number . '"')
                        ->modalSubheading('Are you sure you want to complete this order?')
                        ->modalButton('Yes, Complete')
                        ->modalWidth('lg')
                        ->hidden(fn ($record): bool => $record->status !== 'processing'),
                    // Cancel Order action
                    Tables\Actions\Action::make('Cancel Order')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($record) {
                            $record->update(['status' => 'cancelled']);
                            // Send a notification to all admin user about the order cancellation
                            Notification::make()
                                ->title('Order Cancelled')
                                ->body('Order "' . $record->order_number . '" has been cancelled by ' . Auth::user()->name)
                                ->icon('heroicon-o-x-circle')
                                ->danger()
                                ->sendToDatabase(User::findAdmin(), isEventDispatched: true);
                        })
                        ->requiresConfirmation()
                        ->color('danger')
                        ->modalHeading(fn ($record): string => 'Confirm Cancel for Order "' . $record->order_number . '"')
                        ->modalSubheading('Are you sure you want to cancel this order?')
                        ->modalButton('Yes, Cancel')
                        ->modalWidth('lg')
                        ->hidden(fn ($record): bool => $record->status !== 'pending'),
                ])
            ])
            ->bulkActions([
                //
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
                ])->columnSpanFull(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeOrders::route('/'),
            'create' => Pages\CreateEmployeeOrder::route('/create'),
            'edit' => Pages\EditEmployeeOrder::route('/{record}/edit'),
        ];
    }
}
