<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Part;
use Filament\Tables;
use App\Models\Purchase;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PurchaseResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Actions\Action as ActionsAction;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Purchases';
    protected static ?string $pluralLabel = 'List Purchases';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('part_id')
                    ->label('Part')
                    ->relationship('part', 'name')
                    ->required()
                    ->reactive()
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $part = Part::find($state);
                        if ($part) {
                            $set('price', $part->price);

                            if ($get('quantity')) {
                                $set('total_price', $get('quantity') * $part->price);
                            }
                        }
                    }),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->disabled(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $price = $get('price');
                        if ($price) {
                            $set('total_price', $state * $price);
                        }
                    }),
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->disabled()
                    ->reactive()
                    ->dehydrated(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->sortable()
                    ->label('#')
                    ->rowIndex()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('part.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('part.price')
                    ->numeric()
                    ->money('idr')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->money('idr')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->icon('heroicon-o-banknotes'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->multiple()
                    ->searchable(),
                SelectFilter::make('part_id')
                    ->label('Part')
                    ->relationship('part', 'name')
                    ->multiple()
                    ->searchable(),
            ])
            ->actions([
                    Action::make('complaint')
                        ->label('Complaint')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->url(fn(Purchase $record) => ComplaintResource::getUrl('create', ['purchase_id' => $record->id])),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPurchases::route('/'),
            // 'create' => Pages\CreatePurchase::route('/create'),
            // 'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
