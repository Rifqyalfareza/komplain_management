<?php

namespace App\Filament\Widgets;

use App\Models\Complaint;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class WidgetDateChart extends ChartWidget
{
    protected static ?string $heading = 'Chart of Complaints by Date';

    protected function getData(): array
    {
        $complaints = Complaint::select('complaint_date')->get();
        $labels = [];
        $data = [];
        $monthlyComplaints = [];
        $currentYear = Carbon::now()->year;

        for($i = 1; $i <=12; $i++){
            $monthName = Carbon::create(null, $i, 1)->format('M');
            $labels[] = $monthName;
            $monthlyComplaints[$monthName] = 0;
        }
        foreach ($complaints as $complaint) {
            $complaintDate = Carbon::parse($complaint->complaint_date);

            if ($complaintDate->year === $currentYear) {
                $monthName = $complaintDate->format('M');
                if (isset($monthlyComplaints[$monthName])) {
                    $monthlyComplaints[$monthName]++;
                }
            }
        }
        $data = array_values($monthlyComplaints);
        return [
            'datasets' => [
                [
                    'label' => 'Data',
                    'data' => $data,
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'tension' => 0.1,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
}
