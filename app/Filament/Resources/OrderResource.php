<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\User;
use App\Models\Employee;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItems;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Enums\RolesEnum;
use Filament\Facades\Filament;
use Filament\Tables\Columns\ImageColumn;
use Filament\Infolists;
use Filament\Infolists\Infolist;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Order Management';
    
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')
                    ->placeholder('ORD-#1234')
                    ->prefixActions([
                        Forms\Components\Actions\Action::make('generate')
                            ->label('Generate')
                            ->icon('heroicon-o-arrow-path')
                            ->color('success')
                            ->action(function (callable $set) {
                                $set('order_number', 'ORD-#' . random_int(1000, 9999));
                            })
                            ->visible(fn (string $context): bool => $context !== 'edit'),
                    ])
                    ->disabledOn('edit')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('order_date')
                    ->required()
                    ->default(now())
                    ->maxDate(now()),
                Forms\Components\Select::make('status')
                    ->default('pending')
                    ->options([
                        'pending' => 'Pending',
                        'claimed' => 'Claimed',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Forms\Components\Textarea::make('notes') 
                    ->maxLength(65535),
                Forms\Components\Select::make('employee_id')
                    ->label('Assign Employee')
                    ->options(function ( $state ) {
                        // Check if employees exist, if so, pluck from employees
                        if (Employee::find($state)) {
                            return Employee::pluck('name', 'id');
                        }
                        // Otherwise, pluck from users
                            return User::whereHas('roles', function ($query) {
                                $query->where('name', RolesEnum::Employee->value);
                            })->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Find the user by ID and create an employee if it doesn't exist
                        // and assign the employee ID to the order
                        if ($state && $employee = Employee::where('user_id', $state)->first()) {
                            $set('employee_id', $employee->id);
                        } 
                        else if ($user = User::find($state)) {
                            $employee = Employee::where('user_id', $user->id)->first();
                            if (!$employee) {
                                $employee = Employee::create([
                                    'user_id' => $user->id,
                                    'name' => $user->name,
                                ]);
                            }
                            $set('employee_id', $employee->id);
                        } 
                        else {
                            $set('employee_id', null);
                        }
                    }),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->live(true)
                            ->required()
                            ->afterStateUpdated(function($state, callable $set){
                                $set('slug', Str::slug($state));
                        }),
                        Forms\Components\TextInput::make('slug')
                            ->required(),
                        Forms\Components\Select::make('parent_id')
                            ->options(fn (Forms\Get $get) => Category::where('parent_id', $get('category_id'))
                            ->pluck('name', 'id'))
                            ->label('Parent Category')
                            ->searchable(),                        
                    ])->required(),                  
            ]);
    }

    public static function table(Table $table): Table
    {
        $table->defaultSort('order_date', 'desc');
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        // danger, gray, info, success or warning
                        'pending' => 'warning',
                        'claimed' => 'gray',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(function (string $state) {
                        return ucfirst($state);
                    }),
                Tables\Columns\TextColumn::make('employee.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable(),
                ImageColumn::make('images')
                        ->getStateUsing(function ($record) {
                            return OrderItems::where('order_id', $record->id)->pluck('upload');
                        })
                        ->default('https://fakeimg.pl/600x400?text=No+Image&font=bebas')
                        ->size(40) // Adjusted size
                        ->circular()
                        ->stacked()
                        ->limit(3)
                        ->limitedRemainingText(),
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
                tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
                                Infolists\Components\TextEntry::make('employee.name')
                                    ->label('Assigned Employee'),
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
            RelationManagers\OrderImageRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();
        return $user && $user instanceof User && $user->hasRole(RolesEnum::Admin);
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
