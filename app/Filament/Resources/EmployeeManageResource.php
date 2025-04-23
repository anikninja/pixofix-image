<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeManageResource\Pages;
use App\Filament\Resources\EmployeeManageResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use App\Enums\RolesEnum;
use App\Models\User;

class EmployeeManageResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'User Management';
    
    protected static ?string $navigationLabel = 'Employee management';
    
    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();
        return $user && $user instanceof User && $user->hasRole(RolesEnum::Admin);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('name')
                    ->label('Select User')
                    ->live(true)
                    ->options(function () {
                        return User::whereHas('roles', function (Builder $query) {
                            $query->where('name', RolesEnum::User);
                        })->get()->mapWithKeys(function ($user) {
                            return [$user->id => $user->name];
                        });
                    })
                    ->searchable()
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($user = User::find($state)) {
                            $set('name', $user->name);
                            $set('user_id', $user->id);
                            // Remove the User role if the user has it
                            if ($user->hasRole(RolesEnum::User)) {
                                $user->removeRole(RolesEnum::User);
                            }
                            // Assign the Employee role to the user
                            $user->assignRole(RolesEnum::Employee);
                            // Notify the user about the role change
                            Notification::make()
                                ->title('Role Change Notification')
                                ->body('Your role has been changed to Employee.')
                                ->icon('heroicon-o-user')
                                ->sendToDatabase($user, isEventDispatched: true);
                        }
                    }),
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->readOnly(),
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'user.email'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEmployeeManages::route('/'),
        ];
    }
}
