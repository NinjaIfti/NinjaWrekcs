<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $salesData;
    protected $period;

    public function __construct($salesData, $period)
    {
        $this->salesData = $salesData;
        $this->period = $period;
    }

    public function array(): array
    {
        $rows = [];
        
        foreach ($this->salesData['data'] as $item) {
            $rows[] = [
                $item['date'] ?? 'N/A',
                $item['orders'] ?? 0,
                number_format($item['revenue'] ?? 0, 2),
                number_format($item['average'] ?? 0, 2),
            ];
        }
        
        $rows[] = [];
        $rows[] = [
            'TOTAL',
            $this->salesData['summary']['total_orders'] ?? 0,
            number_format($this->salesData['summary']['total_revenue'] ?? 0, 2),
            number_format($this->salesData['summary']['average_order_value'] ?? 0, 2),
        ];
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Orders',
            'Revenue (৳)',
            'Avg Order (৳)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Sales Report - ' . ucfirst($this->period);
    }
}
