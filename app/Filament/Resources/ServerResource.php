<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServerResource\Pages;
use App\Models\Server;
use App\Services\JarService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                         ->required()
                         ->maxLength(255),

                Select::make('version')
                      ->label('Minecraft Version')
                      ->options(function () {
                          $jarService = new JarService();

                          return $jarService->fetchAvailableVersions();
                      })
                      ->searchable()
                      ->required(),

                TextInput::make('ram_mb')
                         ->label('Memory (MB)')
                         ->numeric()
                         ->default(4096)
                         ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                          ->searchable()
                          ->sortable(),

                TextColumn::make('version')
                          ->sortable(),

                TextColumn::make('memory')
                          ->label('Memory (MB)')
                          ->sortable(),

                TextColumn::make('created_at')
                          ->label('Created')
                          ->dateTime()
                          ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListServers::route('/'),
            'create' => Pages\CreateServer::route('/create'),
            'edit' => Pages\EditServer::route('/{record}/edit'),
        ];
    }
}
