<?php

namespace App\Filament\Widgets;

use App\Models\Complaint;
use App\Models\Purchase;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class PurchaseOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected function getStats(): array
    {
        return [
            Stat::make('Total Purchases', Purchase::sum('total_price'))
                ->description('Total Earnings from Purchases')
                ->descriptionColor('success')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->value('Rp ' . number_format(Purchase::sum('total_price'), 0, ',', '.')),
            Stat::make('Count Of Purchases', Purchase::count())
                ->description('Total Number of Purchases')
                ->descriptionColor('primary')
                ->color('primary')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->value(Purchase::count()),
            Stat::make('Total Quantity Purchased', Purchase::sum('quantity'))
                ->description('Total Quantity Purchased')
                ->descriptionColor('info')
                ->color('info')
                ->descriptionIcon('heroicon-o-cube')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->value(Purchase::sum('quantity')),
            Stat::make('Count of Complaints', Complaint::count())
                ->description('Total Number of Complaints')
                ->descriptionColor('danger')
                ->color('danger')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->value(Complaint::count()),
            Stat::make('Count of Open Complaints', Complaint::where('status', 'open')->count())
                ->description('Total Number of Open Complaints')
                ->descriptionColor('danger')
                ->color('danger')
                ->descriptionIcon('heroicon-o-x-circle')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Count of Closed Complaints', Complaint::where('status', 'closed')->count())
                ->description('Total Number of Closed Complaints')
                ->descriptionColor('success')
                ->color('success')
                ->descriptionIcon('heroicon-o-check-circle')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

        ];
    }
}
