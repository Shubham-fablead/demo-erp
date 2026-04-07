<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;


class GstSalesReportController extends Controller
{
    public function exportGstr3b()
    {
        $spreadsheet = new Spreadsheet();

        /*
|--------------------------------------------------------------------------
| SHEET 1 : GSTR-2B INSTRUCTIONS (A–F layout)
|--------------------------------------------------------------------------
*/
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('GSTR-2B Instructions');

        /* -------------------------------------------------
| COLUMN WIDTHS (A–F)
------------------------------------------------- */
        $sheet1->getColumnDimension('A')->setWidth(18);
        $sheet1->getColumnDimension('B')->setWidth(20);
        $sheet1->getColumnDimension('C')->setWidth(25);
        $sheet1->getColumnDimension('D')->setWidth(35);
        $sheet1->getColumnDimension('E')->setWidth(35);
        $sheet1->getColumnDimension('F')->setWidth(35);

        /* -------------------------------------------------
| MAIN TITLE
------------------------------------------------- */
        $sheet1->mergeCells('A2:F3');
        $sheet1->setCellValue('A2', 'Goods and Services Tax - GSTR-2B');
        $sheet1->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '203864']
            ]
        ]);

        /* -------------------------------------------------
| BASIC DETAILS
------------------------------------------------- */
        $details = [
            ['Financial Year', '2025-26'],
            ['Tax Period', 'November'],
            ['GSTIN', '24ASEPL4747R1Z4'],
            ['Legal Name', 'NEHA LOHIA'],
            ['Trade Name (if any)', 'MAA RII KRIPA NX'],
            ['Date of generation', '14/12/2025'],
        ];

        $row = 4;
        foreach ($details as $item) {

            $sheet1->mergeCells("A{$row}:B{$row}");
            $sheet1->mergeCells("C{$row}:F{$row}");

            $sheet1->setCellValue("A{$row}", $item[0]);
            $sheet1->setCellValue("C{$row}", $item[1]);

            $sheet1->getStyle("A{$row}:F{$row}")->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => ($row <= 5 ? 'FFF2CC' : 'FCE4D6')]
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);

            $row++;
        }

        /* -------------------------------------------------
| SECTION HEADER
------------------------------------------------- */
        $sheet1->mergeCells("A{$row}:F{$row}");
        $sheet1->setCellValue("A{$row}", 'GSTR-2B Data Entry Instructions');
        $sheet1->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        $row++;

        /* -------------------------------------------------
| TABLE HEADER
------------------------------------------------- */
        $sheet1->fromArray([
            ['Worksheet Name', 'GSTR-2B Table Reference', 'Field Name', '', 'Instructions', '']
        ], null, "A{$row}");

        // $sheet1->mergeCells("C{$row}:D{$row}");
        // $sheet1->mergeCells("E{$row}:F{$row}");

        $sheet1->mergeCells("D{$row}:F{$row}");

        $sheet1->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        $row++;

        /* -------------------------------------------------
| TABLE DATA
------------------------------------------------- */
        $tableData = [
            ['GSTIN of Supplier', 'GSTIN of supplier'],
            ['Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available, then legal name of the supplier'],
            ['Invoice number', 'Invoice number'],
            [
                'Invoice type',
                "R - Regular (Other than SEZ supplies and Deemed exports)\n" .
                    "SEZWP - SEZ supplies with payment of tax\n" .
                    "SEZWOP - SEZ supplies without payment of tax\n" .
                    "DE - Deemed exports\n" .
                    "CBW - Intra-State Supplies attracting IGST"
            ],
            ['Invoice date', 'Invoice date format shall be DD-MM-YYYY'],
            ['Invoice value', 'Invoice value (in rupees)'],
            ['Place of supply', 'Place of supply shall be the place where goods are supplied or services are provided (As declared by the supplier)'],
            ['Supply attract Reverse charge', "Y - Purchases attract reverse charge\nN - Purchases don’t attract reverse charge"],
            ['Taxable value', 'Taxable value'],
            ['Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['Central Tax', 'Central Tax amount (In rupees)'],
            ['State/UT tax', 'State/UT tax amount (In rupees)'],
        ];

        foreach ($tableData as $data) {

    $sheet1->setCellValue("C{$row}", $data[0]); // Field Name
    $sheet1->setCellValue("D{$row}", $data[1]); // Instructions

    // Merge instruction column
    $sheet1->mergeCells("D{$row}:F{$row}");

    // AUTO HEIGHT (THIS FIXES YOUR ISSUE)
    $sheet1->getRowDimension($row)->setRowHeight(-1);

    // A–B (White)
    $sheet1->getStyle("A{$row}:B{$row}")->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFFFF']
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);

    // C (Light Grey – Field Name)
    $sheet1->getStyle("C{$row}")->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'EDEDED']
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_TOP,
            'wrapText' => true
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);

    // D–F (Instructions – Wide & Readable)
    $sheet1->getStyle("D{$row}:F{$row}")->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFFFF']
        ],
        'alignment' => [
            'wrapText' => true,
            'vertical' => Alignment::VERTICAL_TOP
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);

    $row++;
}

        /*
    |--------------------------------------------------------------------------
    | SHEET 2 : ITC REVERSAL
    |--------------------------------------------------------------------------
    */
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('ITC Reversal');

        $sheet2->fromArray([
            ['Invoice No', 'Reason', 'IGST', 'CGST', 'SGST', 'Cess'],
            ['INV001', 'Rule 42', '100', '50', '50', '0'],
        ]);


        /*
    |--------------------------------------------------------------------------
    | SHEET 3 : ITC REJECTED
    |--------------------------------------------------------------------------
    */
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('ITC Rejected');

        $sheet3->fromArray([
            ['Invoice No', 'Supplier', 'Amount'],
            ['INV002', 'ABC Traders', '500'],
        ]);


        /*
    |--------------------------------------------------------------------------
    | SHEET 4 : B2B
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('B2B');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);


        /*
    |--------------------------------------------------------------------------
    | SHEET 5 : B2BA
    |--------------------------------------------------------------------------
    */
        $sheet5 = $spreadsheet->createSheet();
        $sheet5->setTitle('B2BA');

        $sheet5->fromArray([
            ['Original Invoice', 'Revised Invoice', 'Amount'],
        ]);

        // Auto size all sheets
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }



        /*
      |--------------------------------------------------------------------------
    | SHEET 6 : B2B CDNR
    |--------------------------------------------------------------------------
    */
        $sheet6 = $spreadsheet->createSheet();
        $sheet6->setTitle('B2B-CDNR');

        $sheet6->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);
        /*
      |--------------------------------------------------------------------------
    | SHEET 7 : B2B CDNRA
    |--------------------------------------------------------------------------
    */
        $sheet7 = $spreadsheet->createSheet();
        $sheet7->setTitle('B2B-CDNRA');

        $sheet7->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 8 : ECO
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('ECOA');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);
        /*
      |--------------------------------------------------------------------------
    | SHEET 9 : ECOA
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('ECOA');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 10 : ISD
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('ISD');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 11 : ISDA
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('ISDA');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 12 : IMPG
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('IMPG');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 13 : IMPGA
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('IMPGA');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 14 : IMPGSEZ
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('IMPGSEZ');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 15 : IMPGSEZA
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('IMPGSEZA');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 16 : B2B(Rejected)
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('B2B(Rejected)');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 17 : B2BA(Rejected)
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('B2BA(Rejected)');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);
        /*
      |--------------------------------------------------------------------------
    | SHEET 18 : ISD(Rejected)
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('ISD(Rejected)');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        /*
      |--------------------------------------------------------------------------
    | SHEET 19 : ISDA(Rejected)
    |--------------------------------------------------------------------------
    */
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('ISDA(Rejected)');

        $sheet4->fromArray([
            ['GSTIN', 'Invoice No', 'Taxable Value', 'IGST', 'CGST', 'SGST'],
        ]);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'GSTR-2B.xlsx');
    }
}
