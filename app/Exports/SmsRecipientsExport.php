<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SmsRecipientsExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected array $rows;

    public function __construct(array $recipients)
    {
        $this->rows = $recipients;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Name', 'Phone', 'Source'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'SMS Recipients';
    }
}
