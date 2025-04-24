<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderImageRelationManager extends RelationManager
{
    protected static string $relationship = 'OrderItems';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Order Images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('image_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('image_description')
                    ->maxLength(65535),
                Forms\Components\FileUpload::make('upload')
                    ->label('Upload Image')
                    ->required()
                    ->preserveFilenames()
                    ->directory('order-images')
                    ->openable()
                    ->downloadable()
                    ->image()
                    ->imageEditor()
                    ->imageEditorMode(2)
                    ->imageEditorAspectRatios(['16:9', '4:3', '1:1', '2:3', '3:2'])
                    ->panelAspectRatio('2:1')
                    ->panelLayout('integrated')
                    ->loadingIndicatorPosition('right')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('right')
                    ->uploadProgressIndicatorPosition('right')
            ])->columns('full');
    }

    public function table(Table $table): Table
    {
        $table->defaultSort('id', 'desc');
        return $table
            ->recordTitleAttribute('image_title')
            ->columns([
                Tables\Columns\ImageColumn::make('upload')
                    ->label('Image')
                    ->circular()
                    ->size(80) // Adjusted size
                    ->default('https://fakeimg.pl/600x400?text=No+Image&font=bebas')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('image_title')
                    ->label('Title')
                    ->description(fn ($record): string => $record->image_description ? 'Description: ' . \Illuminate\Support\Str::limit($record->image_description, 50) : 'No Description')
                    ->tooltip(fn ($record): string => $record->image_description ?? 'No Description'),
                
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->label('Add New Image')
                ->icon('heroicon-o-plus'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
