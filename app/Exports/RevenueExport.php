<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RevenueExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected $data;
    protected $startDate;
    protected $endDate;
    
    public function __construct($data, $startDate, $endDate)
    {
        $this->data = $data;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data);
    }
    
    /**
     * @return string
     */
    public function title(): string
    {
        return 'Revenue Report';
    }
    
    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A6';
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Date',
            'Amount (MAD)',
            'Payment Method',
            'Reference',
            'Customer',
            'Vehicle'
        ];
    }
    
    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            date('Y-m-d', strtotime($row->payment_date)),
            number_format($row->amount, 2),
            ucfirst(str_replace('_', ' ', $row->payment_method)),
            $row->reference ?? 'N/A',
            $row->contract && $row->contract->client ? $row->contract->client->name : 'N/A',
            $row->contract && $row->contract->car ? $row->contract->car->brand_name . ' ' . $row->contract->car->model : 'N/A'
        ];
    }
    
    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            6 => ['font' => ['bold' => true, 'size' => 12]],
            // Style all cells
            'A' => ['font' => ['size' => 11]],
            'B' => ['font' => ['size' => 11]],
            'C' => ['font' => ['size' => 11]],
            'D' => ['font' => ['size' => 11]],
            'E' => ['font' => ['size' => 11]],
            'F' => ['font' => ['size' => 11]],
        ];
    }
    
    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set title
                $event->sheet->mergeCells('A1:F1');
                $event->sheet->setCellValue('A1', 'Car Rental Management System');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                // Set subtitle
                $event->sheet->mergeCells('A2:F2');
                $event->sheet->setCellValue('A2', 'Revenue Report');
                $event->sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                // Set period
                $event->sheet->mergeCells('A3:F3');
                $event->sheet->setCellValue('A3', 'Period: ' . date('F d, Y', strtotime($this->startDate)) . ' - ' . date('F d, Y', strtotime($this->endDate)));
                $event->sheet->getStyle('A3')->getFont()->setSize(12);
                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                // Set generated date
                $event->sheet->mergeCells('A4:F4');
                $event->sheet->setCellValue('A4', 'Generated on: ' . date('F d, Y H:i:s'));
                $event->sheet->getStyle('A4')->getFont()->setSize(10);
                $event->sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                // Add borders to the table
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A6:F' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                // Color the header row
                $event->sheet->getStyle('A6:F6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');
            }
        ];
    }
}