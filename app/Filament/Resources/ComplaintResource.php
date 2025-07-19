<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use App\Models\Purchase;
use Filament\Forms\Form;
use App\Models\Complaint;
use Filament\Tables\Table;
// use Filament\Actions\ViewAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
// use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
// use Filament\Tables\Actions\Modal\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\ComplaintResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\ActionGroup as ActionsActionGroup;
use App\Filament\Resources\ComplaintResource\RelationManagers;
use App\Filament\Resources\ComplaintResource\Pages\EditComplaint;
use App\Filament\Resources\ComplaintResource\Pages\ListComplaints;
use App\Filament\Resources\ComplaintResource\Pages\CreateComplaint;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationLabel = 'Complaints';
    protected static ?string $pluralLabel = 'List Complaints';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('purchase_id'),

                Forms\Components\TextInput::make('customer_name')
                    ->label('Customer')
                    ->reactive()
                    ->afterStateHydrated(function (callable $set, callable $get) {
                        $purchase = Purchase::find($get('purchase_id'));
                        $set('customer_name', $purchase?->customer?->name);
                    })
                    ->disabled(),

                Forms\Components\TextInput::make('part_name')
                    ->afterStateHydrated(function (callable $set, callable $get) {
                        $purchase = Purchase::find($get('purchase_id'));
                        $set('part_name', $purchase?->part?->name);
                    })
                    ->disabled(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity Purchased')
                    ->reactive()
                    ->afterStateHydrated(function (callable $set, callable $get) {
                        $purchase = Purchase::find($get('purchase_id'));
                        $set('quantity', $purchase?->quantity);
                    })
                    ->disabled(),

                Forms\Components\TextInput::make('total_price')
                    ->reactive()
                    ->afterStateHydrated(function (callable $set, callable $get) {
                        $purchase = Purchase::find($get('purchase_id'));
                        $set('total_price', $purchase?->total_price);
                    })
                    ->disabled(),
                Forms\Components\DatePicker::make('complaint_date')
                    ->required(),
                Forms\Components\TextInput::make('quantity_complained')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->reactive()
                    ->rule(function (Get $get) {
                        $purchase = \App\Models\Purchase::find($get('purchase_id'));
                        $max = $purchase?->quantity ?? 1;
                        return 'max:' . $max;
                    }),
                Forms\Components\Textarea::make('problem_description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->disabled()
                    ->hidden()
                    ->default('open'),

                FileUpload::make('photo')
                    ->image()
                    ->directory('complaints')
                    ->visibility('public')
                    ->imageEditor()
                    ->preserveFilenames()
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->maxSize(1024)
                    ->columnSpanFull()
                    ->dehydrated(fn($state) => !empty($state)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('#')
                    ->rowIndex()
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase.customer.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.part.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.part.price')
                    ->sortable()
                    ->money('idr')
                    ->label('Part Price / Unit')
                    ->badge()
                    ->icon('heroicon-o-banknotes'),
                Tables\Columns\TextColumn::make('purchase.total_price')
                    ->sortable()
                    ->money('idr')
                    ->label('Quantity Purchased')
                    ->badge()
                    ->icon('heroicon-o-arrow-trending-up')
                    ->color('success'),
                Tables\Columns\TextColumn::make('purchase.part.part_number')
                    ->label('Part Number')
                    ->sortable(),
                Tables\Columns\TextColumn::make('complaint_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_complained')
                    ->label('Quantity Complaint')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('Price_complaint')
                    ->label('Price Complaint')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->icon('heroicon-o-arrow-trending-down')
                    ->color('danger')
                    ->money('idr')
                    ->getStateUsing(function (Complaint $record): float { 
                        $price = $record->purchase?->part?->price ?? 0; 
                        $quantity = $record->quantity_complained ?? 0;
                        return $price * $quantity;
                    }),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo'),
                // ->circular(),
                Tables\Columns\TextColumn::make('status')
                    ->color(fn($state) => match ($state) {
                        'open' => 'danger',
                        'closed' => 'success',
                        default => 'secondary',
                    })
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_closed')
                    ->date()
                    ->sortable(),
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
                SelectFilter::make('purchase.customer_id')
                    ->label('Customer')
                    ->relationship('purchase.customer', 'name')
                    ->multiple()
                    ->searchable(),
                SelectFilter::make('purchase.part_id')
                    ->label('Part')
                    ->relationship('purchase.part', 'name')
                    ->multiple()
                    ->searchable(),
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ])
                    ->searchable(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('analisa') 
                        ->label('Add Analysis')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('success')
                        ->visible(fn(Complaint $record): bool => $record->status === 'open')
                        ->modalSubmitActionLabel('Analysis')
                        ->form([
                            Forms\Components\Textarea::make('lost_inspection')
                                ->label('Lost Inspection')
                                ->required()
                                ->default(fn(Complaint $record) => $record->lost_inspection)
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('occured')
                                ->label('Occurred')
                                ->required()
                                ->default(fn(Complaint $record) => $record->occured)
                                ->columnSpanFull(), 
                        ])
                        ->action(function (Complaint $record, array $data): void {
                            DB::transaction(function () use ($record, $data) {
                                $record->update([
                                    'lost_inspection' => $data['lost_inspection'],
                                    'occured' => $data['occured'],
                                    'date_of_closed' => now(),
                                    'status' => 'closed', 
                                ]);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Analisa successfully saved and complaint closed.')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\ViewAction::make()
                        ->color('info')
                        ->modalWidth('8xl')
                        ->form(function (Complaint $record): array {
                            return [
                                Forms\Components\Hidden::make('purchase_id'),
                                Section::make('Complaint Details')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('customer_name')
                                            ->label('Customer')
                                            ->reactive()
                                            ->afterStateHydrated(function (callable $set, callable $get) use ($record) {
                                                $purchase = Purchase::find($record->purchase_id);
                                                $set('customer_name', $purchase?->customer?->name);
                                            })
                                            ->disabled(),
                                        Forms\Components\TextInput::make('part_name')
                                            ->afterStateHydrated(function (callable $set, callable $get) use ($record) {
                                                $purchase = Purchase::find($record->purchase_id);
                                                $set('part_name', $purchase?->part?->name);
                                            })
                                            ->disabled(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Quantity Purchased')
                                            ->afterStateHydrated(function (callable $set, callable $get) use ($record) {
                                                $purchase = Purchase::find($record->purchase_id);
                                                $set('quantity', $purchase?->quantity);
                                            })
                                            ->disabled(),
                                        Forms\Components\TextInput::make('total_price')
                                            ->prefix('Rp')
                                            ->afterStateHydrated(function (callable $set, callable $get) use ($record) {
                                                $purchase = Purchase::find($record->purchase_id);
                                                $set('total_price', $purchase?->total_price);
                                            })
                                            ->disabled(),
                                        Forms\Components\DatePicker::make('complaint_date')
                                            ->disabled(),
                                        Forms\Components\TextInput::make('quantity_complained')
                                            ->label('Quantity Complained')
                                            ->disabled(),
                                        Forms\Components\TextInput::make('status')
                                            ->disabled(),
                                        Forms\Components\DatePicker::make('date_of_closed')
                                            ->disabled()
                                            ->visible(fn(Complaint $record): bool => $record->status === 'closed'),
                                    ]),

                                Section::make('Problem Description & Photo')
                                    ->schema([
                                        Forms\Components\Textarea::make('problem_description')
                                            ->disabled()
                                            ->columnSpanFull(), 
                                        FileUpload::make('photo')
                                            ->image()
                                            ->directory('complaints')
                                            ->visibility('public')
                                            ->preserveFilenames()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                            ->maxSize(1024)
                                            ->columnSpanFull()
                                            ->disabled(),
                                    ]),

                                Section::make('Analysis')
                                    ->visible(fn(Complaint $record): bool => $record->status === 'closed') // Only show this section if closed
                                    ->schema([
                                        Forms\Components\Textarea::make('lost_inspection')
                                            ->label('Lost Inspection')
                                            ->disabled()
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('occured')
                                            ->label('Occurred')
                                            ->disabled()
                                            ->columnSpanFull(),
                                    ]),
                            ];
                        }),
                    Tables\Actions\EditAction::make()
                        ->color('warning')
                        ->visible(fn(Complaint $record): bool => $record->status === 'open'),
                    Tables\Actions\DeleteAction::make(),
                ]),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordAction(ViewAction::class);
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
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}
