<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CheckStockExport implements FromView, WithEvents
{
    use Exportable;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function view() : View
    {
        //
        return view('admin.check_stocks.print.index', ['products' => $this->products]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDefaultRowDimension()->setRowHeight(19);
                $event->sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $event->sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getPageMargins()->setTop(0.7874015748);
                $event->sheet->getPageMargins()->setRight(0.11811023622);
                $event->sheet->getPageMargins()->setBottom(0.11811023622);
                $event->sheet->getPageMargins()->setLeft(0.90551181102);
                $event->sheet->getPageMargins()->setHeader(0);
                $event->sheet->getPageMargins()->setFooter(0);
                $event->sheet->getColumnDimension('A')->setWidth(39.75);
                $event->sheet->getColumnDimension('B')->setWidth(16.75);
                $event->sheet->getColumnDimension('C')->setWidth(20.75);
                $event->sheet->getColumnDimension('D')->setWidth(5.75);
                $event->sheet->getColumnDimension('E')->setWidth(9.75);

                $styleArray = [
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                        'right' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                        'left' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                $event->sheet->getStyle(
                    'A1:' .
                    'A' .
                    $event->sheet->getHighestRow()
                )->applyFromArray($styleArray);

                $event->sheet->getStyle(
                    'B1:' .
                    'B' .
                    $event->sheet->getHighestRow()
                )->applyFromArray($styleArray);

                $event->sheet->getStyle(
                    'C1:' .
                    'C' .
                    $event->sheet->getHighestRow()
                )->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                        'left' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $event->sheet->getStyle(
                    'D1:' .
                    'D' .
                    $event->sheet->getHighestRow()
                )->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                        'right' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $event->sheet->getStyle(
                    'E1:' .
                    'E' .
                    $event->sheet->getHighestRow()
                )->applyFromArray($styleArray);
            },
        ];
    }
}
