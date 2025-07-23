<?php

namespace App\Filament\Widgets;

use App\Models\Complaint;
use Dom\Text;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecetComplaint extends BaseWidget
{
    protected static ?int $sort = 1; 
    protected int | string | array $columnSpan = 'full'; 
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('no')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('complaint_date')
                    ->label('Complaint Date')
                    ->date()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('purchase.customer.name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase.part.name')
                    ->label('Part Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase.part.part_number')
                    ->label('Part Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase.part.price')
                    ->label('Part Price / Pcs')
                    ->money('idr')
                    ->sortable()
                    ->searchable()
                    ->badge(),
                TextColumn::make('quantity_complained')
                    ->label('Quantity Complaint')
                    ->sortable()
                    ->searchable(),
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

            ]);
    }

    protected function getTableQuery(): Builder
    {
        return Complaint::query(); 
    }
}
