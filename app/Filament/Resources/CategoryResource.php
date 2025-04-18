<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Enums\RolesEnum;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Wizard\Step;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                Step::make('Category Name')
                    ->description('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(true)
                            ->afterStateUpdated(function($state, callable $set){
                                $set('slug', Str::slug($state));
                        }),
                        Forms\Components\TextInput::make('slug')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                    ]),
                Step::make('Parent Category')
                    ->description('Select parent category')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->placeholder('Select Parent Category')
                            ->helperText('* Leave this field empty if the category does not have a parent category.')
                            ->options(fn (Forms\Get $get) => Category::where('parent_id', $get('category_id'))
                            ->pluck('name', 'id'))
                            ->label('Parent Category')
                            ->searchable(),
                    ])
            ])
        ])->columns('full');
    }

    public static function table(Table $table): Table
    {
        
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent_id')
                    ->label('Parent Category')
                    ->getStateUsing(function ($record) {
                        return $record->parent_id ? Category::find($record->parent_id)?->name : null;
                    })
            ])
            ->filters([
                tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ])->label('Bulk Actions'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->label('Category Name'),
                Infolists\Components\TextEntry::make('slug')
                    ->label('Slug'),
                Infolists\Components\TextEntry::make('parent_id')
                    ->label('Parent Category')
                    ->getStateUsing(fn ($record) => Category::find($record->parent_id)?->name ?? 'None'),
                Infolists\Components\TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
                Infolists\Components\TextEntry::make('updated_at')
                    ->label('Updated At')
                    ->dateTime(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
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
