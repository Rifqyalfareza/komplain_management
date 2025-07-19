<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Complaint;
use Filament\Widgets\ChartWidget;

class WidgetCustomerChart extends ChartWidget
{
    protected static ?string $heading = 'Chart of Customer Complaints';

    protected function getData(): array
    {
        $customers = Customer::all();

        $labels = [];
        $data = [];

        foreach ($customers as $customer) {
            $labels[] = $customer->name; 
            $complaintCount = Complaint::whereHas('purchase.customer', function ($query) use ($customer) {
                $query->where('id', $customer->id);
            })->count();
            $data[] = $complaintCount; }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Complaints',
                    'data' => $data,
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'tension' => 0.1,
                    'fill' => true,
                ],
                [
                    'label'=> 'Target Complaints',
                    'data' => array_fill(0, count($data), 2), 
                    'borderColor' => '#FF6384',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'tension' => 0.1,
                    'borderDash' => [5, 5], 
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    // protected function getOptions(): array
    // {
    //     return [
    //         'scales' => [
    //             'y' => [
    //                 'beginAtZero' => true,
    //                 'ticks' => [
    //                     'precision' => 0, 
    //                 ],
    //                 'title' => [
    //                     'display' => true,
    //                     'text' => 'Complaints',
    //                 ],
    //             ],
    //             'x' => [
    //                 'title' => [
    //                     'display' => true,
    //                     'text' => 'Customer Name',
    //                 ],
    //             ],
    //         ],
    //         'plugins' => [
    //             'legend' => [
    //                 'display' => true,
    //             ],
    //         ],
    //         'responsive' => true,
    //         'maintainAspectRatio' => false,
    //     ];
    // }
    
}
