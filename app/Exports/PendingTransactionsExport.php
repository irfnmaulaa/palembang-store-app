<?php

namespace App\Exports;

use App\Models\TransactionProduct;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PendingTransactionsExport implements FromView, WithEvents
{
    use Exportable;

    public function __construct($printed_by, $transaction_products)
    {
        $this->printed_by = $printed_by;
        $this->transaction_products = $transaction_products;
    }

    public function view() : View
    {

        $transaction_products = $this->transaction_products;
        $printed_by = $this->printed_by;
        return view('admin.transactions.export.pending-transactions-pdf', compact('transaction_products', 'printed_by'));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getRowDimension('1')->setRowHeight(26.5);
                for ($i = 2; $i <= $event->sheet->getHighestRow(); $i++) {
                    $event->sheet->getRowDimension($i)->setRowHeight(19);
                }

                $event->sheet->getDefaultRowDimension()->setRowHeight(19);
                $event->sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $event->sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getPageMargins()->setTop(1.1811);
                $event->sheet->getPageMargins()->setRight(0.11811);
                $event->sheet->getPageMargins()->setBottom(0.11811);
                $event->sheet->getPageMargins()->setLeft(0.11811);
                $event->sheet->getColumnDimension('A')->setWidth(15.46);
                $event->sheet->getColumnDimension('B')->setWidth(13.61);
                $event->sheet->getColumnDimension('C')->setWidth(12.75);
                $event->sheet->getColumnDimension('D')->setWidth(34.04);
                $event->sheet->getColumnDimension('E')->setWidth(34.75);
                $event->sheet->getColumnDimension('F')->setWidth(14.89);
                $event->sheet->getColumnDimension('G')->setWidth(10.89);
                $event->sheet->getColumnDimension('H')->setWidth(10.46);


                $styleRow = [
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOTTED,
                        ],
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOTTED,
                        ],
                    ],
                ];

                $styleHeader = [
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        ],
                    ],
                ];


                $event->sheet->getStyle("A1:H1")->applyFromArray($styleHeader);
                $event->sheet->getStyle("A1:H1")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle("A{$event->sheet->getHighestRow()}:H{$event->sheet->getHighestRow()}")->applyFromArray($styleHeader);

                $cell = $event->sheet->getHighestRow() + 2;
                $event->sheet->getCell("G{$cell}")->setValue("ID :");
                $event->sheet->getCell("H{$cell}")->setValue(auth()->user()->name);

                $event->sheet->getStyle("G{$cell}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $event->sheet->getStyle("H{$cell}")->getFont()->setBold(true);
            }
        ];
    }
}
