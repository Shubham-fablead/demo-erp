<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\Request;

class GstSalesReportController extends Controller
{
    public function exportGstr3b()
    {
        $spreadsheet = new Spreadsheet();

        // 1. Read Me (First sheet)
        $this->addReadMeSheet($spreadsheet);

        // 2. ITC Available
        $this->addItcAvailableSheet($spreadsheet);

        // 3. ITC Not Available
        $this->addItcNotAvailableSheet($spreadsheet);

        // 4. ITC Reversal
        $this->addItcReversalSheet($spreadsheet);

        // 5. ITC Rejected
        $this->addItcRejectedSheet($spreadsheet);

        // 6. B2B
        $this->addB2BSheet($spreadsheet);


        // 7. B2BA
        $this->addB2BASheet($spreadsheet);


        // 8. B2B-CDNR
        $this->addB2B_CDNRSheet($spreadsheet);

        // 9. B2B-CDNRA
        $this->addB2B_CDNRASheet($spreadsheet);

        // 10. ECO
        $this->addECOSheet($spreadsheet);

        // 11. ECOA
        $this->addECOASheet($spreadsheet);

        // 12. ISD
        $this->addISDSheet($spreadsheet);

        // 13. ISDA
        $this->addISDASheet($spreadsheet);

        // 14. IMPG
        $this->addIMPGSheet($spreadsheet);

        // 15. IMPGA
        $this->addIMPGASheet($spreadsheet);

        // 16. IMPGSEZ
        $this->addIMPGSEZSheet($spreadsheet);

        // 17. IMPGSEZA
        $this->addIMPGSEZASheet($spreadsheet);

        // 18. B2B(ITC Reversal)
        $this->addB2B_ITCReversalSheet($spreadsheet);

        // 19. B2BA(ITC Reversal)
        $this->addB2BA_ITCReversalSheet($spreadsheet);

        // 20. B2B-DNR
        $this->addB2B_DNRSheet($spreadsheet);

        // 21. B2B-DNRA
        $this->addB2B_DNRASheet($spreadsheet);

        // 22. B2B(Rejected)
        $this->addB2B_RejectedSheet($spreadsheet);

        // 23. B2BA(Rejected)
        $this->addB2BA_RejectedSheet($spreadsheet);

        // 24. B2B-CDNR(Rejected)
        $this->addB2BCDNRRejectedSheet($spreadsheet);

        // 25. B2B-CDNRA(Rejected)
        $this->addB2BCDNRARejectedSheet($spreadsheet);

        // 26. ECO(Rejected)
        $this->addECORejectedSheet($spreadsheet);

        // 27. ECOA(Rejected)
        $this->addECOARejectedSheet($spreadsheet);

        // 28. ISD(Rejected)
        $this->addISDRejectedSheet($spreadsheet);

        //  29. ISDA(Rejected)
        $this->addISDARejectedSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);

        // Use output buffering to capture the Excel file
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="GSTR2B_Multiple_Sheets.xlsx"')
            ->header('Cache-Control', 'max-age=0');
    }

    private function stateName($code)
    {
        $states = [
            '01' => 'Jammu and Kashmir',
            '02' => 'Himachal Pradesh',
            '03' => 'Punjab',
            '04' => 'Chandigarh',
            '05' => 'Uttarakhand',
            '06' => 'Haryana',
            '07' => 'Delhi',
            '08' => 'Rajasthan',
            '09' => 'Uttar Pradesh',
            '10' => 'Bihar',
            '11' => 'Sikkim',
            '12' => 'Arunachal Pradesh',
            '13' => 'Nagaland',
            '14' => 'Manipur',
            '15' => 'Mizoram',
            '16' => 'Tripura',
            '17' => 'Meghalaya',
            '18' => 'Assam',
            '19' => 'West Bengal',
            '20' => 'Jharkhand',
            '21' => 'Odisha',
            '22' => 'Chhattisgarh',
            '23' => 'Madhya Pradesh',
            '24' => 'Gujarat',
            '25' => 'Daman and Diu',
            '26' => 'Dadra and Nagar Haveli',
            '27' => 'Maharashtra',
            '28' => 'Andhra Pradesh',
            '29' => 'Karnataka',
            '30' => 'Goa',
            '31' => 'Lakshadweep',
            '32' => 'Kerala',
            '33' => 'Tamil Nadu',
            '34' => 'Puducherry',
            '35' => 'Andaman and Nicobar Islands',
            '36' => 'Telangana',
            '37' => 'Andhra Pradesh (New)',
        ];

        return $states[$code] ?? '';
    }

    private function addReadMeSheet($spreadsheet)
    {
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Read me');

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
        $sheet1->mergeCells('A1:F3');
        $sheet1->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet1->getStyle('A1:F3')->applyFromArray([
            'font' => [
                'bold' => false,
                'size' => 27,
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

            $sheet1->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle("C{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $row++;
        }

        $row++; // Add empty row 10

        /* -------------------------------------------------
        | SECTION HEADER
        ------------------------------------------------- */
        $sheet1->mergeCells("A{$row}:F{$row}");
        $sheet1->setCellValue("A{$row}", 'GSTR-2B Data Entry Instructions');
        $sheet1->getStyle("A{$row}:F{$row}")->applyFromArray([
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
            ['Worksheet Name', 'GSTR-2B Table Reference', 'Field Name', 'Instructions', '', '']
        ], null, "A{$row}");

        $sheet1->mergeCells("D{$row}:F{$row}");

        $sheet1->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            ],
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
        | INSTRUCTIONS DATA
        ------------------------------------------------- */
        $instructions = [
            ['B2B', 'Taxable inward supplies received from registered person', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2B', 'Taxable inward supplies received from registered person', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available, then legal name of the supplier'],
            ['B2B', 'Taxable inward supplies received from registered person', 'Invoice number', 'Invoice number'],
            ['B2B', 'Taxable inward supplies received from registered person', 'Invoice type', "Invoice type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWIP - SEZ supplies with payment of tax\nSEZWOP - SEZ supplies without payment of tax\nDE - Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2B', 'Taxable inward supplies received from registered person', 'Invoice date', 'Invoice date format shall be DD-MM-YYYY'],
            ['B2B', 'Taxable inward supplies received from registered person', 'Invoice value', 'Invoice value (in rupees)'],
            ['B2B', 'Taxable inward supplies received from registered person', 'Place of supply', 'Place of supply shall be the place where goods are supplied or services are provided (As declared by the supplier)'],
            ['B2B', 'Taxable inward supplies received from registered person', 'Supply attract Reverse charge', "Supply attract reverse charge divided into two types:\nY- Purchases attract reverse charge\nN- Purchases don't attract reverse charge"],
            ['B2B', 'Taxable inward supplies received from registered person', 'Taxable value', 'Taxable value'],
            ['B2B', 'Taxable inward supplies received from registered person', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2B', 'Taxable inward supplies received from registered person', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2B', 'Taxable inward supplies received from registered person', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['B2B', 'Taxable inward supplies received from registered person', 'Cess', 'Cess amount (In rupees)'],
            ['B2B', 'Taxable inward supplies received from registered person', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B', 'Taxable inward supplies received from registered person', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B', 'Taxable inward supplies received from registered person', 'ITC Availability', "Is ITC available or not on the document - 'Yes' or 'No'"],
            ['B2B', 'Taxable inward supplies received from registered person', 'Reason', "Reason, if ITC availability is 'No'"],
            ['B2B', 'Taxable inward supplies received from registered person', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2B', 'Taxable inward supplies received from registered person', 'Source', "Source of the document shall be displayed. It shall be:\na. 'e-invoice', if the document is auto-populated from e-invoice.\nb. Blank, if the document is uploaded by the supplier"],
            ['B2B', 'Taxable inward supplies received from registered person', 'IRN', "It is the unique Invoice reference number of the document auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank"],
            ['B2B', 'Taxable inward supplies received from registered person', 'IRN date', "This is the date of invoice reference number, auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank"],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Invoice number (Original details)', 'Original invoice number'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Invoice date (Original details)', 'Original invoice date (Date format shall be DD-MM-YYYY)'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Invoice number', 'Revised Invoice number'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Invoice type', "Invoice type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWIP - SEZ supplies with payment of tax\nSEZWOP - SEZ supplies without payment of tax\nDE - Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Invoice date', 'Invoice date format shall be DD-MM-YYYY'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Invoice value', 'Invoice value (in rupees)'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the supplier)'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Supply attract Reverse charge', "Supply attract reverse charge divided into two types:\nY- Purchases attract reverse charge\nN- Purchases don't attract reverse charge"],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Taxable value', 'Taxable value (In rupees)'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Cess', 'Cess amount (In rupees)'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Whether ITC to be reduced (Taxpayer\'s Input)', 'YES/NO - As per selection by recipient on IMS Dashboard'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Integrated Tax', 'Amount of IGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Central Tax', 'Amount of CGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'State/UT Tax', 'Amount of SGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Cess', 'Amount of Cess declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'ITC Availability', "If ITC is available, 'Yes', else 'No'"],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Reason', "Reason, if ITC availability is 'No'"],
            ['B2BA', 'Amendments to previously uploaded invoices by supplier', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Note number', 'Debit/Credit note number'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Note type', 'Document type can be Debit note or credit note'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Note Supply Type', "Note Supply type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWIP - SEZ supplies with payment of tax\nSEZWOP - SEZ supplies without payment of tax\nDE - Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Note date', 'Debit/Credit note date format shall be (DD-MM-YYYY)'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Note Value', 'Debit/Credit note value (In rupees)'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the supplier)'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Supply attract Reverse charge', "Supply attract reverse charge divided into two types\nY- Purchases attract reverse charge\nN- Purchases don't attract reverse charge"],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Taxable value', 'Taxable value (In rupees)'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Cess', 'Cess amount (In rupees)'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Whether ITC to be reduced (Taxpayer\'s Input)', 'YES/NO - As per selection by recipient on IMS Dashboard'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Integrated Tax', 'Amount of IGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Central Tax', 'Amount of CGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'State/UT Tax', 'Amount of SGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Cess', 'Amount of Cess declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'ITC Availability', "If ITC is available, 'Yes', else 'No'"],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Reason', "Reason, if ITC availability is 'No'"],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Source', "Source of the document shall be displayed. It shall be:\na. 'e-invoice', if the document is auto-populated from e-invoice.\nb. Blank, if the document is uploaded by the supplier"],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'IRN', "It is the unique Invoice reference number of the document auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank"],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'IRN date', "This is the date of invoice reference number, auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank"],
            ['B2B-CDNR', 'Debit/Credit notes(Original)', 'Note type(Original)', 'Note type can be Debit note or credit note'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Note number(Original)', 'Original Debit/Credit note number'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Note date(Original)', 'Original Debit/Credit note date (Note date format shall be DD-MM-YYYY)'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Note number', 'Debit/Credit note number'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Note type', 'Note type can be Debit note or credit note'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Note Supply Type', "Note Supply type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWIP - SEZ supplies with payment of tax\nSEZWOP - SEZ supplies without payment of tax\nDE - Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Note date', 'Debit/Credit note date format shall be (DD-MM-YYYY)'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Note Value', 'Debit/Credit note value (In rupees)'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the supplier)'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Supply attract Reverse charge', "Supply attract reverse charge divided into two types\nY- Purchases attract reverse charge\nN- Purchases don't attract reverse charge"],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Taxable value', 'Taxable value (In rupees)'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Cess', 'Cess amount (In rupees)'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Whether ITC to be reduced (Taxpayer\'s Input)', 'YES/NO - As per selection by recipient on IMS Dashboard'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Integrated Tax', 'Amount of IGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Central Tax', 'Amount of CGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'State/UT Tax', 'Amount of SGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Cess', 'Amount of Cess declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'ITC Availability', "If ITC is available, 'Yes', else 'No'"],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Reason', "Reason, if ITC availability is 'No'"],
            ['B2B-CDNRA', 'Amendments to previously uploaded Credit/Debit notes by supplier', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'GSTIN of ECO', 'GSTIN of E-commerce operator'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Trade/Legal name', 'Trade name of the E-commerce operator will be displayed. If trade name is not available, then legal name of the E-commerce operator'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document number', 'Document number'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document type', "Document type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP - SEZ supplies with payment of tax\nSEZWOP - SEZ supplies without payment of tax\nDE - Deemed exports"],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document date', 'Document date format shall be DD-MM-YYYY'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document value', 'Document value (in rupees)'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Place of supply', 'Place of supply shall be the place where goods are supplied or services are provided (As declared by the eco)'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Taxable value', 'Taxable value'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Cess', 'Cess amount (In rupees)'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'GSTR-1/1A/IFF period', 'Period for which GSTR-1/IFF has been filed'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'GSTR-1/1A/IFF filing date', 'Date on which GSTR-1/IFF has been filed'],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'ITC Availability', "Is ITC available or not on the document - 'Yes' or 'No'"],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Reason', "Reason, if ITC availability is 'No'"],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Source', "Source of the document shall be displayed. It shall be:\na. 'e-invoice', if the document is auto-populated from e-invoice.\nb. Blank, if the document is uploaded by the E-commerce operator"],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'IRN', "It is the unique Invoice reference number of the document auto-populated from e-invoice. For the documents uploaded by the E-commerce operator, this shall be blank"],
            ['ECO', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'IRN date', "This is the date of invoice reference number, auto-populated from e-invoice. For the documents uploaded by the E-commerce operator, this shall be blank"],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document number (Original details)', 'Document invoice number'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document date (Original details)', 'Document invoice date (Date format shall be DD-MM-YYYY)'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'GSTIN of ECO', 'GSTIN of E-commerce operator'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Trade/Legal name', 'Trade name of the E-commerce operator will be displayed. If trade name is not available then legal name of the E-commerce operator'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document number', 'Revised Document number'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document type', "Document type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP - SEZ supplies with payment of tax\nSEZWOP - SEZ supplies without payment of tax\nDE - Deemed exports"],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document date', 'Document date format shall be DD-MM-YYYY'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document value', 'Document value (in rupees)'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the Eco)'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Taxable value', 'Taxable value (In rupees)'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Cess', 'Cess amount (In rupees)'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Whether ITC to be reduced (Taxpayer\'s Input)', 'YES/NO - As per selection by recipient on IMS Dashboard'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Integrated Tax', 'Amount of IGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Central Tax', 'Amount of CGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'State/UT Tax', 'Amount of SGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Cess', 'Amount of Cess declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'GSTR-1/1A/IFF Period', 'Period for which GSTR-1/IFF has been filed'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'GSTR-1/1A/IFF filing date', 'Date on which GSTR-1/IFF has been filed'],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'ITC Availability', "If ITC is available, 'Yes', else 'No'"],
            ['ECOA', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Reason', "Reason, if ITC availability is 'No'"],
            ['ISD', 'ISD Credit', 'GSTIN of ISD', 'Input Service Distributor GSTIN'],
            ['ISD', 'ISD Credit', 'Trade/Legal name of the ISD', 'Trade name of the ISD will be displayed. If trade name is not available then legal name of the ISD'],
            ['ISD', 'ISD Credit', 'ISD Document type', 'ISD document type can be Invoice or Credit note'],
            ['ISD', 'ISD Credit', 'ISD Document number', 'ISD invoice / ISD Credit note number'],
            ['ISD', 'ISD Credit', 'ISD Document date', 'ISD Document date format will be DD-MM-YYYY'],
            ['ISD', 'ISD Credit', 'Original ISD Invoice number', "This is applicable only if ISD document type is 'Credit note' is linked to invoice"],
            ['ISD', 'ISD Credit', 'Original ISD Invoice date', "This is applicable only if ISD document type is 'Credit note' is linked to invoice"],
            ['ISD', 'ISD Credit', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['ISD', 'ISD Credit', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['ISD', 'ISD Credit', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['ISD', 'ISD Credit', 'Cess', 'Cess amount (In rupees)'],
            ['ISD', 'ISD Credit', 'ISD GSTR-6 Period', 'Period for which GSTR-6 is to be filed'],
            ['ISD', 'ISD Credit', 'ISD GSTR-6 Filing date', 'Date on which GSTR-6 has been filed'],
            ['ISD', 'ISD Credit', 'Eligibility of ITC', "Eligibility of ITC are two types:\nY-Yes. Taxpayer can claim ITC on such invoice\nN- No. Taxpayer can't claim ITC on such invoice"],
            ['ISDA', 'Amendments to ISD Credits received', 'ISD Document type (Original)', 'ISD document type can be Invoice or Credit note'],
            ['ISDA', 'Amendments to ISD Credits received', 'ISD Document Number (Original)', 'Invoice/Credit note number'],
            ['ISDA', 'Amendments to ISD Credits received', 'ISD Document date (Original)', 'Invoice/Credit note date'],
            ['ISDA', 'Amendments to ISD Credits received', 'GSTIN of ISD', 'GSTIN of the Input Service Distributor'],
            ['ISDA', 'Amendments to ISD Credits received', 'Trade/Legal name of the ISD', 'Trade name of the ISD will be displayed. If trade name is not available then legal name of the ISD'],
            ['ISDA', 'Amendments to ISD Credits received', 'ISD Document type', 'ISD document type can be Invoice or Credit note'],
            ['ISDA', 'Amendments to ISD Credits received', 'ISD Document number', 'ISD invoice / ISD Credit note number'],
            ['ISDA', 'Amendments to ISD Credits received', 'ISD Document date', 'ISD Document date format will be DD-MM-YYYY'],
            ['ISDA', 'Amendments to ISD Credits received', 'Original ISD Invoice number', "This is applicable only if ISD document type is 'Credit note' is linked to invoice"],
            ['ISDA', 'Amendments to ISD Credits received', 'Original ISD Invoice date', "This is applicable only if ISD document type is 'Credit note' is linked to invoice"],
            ['ISDA', 'Amendments to ISD Credits received', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['ISDA', 'Amendments to ISD Credits received', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['ISDA', 'Amendments to ISD Credits received', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['ISDA', 'Amendments to ISD Credits received', 'Cess', 'Cess amount (In rupees)'],
            ['ISDA', 'Amendments to ISD Credits received', 'ISD GSTR-6 Period', 'Period for which GSTR-6 is to be filed'],
            ['ISDA', 'Amendments to ISD Credits received', 'ISD GSTR-6 Filing date', 'Date on which GSTR-6 has been filed'],
            ['ISDA', 'Amendments to ISD Credits received', 'Eligibility of ITC', "Eligibility of ITC are two types:\nY-Yes. Taxpayer can claim ITC on such invoice\nN- No. Taxpayer can't claim ITC on such invoice"],
            ['IMPG', 'Import of goods from overseas on bill of entry', 'ICEGATE Reference date', 'Relevant date for availing credit on the bill of entry'],
            ['IMPG', 'Import of goods from overseas on bill of entry', 'Port Code', 'Port code'],
            ['IMPG', 'Import of goods from overseas on bill of entry', 'Bill of Entry number', 'Bill of Entry number'],
            ['IMPG', 'Import of goods from overseas on bill of entry', 'Bill of Entry date', 'Bill of Entry date format shall be DD-MM-YYYY'],
            ['IMPG', 'Import of goods from overseas on bill of entry', 'Taxable value', 'Taxable value (In rupees)'],
            ['IMPG', 'Import of goods from overseas on bill of entry', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['IMPG', 'Import of goods from overseas on bill of entry', 'Cess', 'Cess amount (In rupees)'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'ICEGATE Reference date', 'Relevant date for availing credit on the bill of entry'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'Port Code (Original details)', 'Original Port code'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'Bill of Entry number (Original details)', 'Original Bill of Entry number'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'Bill of Entry date (Original details)', 'Original Bill of Entry date (Date format shall be DD-MM-YYYY)'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'Taxable value', 'Taxable value (In rupees)'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'Cess', 'Cess amount (In rupees)'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'Type of Amendment', 'Amendment type: 1. Amendment 2. Cancellation'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'Whether ITC to be reduced?(Taxpayer\'s input)', 'Whether taxpayer (with incorrect GSTIN) wishes to reduce the ITC in case of GSTIN amendment'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', ' Amount declared by taxpayer for ITC reduction - Integerated Tax', 'In case of GSTIN amendment, the value of cess to be reduced from ITC as entered by taxpayer (with incorrect GSTIN)'],
            ['IMPGA', 'Amendments to Import of Goods from overseas on bill of entry', 'Amount declared by taxpayer for ITC reduction - Cess', 'In case of GSTIN amendment, the value of cess to be reduced from ITC as entered bytaxpayer (with incorrect GSTIN)'],
            ['IMPGSEZ', 'Import of goods from SEZ units / developers on bill of entry', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['IMPGSEZ', 'Import of goods from SEZ units / developers on bill of entry', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['IMPGSEZ', 'Import of goods from SEZ units / developers on bill of entry', 'ICEGATE Reference date', 'Relevant date for availing credit on the bill of entry'],
            ['IMPGSEZ', 'Import of goods from SEZ units / developers on bill of entry', 'Port Code', 'Port code'],
            ['IMPGSEZ', 'Import of goods from SEZ units / developers on bill of entry', 'Bill of Entry number', 'Bill of Entry number'],
            ['IMPGSEZ', 'Import of goods from SEZ units / developers on bill of entry', 'Bill of Entry date', 'Bill of Entry date format shall be DD-MM-YYYY'],
            ['IMPGSEZ', 'Import of goods from SEZ units / developers on bill of entry', 'Taxable value', 'Taxable value (In rupees)'],
            ['IMPGSEZ', 'Import of goods from SEZ units / developers on bill of entry', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['IMPGSEZ', 'Import of goods from SEZ units / developers on bill of entry', 'Cess', 'Cess amount (In rupees)'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Bill of Entry number (Original details)', 'Original Bill of Entry number'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Bill of Entry date (Original details)', 'Original Bill of Entry date (Date format shall be DD-MM-YYYY)'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Type of Amendment', 'Amendment type: 1. Amendment 2. Cancellation'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Port Code', 'Port code'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Bill of Entry number', 'Bill of Entry number'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Bill of Entry date', 'Bill of Entry date format shall be DD-MM-YYYY'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Taxable value', 'Taxable value (In rupees)'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Cess', 'Cess amount (In rupees)'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Whether ITC to be reduced (Taxpayer\'s Input)', 'YES/NO - As per selection by recipient on IMS Dashboard'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Integrated Tax', 'Amount of IGST declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['IMPGSEZA', 'Amendments to Import of Goods from SEZ units / developers on bill of entry', 'Cess', 'Amount of Cess declared by recipient (on IMS Dashboard) for ITC reduction'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available, then legal name of the supplier'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Invoice number', 'Invoice number'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Invoice type', "Invoice type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWIP - SEZ supplies with payment of tax\nSEZWOP - SEZ supplies without payment of tax\nDE - Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Invoice date', 'Invoice date format shall be DD-MM-YYYY'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Invoice value', 'Invoice value (in rupees)'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Place of supply', 'Place of supply shall be the place where goods are supplied or services provided (As declared by the supplier)'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Supply attract Reverse charge', "Supply attract reverse charge divided into two types:\nY- Purchases attract reverse charge\nN- Purchases don't attract reverse charge"],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Taxable value', 'Taxable value'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Cess', 'Cess amount (In rupees)'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Whether ITC to be reversed (Taxpayer\'s Input)', 'YES/NO - As per selection by recipient on IMS Dashboard'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Integrated Tax', 'Amount of IGST declared by recipient (on IMS Dashboard) for ITC reversal'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Central Tax', 'Amount of CGST declared by recipient (on IMS Dashboard) for ITC reversal'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'State/UT Tax', 'Amount of SGST declared by recipient (on IMS Dashboard) for ITC reversal'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Cess', 'Amount of Cess declared by recipient (on IMS Dashboard) for ITC reversal'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'ITC Availability', "Is ITC available or not on the document - 'Yes' or 'No'"],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Reason', "Reason, if ITC availability is 'No'"],
            ['B2B(ITC Reversal)', 'Taxable inward supplies received from registered person', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Invoice number (Original details)', 'Original invoice number'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Invoice date (Original details)', 'Original invoice date (Date format shall be DD-MM-YYYY)'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Invoice number', 'Revised Invoice number'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Invoice type', "Invoice type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWIP - SEZ supplies with payment of tax\nSEZWOP - SEZ supplies without payment of tax\nDE - Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Invoice date', 'Invoice date format shall be DD-MM-YYYY'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Invoice value', 'Invoice value (in rupees)'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the supplier)'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Supply attract Reverse charge', "Supply attract reverse charge divided into two types:\nY- Purchases attract reverse charge\nN- Purchases don't attract reverse charge"],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Taxable value', 'Taxable value (In rupees)'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'State/UT Tax', 'State/UT Tax amount (In rupees)'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Cess', 'Cess amount (In rupees)'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Whether ITC to be reversed (Taxpayer\'s Input)', 'YES/NO - As per selection by recipient on IMS Dashboard'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Integrated Tax', 'Amount of IGST declared by recipient (on IMS Dashboard) for ITC reversal'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Central Tax', 'Amount of CGST declared by recipient (on IMS Dashboard) for ITC reversal'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'State/UT Tax', 'Amount of SGST declared by recipient (on IMS Dashboard) for ITC reversal'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Cess', 'In case of GSTIN amendment, the value of cess to be reduced from ITC as entered by taxpayer (with incorrect GSTIN)'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'ITC Availability', "If ITC is available, 'Yes', else 'No'"],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Reason', "Reason, if ITC availability is 'No'"],
            ['B2BA(ITC Reversal)', 'Amendments to previously uploaded invoices by supplier', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2B-DNR', 'Debit notes(Original)', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2B-DNR', 'Debit notes(Original)', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['B2B-DNR', 'Debit notes(Original)', 'Note number', 'Debit note number'],
            ['B2B-DNR', 'Debit notes(Original)', 'Note type', 'Document type can be Debit note'],
            ['B2B-DNR', 'Debit notes(Original)', 'Note Supply Type', "Note Supply type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP- SEZ supplies with payment of tax\nSEZWOP- SEZ supplies without payment of tax\nDE- Deemed exports\nCBW- Intra-State Supplies attracting IGST"],
            ['B2B-DNR', 'Debit notes(Original)', 'Note date', 'Debit note date format shall be (DD-MM-YYYY)'],
            ['B2B-DNR', 'Debit notes(Original)', 'Note Value', 'Debit note value (In rupees)'],
            ['B2B-DNR', 'Debit notes(Original)', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the supplier)'],
            ['B2B-DNR', 'Debit notes(Original)', 'Supply attract Reverse charge', "Supply attract reverse charge divided into two types:\nY- Purchases attract reverse charge\nN- Purchases don't attract reverse charge"],
            ['B2B-DNR', 'Debit notes(Original)', 'Taxable value', 'Taxable value (In rupees)'],
            ['B2B-DNR', 'Debit notes(Original)', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2B-DNR', 'Debit notes(Original)', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2B-DNR', 'Debit notes(Original)', 'State/UT Tax', 'State/UT tax amount (In rupees)'],
            ['B2B-DNR', 'Debit notes(Original)', 'Cess', 'Cess amount (In rupees)'],
            ['B2B-DNR', 'Debit notes(Original)', 'GSTR-1/IFF Period', 'Period for which GSTR-1/IFF has been filed'],
            ['B2B-DNR', 'Debit notes(Original)', 'GSTR-1/IFF Filing Date', 'Date on which GSTR-1/IFF has been filed'],
            ['B2B-DNR', 'Debit notes(Original)', 'ITC Availability', "If ITC is available, 'Yes', else 'No'"],
            ['B2B-DNR', 'Debit notes(Original)', 'Reason', "Reason, if ITC availability is 'No'"],
            ['B2B-DNR', 'Debit notes(Original)', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2B-DNR', 'Debit notes(Original)', 'Source', "Source of the document shall be displayed. It shall be:\na. 'e-invoice', if the document is auto-populated from e-invoice.\nb. Blank, if the document is uploaded by the supplier"],
            ['B2B-DNR', 'Debit notes(Original)', 'IRN', "It is the unique Invoice reference number of the document auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank."],
            ['B2B-DNR', 'Debit notes(Original)', 'IRN date', "This is the date of invoice reference number, auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank."],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Note type(Original)', 'Note type can be Debit note'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Note number(Original)', 'Original Debit note number'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Note date(Original)', 'Original Debit note date (Note date format shall be DD-MM-YYYY)'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Note number', 'Debit note number'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Note type', 'Note type can be Debit note'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Note Supply Type', "Note Supply type can be derived based on the following types:\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP- SEZ supplies with payment of tax\nSEZWOP- SEZ supplies with out payment of tax\nDE- Deemed exports\nCBW- Intra-State Supplies attracting IGST"],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Note date', 'Debit note date format shall be (DD-MM-YYYY)'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Note Value', 'Debit note value (In rupees)'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the supplier)'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Supply attract Reverse charge', "Supply attract reverse charge divided into two types\nY- Purchases attract reverse charge\nN- Purchases don't attract reverse charge"],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Taxable value', 'Taxable value (In rupees)'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'State/UT tax', 'State/UT tax amount (In rupees)'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Cess', 'Cess amount (In rupees)'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'GSTR-1/IFF Period', 'Period for which GSTR-1/IFF has been filed'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'GSTR-1/IFF Filing Date', 'Date on which GSTR-1/IFF has been filed'],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'ITC Availability', "If ITC is available, 'Yes', else 'No'"],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Reason', "Reason, if ITC availability is 'No'"],
            ['B2B-DNRA', 'Amendments to previously uploaded Debit notes by supplier', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available, then legal name of the supplier'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Invoice number', 'Invoice number'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Invoice type', "Invoice type can be derived based on the following types\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP- SEZ supplies with payment of tax\nSEZWOP- SEZ supplies with out payment of tax\nDE- Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Invoice date', 'Invoice date format shall be DD-MM-YYYY'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Invoice value', 'Invoice value (in rupees)'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Place of supply', 'Place of supply shall be the place where goods are supplied or services are provided (As declared by the supplier)'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Taxable value', 'Taxable value'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'State/UT tax', 'State/UT tax amount (In rupees)'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Cess', 'Cess amount (In rupees)'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Remarks', 'Remark entered by recipient on IMS Dashboard at the time of rejection of record.'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'Source', "Source of the document shall be displayed. It shall be:\na. 'e-invoice', if the document is auto-populated from e-invoice.\nb. Blank, if the document is uploaded by the supplier"],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'IRN', "It is the unique Invoice reference number of the document auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank."],
            ['B2B-Rejected', 'ITC Rejected for taxable inward supplies received from registered persons', 'IRN date', "This is the date of invoice reference number, auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank."],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Invoice number (Original details)', 'Original invoice number'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Invoice date (Original details)', 'Original invoice date (Date format shall be DD-MM-YYYY)'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier.'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Invoice number', 'Revised Invoice number'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Invoice type', "Invoice type can be derived based on the following types\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP- SEZ supplies with payment of tax\nSEZWOP- SEZ supplies with out payment of tax\nDE- Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Invoice date', 'Invoice date format shall be DD-MM-YYYY'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Invoice value', 'Invoice value (in rupees)'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the supplier)'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Taxable value', 'Taxable value (In rupees)'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'State/UT tax', 'State/UT tax amount (In rupees)'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Cess', 'Cess amount (In rupees)'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Remarks', 'Remark entered by recipient on IMS Dashboard at the time of rejection of record.'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2BA-Rejected', 'ITC Rejected for amendments to previously filed invoices by supplier', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Note number', 'Debit/Credit note number'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Note type', 'Document type can be Debit note or credit note'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Note Supply Type', "Note Supply type can be derived based on the following types\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP- SEZ supplies with payment of tax\nSEZWOP- SEZ supplies with out payment of tax\nDE- Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Note date', 'Debit/Credit note date format shall be (DD-MM-YYYY)'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Note Value', 'Debit/Credit note value (In rupees)'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the supplier)'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Taxable value', 'Taxable value (In rupees)'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'State/UT tax', 'State/UT tax amount (In rupees)'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Cess', 'Cess amount (In rupees)'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Remarks', 'Remark entered by recipient on IMS Dashboard at the time of rejection of record.'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'Source', "Source of the document shall be displayed. It shall be:\na. 'e-invoice', if the document is auto-populated from e-invoice.\nb. Blank, if the document is uploaded by the supplier"],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'IRN', "It is the unique Invoice reference number of the document auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank."],
            ['B2B-CDNR-Rejected', 'ITC Rejected for Debit/Credit notes (Original)', 'IRN date', "This is the date of invoice reference number, auto-populated from e-invoice. For the documents uploaded by the supplier, this shall be blank."],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Note type(Original)', 'Note type can be Debit note or credit note'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Note number(Original)', 'Original Debit/Credit note number'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Note date(Original)', 'Original Debit/Credit note date (Note date format shall be DD-MM-YYYY)'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'GSTIN of Supplier', 'GSTIN of supplier'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Trade/Legal name', 'Trade name of the supplier will be displayed. If trade name is not available then legal name of the supplier'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Note number', 'Debit/Credit note number'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Note type', 'Note type can be Debit note or credit note'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Note Supply Type', "Note Supply type can be derived based on the following types\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP- SEZ supplies with payment of tax\nSEZWOP- SEZ supplies with out payment of tax\nDE- Deemed exports\nCBW - Intra-State Supplies attracting IGST"],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Note date', 'Debit/Credit note date format shall be (DD-MM-YYYY)'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Note Value', 'Debit/Credit note value (In rupees)'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the supplier)'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Taxable value', 'Taxable value (In rupees)'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'State/UT tax', 'State/UT tax amount (In rupees)'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Cess', 'Cess amount (In rupees)'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Remarks', 'Remark entered by recipient on IMS Dashboard at the time of rejection of record.'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'GSTR-1/IFF/GSTR-5 Period', 'Period for which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'GSTR-1/IFF/GSTR-5 Filing Date', 'Date on which GSTR-1/IFF/GSTR-5 has been filed'],
            ['B2B-CDNRA-Rejected', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier', 'Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, it shall be 65%, else blank"],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'GSTIN of ECO', 'GSTIN of E-commerce operator'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Trade/Legal name', 'Trade name of the E-commerce operator will be displayed. If trade name is not available, then legal name of the E-commerce operator'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Document number', 'Document number'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Document type', "Document type can be derived based on the following types\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP- SEZ supplies with payment of tax\nSEZWOP- SEZ supplies with out payment of tax\nDE- Deemed exports"],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Document date', 'Document date format shall be DD-MM-YYYY'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Document value', 'Document value (in rupees)'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Place of supply', 'Place of supply shall be the place where goods are supplied or services provided (As declared by the eco)'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Taxable value', 'Taxable value'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'State/UT tax', 'State/UT tax amount (In rupees)'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Cess', 'Cess amount (In rupees)'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Remarks', 'Remark entered by recipient on IMS Dashboard at the time of rejection of record.'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'GSTR-1/IFF period', 'Period for which GSTR-1/IFF has been filed'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'GSTR-1/IFF filing date', 'Date on which GSTR-1/IFF has been filed'],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'Source', "Source of the document shall be displayed. It shall be:\na. 'e-invoice', if the document is auto-populated from e-invoice.\nb. Blank, if the document is uploaded by the e commerce operator"],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'IRN', "It is the unique Invoice reference number of the document auto-populated from e-invoice. For the documents uploaded by the ecommerce operator, this shall be blank."],
            ['ECO-Rejected', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)', 'IRN date', "This is the date of invoice reference number, auto-populated from e-invoice. For the documents uploaded by the e commerce operator, this shall be blank."],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document number (Original details)', 'Document invoice number'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document date (Original details)', 'Document invoice date (Date format shall be DD-MM-YYYY)'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'GSTIN of ECO', 'GSTIN of E-commerce operator'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Trade/Legal name', 'Trade name of the E-commerce operator will be displayed. If trade name is not available then legal name of the E-commerce operator.'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document number', 'Revised Document number'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document type', "Document type can be derived based on the following types\nR- Regular (Other than SEZ supplies and Deemed exports)\nSEZWP- SEZ supplies with payment of tax\nSEZWOP- SEZ supplies with out payment of tax\nDE- Deemed exports"],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document date', 'Document date format shall be DD-MM-YYYY'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Document value', 'Document value (in rupees)'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Place of supply', 'Place of supply shall be the place where goods supplied or services provided (As declared by the Eco)'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Taxable value', 'Taxable value (In rupees)'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'State/UT tax', 'State/UT tax amount (In rupees)'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Cess', 'Cess amount (In rupees)'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'Remarks', 'Remark entered by recipient on IMS Dashboard at the time of rejection of record.'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'GSTR-1/IFF Period', 'Period for which GSTR-1/IFF has been filed'],
            ['ECOA-Rejected', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)', 'GSTR-1/IFF filing date', 'Date on which GSTR-1/IFF has been filed'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'GSTIN of ISD', 'Input Service Distributor GSTIN'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'Trade/Legal name of the ISD', 'Trade name of the ISD will be displayed. If trade name is not available then legal name of the ISD'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'ISD Document type', 'ISD document type can be Invoice or Credit note'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'ISD Document number', 'ISD invoice / ISD Credit note number'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'ISD Document date', 'ISD Document date format will be DD-MM-YYYY'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'Original ISD Invoice number', "This is applicable only if ISD document type is 'Credit note' is linked to invoice"],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'Original ISD Invoice date', "This is applicable only if ISD document type is 'Credit note' is linked to invoice"],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'State/UT tax', 'State/UT tax amount (In rupees)'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'Cess', 'Cess amount (In rupees)'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'ISD GSTR-6 Period', 'Period for which GSTR-6 is to be filed.'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'ISD GSTR-6 Filing date', 'Date on which GSTR-6 has been filed.'],
            ['ISD-Reject ed', 'ITC Rejected for ISD Credits', 'Eligibilty of ITC', "Eligibility of ITC are two types:\nY-Yes. Taxpayer can claim ITC on such invoice\nN- No. Taxpayer can't claim ITC on such invoice"],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'ISD Document type (Original)', 'ISD document type can be Invoice or Credit note'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'ISD Document Number (Original)', 'Invoice/Credit note number'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'ISD Document date (Original)', 'Invoice/Credit note date'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'GSTIN of ISD', 'GSTIN of the Input Service Distributor'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'Trade/Legal name of the ISD', 'Trade name of the ISD will be displayed. If trade name is not available then legal name of the ISD'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'ISD Document type', 'ISD document type can be Invoice or Credit note'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'ISD Document number', 'ISD invoice / ISD Credit note number'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'ISD Document date', 'ISD Document date format will be DD-MM-YYYY'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'Original ISD Invoice number', "This is applicable only if ISD document type is 'Credit note' is linked to invoice"],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'Original ISD Invoice date', "This is applicable only if ISD document type is 'Credit note' is linked to invoice"],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'Integrated Tax', 'Integrated Tax amount (In rupees)'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'Central Tax', 'Central Tax amount (In rupees)'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'State/UT tax', 'State/UT tax amount (In rupees)'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'Cess', 'Cess amount (In rupees)'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'ISD GSTR-6 Period', 'Period for which GSTR-6 is to be filed.'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'ISD GSTR-6 Filing date', 'Date on which GSTR-6 has been filed.'],
            ['ISDA-Reje cted', 'ITC Rejected for amendments of ISD Credits received', 'Eligibilty of ITC', "Eligibility of ITC are two types:\nY-Yes. Taxpayer can claim ITC on such invoice\nN- No. Taxpayer can't claim ITC on such invoice"]
        ];

        $startDataRow = $row;
        foreach ($instructions as $instruction) {
            $sheet1->setCellValue('A' . $row, $instruction[0]);
            $sheet1->setCellValue('B' . $row, $instruction[1]);
            $sheet1->setCellValue('C' . $row, $instruction[2]);

            // Merge D, E, F columns for instructions
            $sheet1->mergeCells("D{$row}:F{$row}");
            $sheet1->setCellValue("D{$row}", $instruction[3]);

            // 🔹 Background color for Column C
            $sheet1->getStyle("C{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7E6E6'] // Grey background
                ]
            ]);

            // 🔹 Apply styles to all columns (A-F) including merged D-F
            $sheet1->getStyle("A{$row}:F{$row}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ],
                'font' => [
                    'name' => 'Times New Roman'
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true // 🔹 Enable wrap text for auto row height
                ]
            ]);

            // 🔹 Explicitly ensure the merged range D-F has wrap text enabled
            $sheet1->getStyle("D{$row}:F{$row}")->getAlignment()->setWrapText(true);

            // 🔹 Set row height to -1 for automatic adjustment according to content
            $sheet1->getRowDimension($row)->setRowHeight(-1);

            $row++;
        }
        $endDataRow = $row - 1;

        // Alignment for Worksheet Name and Reference columns
        $sheet1->getStyle("A{$startDataRow}:B{$endDataRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            ]
        ]);

        // Merge B2B section
        $b2bStart = $startDataRow + 0;
        $b2bEnd = $startDataRow + 20;
        $sheet1->mergeCells("A{$b2bStart}:A{$b2bEnd}");
        $sheet1->mergeCells("B{$b2bStart}:B{$b2bEnd}");

        // Merge B2BA section
        $b2baStart = $startDataRow + 21;
        $b2baEnd = $startDataRow + 45;
        $sheet1->mergeCells("A{$b2baStart}:A{$b2baEnd}");
        $sheet1->mergeCells("B{$b2baStart}:B{$b2baEnd}");

        // Merge B2B-CDNR section
        $cdnrStart = $startDataRow + 46;
        $cdnrEnd = $startDataRow + 72;
        $sheet1->mergeCells("A{$cdnrStart}:A{$cdnrEnd}");
        $sheet1->mergeCells("B{$cdnrStart}:B{$cdnrEnd}");

        // Merge B2B-CDNRA section
        $cdnraStart = $startDataRow + 73;
        $cdnraEnd = $startDataRow + 99;
        $sheet1->mergeCells("A{$cdnraStart}:A{$cdnraEnd}");
        $sheet1->mergeCells("B{$cdnraStart}:B{$cdnraEnd}");

        // Merge ECO section
        $ecoStart = $startDataRow + 100;
        $ecoEnd = $startDataRow + 118;
        $sheet1->mergeCells("A{$ecoStart}:A{$ecoEnd}");
        $sheet1->mergeCells("B{$ecoStart}:B{$ecoEnd}");

        // Merge ECOA section
        $ecoaStart = $startDataRow + 119;
        $ecoaEnd = $startDataRow + 141;
        $sheet1->mergeCells("A{$ecoaStart}:A{$ecoaEnd}");
        $sheet1->mergeCells("B{$ecoaStart}:B{$ecoaEnd}");

        // Merge ISD section
        $isdStart = $startDataRow + 142;
        $isdEnd = $startDataRow + 155;
        $sheet1->mergeCells("A{$isdStart}:A{$isdEnd}");
        $sheet1->mergeCells("B{$isdStart}:B{$isdEnd}");

        // Merge ISDA section
        $isdaStart = $startDataRow + 156;
        $isdaEnd = $startDataRow + 172;
        $sheet1->mergeCells("A{$isdaStart}:A{$isdaEnd}");
        $sheet1->mergeCells("B{$isdaStart}:B{$isdaEnd}");

        // Merge IMPG section
        $impgStart = $startDataRow + 173;
        $impgEnd = $startDataRow + 179;
        $sheet1->mergeCells("A{$impgStart}:A{$impgEnd}");
        $sheet1->mergeCells("B{$impgStart}:B{$impgEnd}");

        // Merge IMPGA section
        $impgaStart = $startDataRow + 180;
        $impgaEnd = $startDataRow + 190;
        $sheet1->mergeCells("A{$impgaStart}:A{$impgaEnd}");
        $sheet1->mergeCells("B{$impgaStart}:B{$impgaEnd}");

        // Merge IMPGSEZ section
        $impgsezStart = $startDataRow + 191;
        $impgsezEnd = $startDataRow + 199;
        $sheet1->mergeCells("A{$impgsezStart}:A{$impgsezEnd}");
        $sheet1->mergeCells("B{$impgsezStart}:B{$impgsezEnd}");

        // Merge IMPGSEZA section
        $impgsezaStart = $startDataRow + 200;
        $impgsezaEnd = $startDataRow + 213;
        $sheet1->mergeCells("A{$impgsezaStart}:A{$impgsezaEnd}");
        $sheet1->mergeCells("B{$impgsezaStart}:B{$impgsezaEnd}");

        // Merge B2B(ITC Reversal) section
        $b2bitcrStart = $startDataRow + 214;
        $b2bitcrEnd = $startDataRow + 236;
        $sheet1->mergeCells("A{$b2bitcrStart}:A{$b2bitcrEnd}");
        $sheet1->mergeCells("B{$b2bitcrStart}:B{$b2bitcrEnd}");

        // Merge B2BA(ITC Reversal) section
        $b2baitcrStart = $startDataRow + 237;
        $b2baitcrEnd = $startDataRow + 261;
        $sheet1->mergeCells("A{$b2baitcrStart}:A{$b2baitcrEnd}");
        $sheet1->mergeCells("B{$b2baitcrStart}:B{$b2baitcrEnd}");

        // Merge B2B-DNR section
        $b2bdnrStart = $startDataRow + 262;
        $b2bdnrEnd = $startDataRow + 283;
        $sheet1->mergeCells("A{$b2bdnrStart}:A{$b2bdnrEnd}");
        $sheet1->mergeCells("B{$b2bdnrStart}:B{$b2bdnrEnd}");

        // Merge B2B-DNRA section
        $b2bdnraStart = $startDataRow + 284;
        $b2bdnraEnd = $startDataRow + 305;
        $sheet1->mergeCells("A{$b2bdnraStart}:A{$b2bdnraEnd}");
        $sheet1->mergeCells("B{$b2bdnraStart}:B{$b2bdnraEnd}");

        // Merge B2B-Rejected section
        $b2brejectedStart = $startDataRow + 306;
        $b2brejectedEnd = $startDataRow + 324;
        $sheet1->mergeCells("A{$b2brejectedStart}:A{$b2brejectedEnd}");
        $sheet1->mergeCells("B{$b2brejectedStart}:B{$b2brejectedEnd}");

        // Merge B2BA-Rejected section
        $b2barejectedStart = $startDataRow + 325;
        $b2barejectedEnd = $startDataRow + 342;
        $sheet1->mergeCells("A{$b2barejectedStart}:A{$b2barejectedEnd}");
        $sheet1->mergeCells("B{$b2barejectedStart}:B{$b2barejectedEnd}");

        // Merge B2B-CDNR-Rejected section
        $b2bcdnrrejectedStart = $startDataRow + 343;
        $b2bcdnrrejectedEnd = $startDataRow + 362;
        $sheet1->mergeCells("A{$b2bcdnrrejectedStart}:A{$b2bcdnrrejectedEnd}");
        $sheet1->mergeCells("B{$b2bcdnrrejectedStart}:B{$b2bcdnrrejectedEnd}");

        // Merge B2B-CDNRA-Rejected section
        $b2bcdnrarejectedStart = $startDataRow + 363;
        $b2bcdnrarejectedEnd = $startDataRow + 382;
        $sheet1->mergeCells("A{$b2bcdnrarejectedStart}:A{$b2bcdnrarejectedEnd}");
        $sheet1->mergeCells("B{$b2bcdnrarejectedStart}:B{$b2bcdnrarejectedEnd}");

        // Merge ECO-Rejected section
        $ecorejectedStart = $startDataRow + 383;
        $ecorejectedEnd = $startDataRow + 400;
        $sheet1->mergeCells("A{$ecorejectedStart}:A{$ecorejectedEnd}");
        $sheet1->mergeCells("B{$ecorejectedStart}:B{$ecorejectedEnd}");

        // Merge ECOA-Rejected section
        $ecoarejectedStart = $startDataRow + 401;
        $ecoarejectedEnd = $startDataRow + 417;
        $sheet1->mergeCells("A{$ecoarejectedStart}:A{$ecoarejectedEnd}");
        $sheet1->mergeCells("B{$ecoarejectedStart}:B{$ecoarejectedEnd}");

        // Merge ISD-Rejected section
        $isdrejectedStart = $startDataRow + 418;
        $isdrejectedEnd = $startDataRow + 431;
        $sheet1->mergeCells("A{$isdrejectedStart}:A{$isdrejectedEnd}");
        $sheet1->mergeCells("B{$isdrejectedStart}:B{$isdrejectedEnd}");

        // Merge ISDA-Rejected section
        $isdarejectedStart = $startDataRow + 432;
        $isdarejectedEnd = $startDataRow + 448;
        $sheet1->mergeCells("A{$isdarejectedStart}:A{$isdarejectedEnd}");
        $sheet1->mergeCells("B{$isdarejectedStart}:B{$isdarejectedEnd}");



        /* -------------------------------------------------
        | STYLE THE INSTRUCTIONS TABLE
        ------------------------------------------------- */
        $sheet1->getStyle('C' . $startDataRow . ':C' . $endDataRow)->getFont()->setBold(true);
    }

    private function addItcAvailableSheet($spreadsheet)
    {

        // $sheet = $spreadsheet->getActiveSheet();
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ITC Available');

        // Main Title
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'FORM GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 2: Advisory Info
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'FORM GSTR-2B has been generated on the basis of the information furnished by your suppliers in their respective FORMS GSTR-1/IFF, 5 and 6. It also contains information on imports of goods from the ICEGATE system. This information is for guidance purposes only.');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9, 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(2)->setRowHeight(40);

        // Row 3: Summary Title
        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A3', 'FORM SUMMARY - ITC Available');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
        ]);

        // Row 4: Headers
        $headers = ['S.no.', 'Heading', 'GSTR-3B table', 'Integrated Tax (₹)', 'Central Tax (₹)', 'State/UT Tax (₹)', 'Cess (₹)', 'Advisory'];
        $sheet->fromArray([$headers], null, 'A6');
        $sheet->mergeCells('H6:J6');
        $sheet->getStyle('A6:J6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText'   => true]
        ]);

        // Row 7: Section Title
        $sheet->mergeCells('A7:J7');
        $sheet->setCellValue('A7', 'Credit which may be availed under FORM GSTR-3B');
        $sheet->getStyle('A7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C65911']],
        ]);

        // Row 8: Part A Header
        $sheet->setCellValue('A8', 'Part A');
        $sheet->mergeCells('B8:G8');
        $sheet->setCellValue('B8', 'ITC Available - Credit may be claimed in relevant headings in GSTR-3B');
        $sheet->mergeCells('H8:J8');
        $sheet->getStyle('A8:J8')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
        ]);

        // Row 9: I - All other ITC
        $rowIndex = 9;
        $sheet->setCellValue('A' . $rowIndex, 'I');
        $sheet->setCellValue('B' . $rowIndex, 'All other ITC - Supplies from registered persons other than reverse charge (IMS)');
        $sheet->setCellValue('C' . $rowIndex, '4(A)(5)');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '8099.19');
        $sheet->setCellValue('F' . $rowIndex, '8099.19');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Net input tax credit may be availed under Table 4(A)(5) of FORM GSTR-3B.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsI = [
            ['B2B - Invoices (IMS)', '8099.19', '8099.19'],
            ['B2B - Debit notes (IMS)', '0.00', '0.00'],
            ['ECO - Documents (IMS)', '0.00', '0.00'],
            ['B2B - Invoices (Amendment) (IMS)', '0.00', '0.00'],
            ['B2B - Debit notes (Amendment) (IMS)', '0.00', '0.00'],
            ['ECO - Documents (Amendment) (IMS)', '0.00', '0.00'],
        ];

        $startDetailRow = $rowIndex + 1;
        foreach ($detailsI as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail[0]);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, $detail[1]);
            $sheet->setCellValue('F' . $rowIndex, $detail[2]);
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        // 🔹 Merge C10:C15 (GSTR-3B table column)
        $sheet->mergeCells("C{$startDetailRow}:C{$endDetailRow}");

        // 🔹 Apply grey background + center alignment to C10:C15
        $sheet->getStyle("C{$startDetailRow}:C{$endDetailRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'], // Excel light grey
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // 🔹 Merge H10:J15
        $sheet->mergeCells("H{$startDetailRow}:J{$endDetailRow}");

        // 🔹 Apply same grey background to H10:J15
        $sheet->getStyle("H{$startDetailRow}:J{$endDetailRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);



        // Row II
        $rowIndex++;
        $sheet->setCellValue('A' . $rowIndex, 'II');
        $sheet->setCellValue('B' . $rowIndex, 'Inward Supplies from ISD');
        $sheet->setCellValue('C' . $rowIndex, '4(A)(4)');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Net input tax credit may be availed under Table 4(A)(4) of FORM GSTR-3B.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsII = ['ISD - Invoices', 'ISD - Invoices (Amendment)'];
        $startDetailRow = $rowIndex + 1;
        foreach ($detailsII as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        // 🔹 Merge C17:C18
        $sheet->mergeCells("C{$startDetailRow}:C{$endDetailRow}");

        // 🔹 Apply grey background + center alignment to C17:C18
        $sheet->getStyle("C{$startDetailRow}:C{$endDetailRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // 🔹 Merge H17:J18
        $sheet->mergeCells("H{$startDetailRow}:J{$endDetailRow}");

        // 🔹 Apply same grey background to H17:J18
        $sheet->getStyle("H{$startDetailRow}:J{$endDetailRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        // Row III
        $rowIndex++;
        $sheet->setCellValue('A' . $rowIndex, 'III');
        $sheet->setCellValue('B' . $rowIndex, 'Inward Supplies liable for reverse charge');
        $sheet->setCellValue('C' . $rowIndex, "3.1(d)\n4(A)(3)");
        $sheet->getStyle('C' . $rowIndex)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "These supplies shall be declared in Table 3.1(d) of FORM GSTR-3B for payment of tax. Net input tax credit may be availed under Table 4(A)(3) of FORM GSTR-3B on payment of tax.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsIII = ['B2B - Invoices', 'B2B - Debit notes', 'B2B - Invoices (Amendment)', 'B2B - Debit notes (Amendment)'];
        $startDetailRow = $rowIndex + 1;
        foreach ($detailsIII as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        // 🔹 Merge C20:C23
        $sheet->mergeCells("C{$startDetailRow}:C{$endDetailRow}");

        // 🔹 Apply grey background + center alignment to C20:C23
        $sheet->getStyle("C{$startDetailRow}:C{$endDetailRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // 🔹 Merge H20:J23
        $sheet->mergeCells("H{$startDetailRow}:J{$endDetailRow}");

        // 🔹 Apply same grey background to H20:J23
        $sheet->getStyle("H{$startDetailRow}:J{$endDetailRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        // Row IV
        $rowIndex++;
        $sheet->setCellValue('A' . $rowIndex, 'IV');
        $sheet->setCellValue('B' . $rowIndex, 'Import of Goods');
        $sheet->setCellValue('C' . $rowIndex, '4(A)(1)');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Net input tax credit may be availed under Table 4(A)(1) of FORM GSTR-3B.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsIV = ['IMPG - Import of goods from overseas', 'IMPG (Amendment)', 'IMPGSEZ - Import of goods from SEZ', 'IMPGSEZ (Amendment)'];
        $startDetailRow = $rowIndex + 1;
        foreach ($detailsIV as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        // 🔹 Merge C25:C28
        $sheet->mergeCells("C{$startDetailRow}:C{$endDetailRow}");

        // 🔹 Apply grey background + center alignment to C25:C28
        $sheet->getStyle("C{$startDetailRow}:C{$endDetailRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // 🔹 Merge H25:J28
        $sheet->mergeCells("H{$startDetailRow}:J{$endDetailRow}");

        // 🔹 Apply same grey background to H25:J28
        $sheet->getStyle("H{$startDetailRow}:J{$endDetailRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        // Part B Section
        $rowIndex++;
        $sheet->mergeCells('A' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('A' . $rowIndex, 'Part B ITC Available - Credit notes should be net off against relevant ITC available headings in GSTR-3B');
        $sheet->getStyle('A' . $rowIndex)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C65911']],
        ]);

        $rowIndex++;
        $sheet->setCellValue('A' . $rowIndex, 'I');
        $sheet->setCellValue('B' . $rowIndex, 'Others');
        $sheet->setCellValue('C' . $rowIndex, '4(A)');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '167.40');
        $sheet->setCellValue('F' . $rowIndex, '167.40');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Credit Notes shall be net-off against relevant ITC available tables [Table 4A(3,4,5)]. Liability against Credit Notes (Reverse Charge) shall be net-off in Table 3.1(d).");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsB = [
            ['B2B - Credit notes (IMS)', '4(A)(5)'],
            ['B2B - Credit notes (Amendment) (IMS)', '4(A)(5)'],
            ['B2B - Credit notes (Reverse charge)', "3.1(d)\n4(A)(3)"],
            ['B2B - Credit notes (Reverse charge)(Amendment)', "3.1(d)\n4(A)(3)"],
            ['ISD - Credit notes', '4(A)(4)'],
            ['ISD - Credit notes (Amendment)', '4(A)(4)']
        ];
        $startDetailRow = $rowIndex + 1;
        foreach ($detailsB as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail[0]);
            $sheet->setCellValue('C' . $rowIndex, $detail[1]);
            $sheet->getStyle('C' . $rowIndex)->getAlignment()->setWrapText(true);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, ($detail[0] == 'B2B - Credit notes (IMS)') ? '167.40' : '0.00');
            $sheet->setCellValue('F' . $rowIndex, ($detail[0] == 'B2B - Credit notes (IMS)') ? '167.40' : '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        // 🔹 Merge H:J for Part B
        $sheet->mergeCells("H{$startDetailRow}:J{$endDetailRow}");

        // 🔹 Apply same grey background to H:J for Part B
        $sheet->getStyle("H{$startDetailRow}:J{$endDetailRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        // Styling and Borders
        $sheet->getStyle('A4:J' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(30);

        // Right align numeric values
        $sheet->getStyle('D4:G' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    private function addItcNotAvailableSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ITC not available');

        // Main Title
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'FORM GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 2: Advisory Info
        $sheet->mergeCells('A2:J4');
        $sheet->setCellValue('A2', 'FORM GSTR-2B has been generated on the basis of the information furnished by your suppliers in their respective FORMS GSTR-1/IFF, 5 and 6. It also contains information on imports of goods from the ICEGATE system. This information is for guidance purposes only.');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9, 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(2)->setRowHeight(40);

        // Row 5: Summary Title
        $sheet->mergeCells('A5:J5');
        $sheet->setCellValue('A5', 'FORM SUMMARY - ITC Not Available');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
        ]);

        // Row 6: Headers
        $headers = ['S.no.', 'Heading', 'GSTR-3B table', 'Integrated Tax (₹)', 'Central Tax (₹)', 'State/UT Tax (₹)', 'Cess (₹)', 'Advisory'];
        $sheet->fromArray([$headers], null, 'A6');
        $sheet->mergeCells('H6:J6');
        $sheet->getStyle('A6:J6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText'   => true]
        ]);

        // Row 8: Section Title
        $sheet->mergeCells('A8:J8');
        $sheet->setCellValue('A8', 'Credit which may not be availed under FORM GSTR-3B');
        $sheet->getStyle('A8')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C65911']],
        ]);

        // Row 9: Part A Header
        $sheet->setCellValue('A9', 'Part A');
        $sheet->mergeCells('B9:G9');
        $sheet->setCellValue('B9', 'ITC Not Available');
        $sheet->mergeCells('H9:J9');
        $sheet->getStyle('A9:J9')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
        ]);

        // Row 10: I - All other ITC
        $rowIndex = 10;
        $sheet->setCellValue('A' . $rowIndex, 'I');
        $sheet->setCellValue('B' . $rowIndex, 'All other ITC - Supplies from registered persons other than reverse charge');
        $sheet->setCellValue('C' . $rowIndex, '4(D)(2)');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Such credit shall not be taken and has to be reported in table 4(D)(2) of FORM GSTR-3B.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsI = [
            'B2B - Invoices',
            'B2B - Debit notes',
            'ECO - Documents',
            'B2B - Invoices (Amendment)',
            'B2B - Debit notes (Amendment)',
            'ECO - Documents (Amendment)'
        ];

        $startDetailRow = $rowIndex + 1;
        foreach ($detailsI as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        // Merge and style C and H:J detail rows
        $this->applyDetailStyles($sheet, $startDetailRow, $endDetailRow);

        // Row II
        $rowIndex++;
        $sheet->setCellValue('A' . $rowIndex, 'II');
        $sheet->setCellValue('B' . $rowIndex, 'Inward Supplies from ISD');
        $sheet->setCellValue('C' . $rowIndex, '4(D)(2)');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Such credit shall not be taken and has to be reported in table 4(D)(2) of FORM GSTR-3B.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsII = ['ISD - Invoices', 'ISD - Invoices (Amendment)'];
        $startDetailRow = $rowIndex + 1;
        foreach ($detailsII as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        $this->applyDetailStyles($sheet, $startDetailRow, $endDetailRow);

        // Row III
        $rowIndex++;
        $sheet->setCellValue('A' . $rowIndex, 'III');
        $sheet->setCellValue('B' . $rowIndex, 'Inward Supplies liable for reverse charge');
        $sheet->setCellValue('C' . $rowIndex, "3.1(d)\n4(D)(2)");
        $sheet->getStyle('C' . $rowIndex)->getAlignment()->setWrapText(true);
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "These supplies shall be declared in Table 3.1(d) of FORM GSTR-3B for payment of tax. However, credit will not be available on the same and has to be reported in table 4(D)(2) of FORM GSTR-3B.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsIII = ['B2B - Invoices', 'B2B - Debit notes', 'B2B - Invoices (Amendment)', 'B2B - Debit notes (Amendment)'];
        $startDetailRow = $rowIndex + 1;
        foreach ($detailsIII as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        $this->applyDetailStyles($sheet, $startDetailRow, $endDetailRow);

        // Part B Section
        $rowIndex++;
        $sheet->mergeCells('A' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('A' . $rowIndex, 'Part B ITC Not Available - Credit notes should be net off against relevant ITC available headings in GSTR-3B');
        $sheet->getStyle('A' . $rowIndex)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C65911']],
        ]);

        $rowIndex++;
        $sheet->setCellValue('A' . $rowIndex, 'I');
        $sheet->setCellValue('B' . $rowIndex, 'Others');
        $sheet->setCellValue('C' . $rowIndex, '4(A)');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Credit Notes shall be net-off against relevant ITC available tables [Table 4A(3,4,5)].");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsB = [
            ['B2B - Credit notes', '4(A)(5)'],
            ['B2B - Credit notes (Amendment)', '4(A)(5)'],
            ['B2B - Credit notes (Reverse charge)', "3.1(d)\n4(A)(3)"],
            ['B2B - Credit notes (Reverse charge)(Amendment)', "3.1(d)\n4(A)(3)"],
            ['ISD - Credit notes', '4(A)(4)'],
            ['ISD - Credit notes (Amendment)', '4(A)(4)']
        ];
        $startDetailRow = $rowIndex + 1;
        foreach ($detailsB as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail[0]);
            $sheet->setCellValue('C' . $rowIndex, $detail[1]);
            $sheet->getStyle('C' . $rowIndex)->getAlignment()->setWrapText(true);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        // Merge H:J for Part B details
        $sheet->mergeCells("H{$startDetailRow}:J{$endDetailRow}");
        $sheet->getStyle("H{$startDetailRow}:J{$endDetailRow}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
        ]);

        // Styling and Borders
        $sheet->getStyle('A6:J' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(30);

        // Right align numeric values
        $sheet->getStyle('D6:G' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    private function applyDetailStyles($sheet, $start, $end)
    {
        // Column C
        $sheet->mergeCells("C{$start}:C{$end}");
        $sheet->getStyle("C{$start}:C{$end}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Column H:J
        $sheet->mergeCells("H{$start}:J{$end}");
        $sheet->getStyle("H{$start}:J{$end}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
        ]);
    }

    private function addItcReversalSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ITC Reversal');

        // Main Title
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'FORM GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 2-4: Advisory Info
        $sheet->mergeCells('A2:J4');
        $sheet->setCellValue('A2', 'FORM GSTR-2B has been generated on the basis of the information furnished by your suppliers in their respective FORMS GSTR-1/IFF, 5 and 6. It also contains information on imports of goods from the ICEGATE system. This information is for guidance purposes only.');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9, 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);

        // Row 5: Summary Title
        $sheet->mergeCells('A5:J5');
        $sheet->setCellValue('A5', 'FORM SUMMARY - ITC Reversal');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
        ]);

        // Row 6: Headers
        $headers = ['S.no.', 'Heading', 'GSTR-3B table', 'Integrated Tax (₹)', 'Central Tax (₹)', 'State/UT Tax (₹)', 'Cess (₹)', 'Advisory'];
        $sheet->fromArray([$headers], null, 'A6');
        $sheet->mergeCells('H6:J6');
        $sheet->getStyle('A6:J6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText'   => true]
        ]);

        // Row 7: Section Title
        $sheet->mergeCells('A7:J7');
        $sheet->setCellValue('A7', 'Credit which may not be availed under FORM GSTR-3B');
        $sheet->getStyle('A7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C65911']],
        ]);

        // Row 8: Part A Header
        $sheet->setCellValue('A8', 'Part A');
        $sheet->mergeCells('B8:G8');
        $sheet->setCellValue('B8', 'ITC Reversed - Others');
        $sheet->mergeCells('H8:J8');
        $sheet->getStyle('A8:J8')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
        ]);

        // Row 9: I - ITC Reversal
        $rowIndex = 9;
        $sheet->setCellValue('A' . $rowIndex, 'I');
        $sheet->setCellValue('B' . $rowIndex, 'ITC Reversal on account of Rule 37A');
        $sheet->setCellValue('C' . $rowIndex, '4(B)(2)');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Such credit shall be reversed and has to be reported in table 4(B)(2) of FORM GSTR-3B.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $details = [
            'B2B - Invoices',
            'B2B - Debit notes',
            'B2B - Invoices (Amendment)',
            'B2B - Debit notes (Amendment)'
        ];

        $startDetailRow = $rowIndex + 1;
        foreach ($details as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        $this->applyDetailStyles($sheet, $startDetailRow, $endDetailRow);

        // Styling and Borders
        $sheet->getStyle('A6:J' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(30);

        // Right align numeric values
        $sheet->getStyle('D6:G' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    private function addItcRejectedSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ITC Rejected');

        // Main Title
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'FORM GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 2-4: Advisory Info
        $sheet->mergeCells('A2:J4');
        $sheet->setCellValue('A2', 'FORM GSTR-2B has been generated on the basis of the information furnished by your suppliers in their respective FORMS GSTR-1/IFF including E-Commerce supplies, 5 and 6. It also contains information on imports of goods from the ICEGATE system. This information is for guidance purposes only.');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9, 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);

        // Row 5: Summary Title
        $sheet->mergeCells('A5:J5');
        $sheet->setCellValue('A5', 'FORM SUMMARY - ITC Rejected');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
        ]);

        // Row 6: Headers
        $headers = ['S.no.', 'Heading', 'GSTR-3B table', 'Integrated Tax (₹)', 'Central Tax (₹)', 'State/UT Tax (₹)', 'Cess (₹)', 'Advisory'];
        $sheet->fromArray([$headers], null, 'A6');
        $sheet->mergeCells('H6:J6');
        $sheet->getStyle('A6:J6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText'   => true]
        ]);

        // Row 7: Section Title
        $sheet->mergeCells('A7:J7');
        $sheet->setCellValue('A7', 'Credit which is rejected on IMS Dashboard');
        $sheet->getStyle('A7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C65911']],
        ]);

        // Row 8: Part A Header
        $sheet->setCellValue('A8', 'Part A');
        $sheet->mergeCells('B8:G8');
        $sheet->setCellValue('B8', 'ITC Rejected - Others');
        $sheet->mergeCells('H8:J8');
        $sheet->getStyle('A8:J8')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
        ]);

        // Row 9: I - Category
        $rowIndex = 9;
        $sheet->setCellValue('A' . $rowIndex, 'I');
        $sheet->setCellValue('B' . $rowIndex, 'All other ITC - Supplies from registered persons other than reverse charge (IMS)');
        $sheet->setCellValue('C' . $rowIndex, 'NA');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Input tax credit cannot be availed in FORM GSTR-3B.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsI = [
            'B2B - Invoices (IMS)',
            'B2B - Debit notes (IMS)',
            'ECO - Documents (IMS)',
            'B2B - Invoices (Amendment) (IMS)',
            'B2B - Debit notes (Amendment) (IMS)',
            'ECO - Documents (Amendment) (IMS)'
        ];

        $startDetailRow = $rowIndex + 1;
        foreach ($detailsI as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        $this->applyDetailStyles($sheet, $startDetailRow, $endDetailRow);

        // Row 16: II - Category
        $rowIndex++;
        $sheet->setCellValue('A' . $rowIndex, 'II');
        $sheet->setCellValue('B' . $rowIndex, 'Inward Supplies from ISD');
        $sheet->setCellValue('C' . $rowIndex, 'NA');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "Input tax credit cannot be availed in FORM GSTR-3B.");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsII = ['ISD - Invoices', 'ISD - Invoices (Amendment)'];
        $startDetailRow = $rowIndex + 1;
        foreach ($detailsII as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        $this->applyDetailStyles($sheet, $startDetailRow, $endDetailRow);

        // Part B Section
        $rowIndex++;
        $sheet->mergeCells('A' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('A' . $rowIndex, 'Part B Rejected Records - Credit notes rejected on IMS Dashboard');
        $sheet->getStyle('A' . $rowIndex)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C65911']],
        ]);

        $rowIndex++;
        $sheet->setCellValue('A' . $rowIndex, 'I');
        $sheet->setCellValue('B' . $rowIndex, 'Others');
        $sheet->setCellValue('C' . $rowIndex, 'NA');
        $sheet->setCellValue('D' . $rowIndex, '0.00');
        $sheet->setCellValue('E' . $rowIndex, '0.00');
        $sheet->setCellValue('F' . $rowIndex, '0.00');
        $sheet->setCellValue('G' . $rowIndex, '0.00');
        $sheet->mergeCells('H' . $rowIndex . ':J' . $rowIndex);
        $sheet->setCellValue('H' . $rowIndex, "These Credit Notes are not eligible to net-off against relevant ITC available tables [Table 4A(4,5)].");
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $detailsB = [
            'B2B - Credit notes (IMS)',
            'B2B - Credit notes (Amendment) (IMS)',
            'ISD - Credit notes',
            'ISD - Credit notes (Amendment)'
        ];
        $startDetailRow = $rowIndex + 1;
        foreach ($detailsB as $detail) {
            $rowIndex++;
            $sheet->setCellValue('B' . $rowIndex, $detail);
            $sheet->setCellValue('C' . $rowIndex, 'NA');
            $sheet->setCellValue('D' . $rowIndex, '0.00');
            $sheet->setCellValue('E' . $rowIndex, '0.00');
            $sheet->setCellValue('F' . $rowIndex, '0.00');
            $sheet->setCellValue('G' . $rowIndex, '0.00');
        }
        $endDetailRow = $rowIndex;
        $sheet->mergeCells("A$startDetailRow:A$endDetailRow");
        $sheet->setCellValue("A$startDetailRow", "Details");
        $sheet->getStyle("A$startDetailRow")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(90);

        // Merge H:J for Part B details and style grey
        $sheet->mergeCells("H{$startDetailRow}:J{$endDetailRow}");
        $sheet->getStyle("H{$startDetailRow}:J{$endDetailRow}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
        ]);

        // Styling and Borders
        $sheet->getStyle('A6:J' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(30);

        // Right align numeric values
        $sheet->getStyle('D6:G' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    private function addB2BSheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();
        // dd($user);
        $role               = $user->role;
        $userId             = $user->id;
        $userBranchId       = $user->branch_id;
        $request            = request();
        $selectedSubAdminId = $request->selectedSubAdminId ?? $userId;
        if ($role === 'sub-admin') {
            $branch_id = $userId;
        } elseif ($role === 'admin' && ! empty($selectedSubAdminId)) {
            $subAdmin  = User::find($selectedSubAdminId);
            $branch_id = $subAdmin ? $subAdmin->id : $userId;
        } elseif ($role === 'staff') {
            $branch_id = $userBranchId;
        } else {
            $branch_id = $userId;
        }
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2B');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:U3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:U4');
        $sheet->setCellValue('A4', 'Taxable inward supplies received from registered persons');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of supplier',
            'B' => 'Trade/Legal name',
            'C' => 'Invoice Details',
            'G' => 'Place of supply',
            'H' => 'Supply Attract Reverse Charge',
            'I' => 'Taxable Value (₹)',
            'J' => 'Tax Amount',
            'N' => 'GSTR-1/IFF/GSTR-5 Period',
            'O' => 'GSTR-1/IFF/GSTR-5 Filing Date',
            'P' => 'ITC Availability',
            'Q' => 'Reason',
            'R' => 'Applicable % of Tax Rate',
            'S' => 'Source',
            'T' => 'IRN',
            'U' => 'IRN Date'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        // Sub-headers (Row 6)
        $sheet->setCellValue('C6', 'Invoice number');
        $sheet->setCellValue('D6', 'Invoice type');
        $sheet->setCellValue('E6', 'Invoice Date');
        $sheet->setCellValue('F6', 'Invoice Value(₹)');
        $sheet->setCellValue('J6', 'Integrated Tax(₹)');
        $sheet->setCellValue('K6', 'Central Tax(₹)');
        $sheet->setCellValue('L6', 'State/UT Tax(₹)');
        $sheet->setCellValue('M6', 'Cess(₹)');

        // Header Merges
        $sheet->mergeCells('A5:A6'); // GSTIN
        $sheet->mergeCells('B5:B6'); // Name
        $sheet->mergeCells('C5:F5'); // Invoice Details Group
        $sheet->mergeCells('G5:G6'); // POS
        $sheet->mergeCells('H5:H6'); // Reverse Charge
        $sheet->mergeCells('I5:I6'); // Taxable Value
        $sheet->mergeCells('J5:M5'); // Tax Amount Group
        $sheet->mergeCells('N5:N6'); // Period
        $sheet->mergeCells('O5:O6'); // Filing Date
        $sheet->mergeCells('P5:P6'); // ITC
        $sheet->mergeCells('Q5:Q6'); // Reason
        $sheet->mergeCells('R5:R6'); // %
        $sheet->mergeCells('S5:S6'); // Source
        $sheet->mergeCells('T5:T6'); // IRN
        $sheet->mergeCells('U5:U6'); // IRN Date

        $sheet->getStyle('A5:U6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        $invoices = PurchaseInvoice::with('vendor')
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->whereHas('vendor', function ($query) {
                $query->whereNotNull('gst_number')->where('gst_number', '!=', '');
            })
            ->get();
        $data = [];
        foreach ($invoices as $inv) {
            $cgstAmount = 0;
            $sgstAmount = 0;
            $igstAmount = 0;
            $cessAmount = 0;

            if (!empty($inv->taxes)) {
                $taxes = json_decode($inv->taxes, true);
                if (is_array($taxes)) {
                    foreach ($taxes as $tax) {
                        $name = strtoupper(trim($tax['name'] ?? $tax['tax_name'] ?? ''));
                        $amount = (float)($tax['amount'] ?? 0);
                        if ($name === 'CGST') $cgstAmount += $amount;
                        elseif ($name === 'SGST') $sgstAmount += $amount;
                        elseif ($name === 'IGST') $igstAmount += $amount;
                        elseif ($name === 'CESS') $cessAmount += $amount;
                    }
                }
            }

            $data[] = [
                $inv->vendor->gst_number ?? '',
                $inv->vendor->name ?? '',
                $inv->invoice_number,
                'Regular',
                Carbon::parse($inv->created_at)->format('d/m/Y'),
                number_format((float)$inv->grand_total, 2, '.', ''),
                $inv->vendor->state_code ?? '',
                'No',
                number_format((float)$inv->total_amount, 2, '.', ''),
                number_format($igstAmount, 2, '.', ''),
                number_format($cgstAmount, 2, '.', ''),
                number_format($sgstAmount, 2, '.', ''),
                number_format($cessAmount, 2, '.', ''),
                Carbon::parse($inv->created_at)->format('M\'y'),
                Carbon::parse($inv->created_at)->format('d/m/Y'),
                'Yes',
                '',
                '100%',
                'Blank',
                '',
                '',
            ];
        }

        $sheet->fromArray($data, null, 'A7');
        $lastRow = count($data) + 6;

        // Set bigger row height for data rows
        for ($i = 7; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(70);
        }

        // Styling and Borders
        $sheet->getStyle('A5:U' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Alignment
        $sheet->getStyle('A7:U' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A7:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G7:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('N7:S' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column widths
        foreach (range('A', 'U') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('T')->setWidth(40); // IRN
    }

    private function addB2BASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2BA');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:Y3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:Y4');
        $sheet->setCellValue('A4', 'Amendments to previously filed invoices by supplier');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 5: Section Headers
        $sheet->mergeCells('A5:B5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:B5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('C5:Y5');
        $sheet->setCellValue('C5', 'Revised Details');
        $sheet->getStyle('C5:Y5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 6 & 7: Headers
        $headers6 = [
            'A' => 'Invoice number',
            'B' => 'Invoice Date',
            'C' => 'GSTIN of supplier',
            'D' => 'Trade/Legal name',
            'E' => 'Invoice Details',
            'I' => 'Place of supply',
            'J' => 'Supply Attract Reverse Charge',
            'K' => 'Taxable Value (₹)',
            'L' => 'Tax Amount',
            'P' => 'Whether ITC to be reduced (Taxpayer\'s Input)',
            'Q' => 'Amount declared by taxpayer for ITC reduction',
            'U' => 'GSTR-1/IFF/GSTR-5 Period',
            'V' => 'GSTR-1/IFF/GSTR-5 Filing Date',
            'W' => 'ITC Availability',
            'X' => 'Reason',
            'Y' => 'Applicable % of Tax Rate'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        $headers7 = [
            'E' => 'Invoice number',
            'F' => 'Invoice type',
            'G' => 'Invoice Date',
            'H' => 'Invoice Value(₹)',
            'L' => 'Integrated Tax(₹)',
            'M' => 'Central Tax(₹)',
            'N' => 'State/UT Tax(₹)',
            'O' => 'Cess(₹)',
            'Q' => 'Integrated Tax(₹)',
            'R' => 'Central Tax(₹)',
            'S' => 'State/UT Tax(₹)',
            'T' => 'Cess(₹)'
        ];

        foreach ($headers7 as $col => $text) {
            $sheet->setCellValue($col . '7', $text);
        }

        // Header Merges
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:H6'); // Invoice Details Group
        $sheet->mergeCells('I6:I7');
        $sheet->mergeCells('J6:J7');
        $sheet->mergeCells('K6:K7');
        $sheet->mergeCells('L6:O6'); // Tax Amount Group
        $sheet->mergeCells('P6:P7');
        $sheet->mergeCells('Q6:T6'); // Reduction Group
        $sheet->mergeCells('U6:U7');
        $sheet->mergeCells('V6:V7');
        $sheet->mergeCells('W6:W7');
        $sheet->mergeCells('X6:X7');
        $sheet->mergeCells('Y6:Y7');

        // Apply header styling
        $sheet->getStyle('A6:Y7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(30);
        $sheet->getRowDimension(7)->setRowHeight(30);

        // Sample Data Rows
        // $data = [
        //     [
        //         'INV/24-25/001',
        //         '01/11/2024',
        //         '24AAAAA0000A1Z5',
        //         'ABC TRADERS',
        //         'INV/24-25/001A',
        //         'Regular',
        //         '05/11/2024',
        //         '10000.00',
        //         'Gujarat',
        //         'No',
        //         '8474.58',
        //         '1525.42',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         'No',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         'Nov-24',
        //         '11/12/2024',
        //         'Yes',
        //         '',
        //         '100%'
        //     ],
        // ];

        // $sheet->fromArray($data, null, 'A8');
        // $lastRow = count($data) + 7;

        // // Set row heights for data
        // for ($i = 8; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(40);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:Y' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // // Alignment for data
        // $sheet->getStyle('A8:Y' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        // $sheet->getStyle('E8:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle('L8:O' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        // $sheet->getStyle('Q8:T' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Column widths
        foreach (range('A', 'Y') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
    }

    private function addB2B_CDNRSheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();

        $role               = $user->role;
        $userId             = $user->id;
        $userBranchId       = $user->branch_id;
        $request            = request();
        $selectedSubAdminId = $request->selectedSubAdminId ?? $userId;

        if ($role === 'sub-admin') {
            $branch_id = $userId;
        } elseif ($role === 'admin' && !empty($selectedSubAdminId)) {
            $subAdmin  = User::find($selectedSubAdminId);
            $branch_id = $subAdmin ? $subAdmin->id : $userId;
        } elseif ($role === 'staff') {
            $branch_id = $userBranchId;
        } else {
            $branch_id = $userId;
        }
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2B-CDNR');

        // 🔹 FETCH DATA (Laravel Query Builder)
        $debitQuery = DB::table('debit_notes_type as dn')
            ->where('dn.isDeleted', 0)
            ->whereIn('dn.transaction_type', ['payment', 'receipt'])
            ->where('dn.branch_id', $branch_id) // 🔹 Branch filter
            ->leftJoin('purchase_invoice as pi', 'pi.id', '=', 'dn.purchase_id')
            // ->leftJoin('users as v', function ($join) {
            //     $join->on('v.id', '=', 'pi.vendor_id')
            //         ->whereNotNull('v.gst_number');
            // })

            ->leftJoin('users as v', 'v.id', '=', 'pi.vendor_id') // 🔹 Fix: simple left join


            ->select([
                'dn.id',
                'dn.invoice_number as note_number',

                DB::raw("
            CASE 
                WHEN dn.transaction_type = 'receipt' THEN 'C'
                ELSE 'D'
            END as note_type
        "),

                'dn.created_at as note_date',
                'dn.settlement_amount as note_value',

                'v.gst_number as supplier_gstin',
                'v.name as supplier_name',
                'v.state_code as place_of_supply',

                DB::raw('0 as taxable_value'),
                DB::raw('NULL as gst_json')
            ]);

        $creditQuery = DB::table('credit_note_items as cni')
            ->where('cni.isDeleted', 0)
            ->where('cni.branch_id', $branch_id) // 🔹 Branch filter
            ->leftJoin('credit_notes_type as cn', 'cn.id', '=', 'cni.credite_note_id')
            ->leftJoin('purchase_invoice as pi', 'pi.id', '=', 'cni.purchase_id')
            // ->leftJoin('users as v', function ($join) {
            //     $join->on('v.id', '=', 'pi.vendor_id')
            //         ->whereNotNull('v.gst_number');
            // })
            ->leftJoin('users as v', 'v.id', '=', 'pi.vendor_id') // 🔹 Fix: simple left join

            ->select([
                'cn.id',
                DB::raw('pi.invoice_number COLLATE utf8mb4_unicode_ci as note_number'),

                DB::raw("'C' as note_type"),

                'cn.created_at as note_date',
                DB::raw('SUM(cni.settlement_amount) as note_value'),

                'v.gst_number as supplier_gstin',
                'v.name as supplier_name',
                'v.state_code as place_of_supply',

                DB::raw('0 as taxable_value'),
                DB::raw('GROUP_CONCAT(cni.product_gst_details) as gst_json')
            ])
            ->groupBy(
                'cn.id',
                'cn.created_at',
                'v.gst_number',
                'v.name',
                'v.state_code',
                'pi.invoice_number'
            );
        $purchaseReturnQuery = DB::table('purchase_returns as pr')
            ->where('pr.isDeleted', 0)
            ->where('pr.branch_id', $branch_id)

            ->leftJoin('purchase_invoice as pi', 'pi.id', '=', 'pr.purchase_id')
            // ->leftJoin('users as v', function ($join) {
            //     $join->on('v.id', '=', 'pi.vendor_id')
            //         ->whereNotNull('v.gst_number');
            // })
            ->leftJoin('users as v', 'v.id', '=', 'pi.vendor_id') // 🔹 Fix: simple left join
            ->leftJoin('purchase_return_items as pri', 'pri.purchase_return_id', '=', 'pr.id')

            ->select([
                'pr.id',
                'pr.return_no as note_number',

                DB::raw("'C' as note_type"),

                'pr.created_at as note_date',
                'pr.total_amount as note_value',

                'v.gst_number as supplier_gstin',
                'v.name as supplier_name',
                'v.state_code as place_of_supply',

                'pr.subtotal as taxable_value',
                DB::raw('GROUP_CONCAT(pri.product_gst_details) as gst_json')
            ])
            ->groupBy(
                'pr.id',
                'pr.return_no',
                'pr.created_at',
                'pr.total_amount',
                'pr.subtotal',
                'v.gst_number',
                'v.name',
                'v.state_code'
            );


        $rows = $debitQuery
            ->unionAll($creditQuery)
            ->unionAll($purchaseReturnQuery)
            ->get();

        // dd($rows);


        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:AA3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 27],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:AA4');
        $sheet->setCellValue('A4', 'Debit/Credit notes (Original)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 5 & 6: Headers
        $headers5 = [
            'A' => 'GSTIN of supplier',
            'B' => 'Trade/Legal name',
            'C' => 'Credit note/Debit note details',
            'H' => 'Place of supply',
            'I' => 'Supply Attract Reverse Charge',
            'J' => 'Taxable Value (₹)',
            'K' => 'Tax Amount',
            'O' => 'Whether ITC to be reduced (Taxpayer\'s Input)',
            'P' => 'Amount declared by taxpayer for ITC reduction',
            'T' => 'GSTR-1/IFF/GSTR-5 Period',
            'U' => 'GSTR-1/IFF/GSTR-5 Filing Date',
            'V' => 'ITC Availability',
            'W' => 'Reason',
            'X' => 'Applicable % of Tax Rate',
            'Y' => 'Source',
            'Z' => 'IRN',
            'AA' => 'IRN Date'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        $headers6 = [
            'C' => 'Note number',
            'D' => 'Note type',
            'E' => 'Note Supply type',
            'F' => 'Note date',
            'G' => 'Note Value (₹)',
            'K' => 'Integrated Tax(₹)',
            'L' => 'Central Tax(₹)',
            'M' => 'State/UT Tax(₹)',
            'N' => 'Cess(₹)',
            'P' => 'Integrated Tax(₹)',
            'Q' => 'Central Tax(₹)',
            'R' => 'State/UT Tax(₹)',
            'S' => 'Cess(₹)'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Header Merges
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:G5'); // Note details group
        $sheet->mergeCells('H5:H6');
        $sheet->mergeCells('I5:I6');
        $sheet->mergeCells('J5:J6');
        $sheet->mergeCells('K5:N5'); // Tax Amount group
        $sheet->mergeCells('O5:O6');
        $sheet->mergeCells('P5:S5'); // Reduction group
        $sheet->mergeCells('T5:T6');
        $sheet->mergeCells('U5:U6');
        $sheet->mergeCells('V5:V6');
        $sheet->mergeCells('W5:W6');
        $sheet->mergeCells('X5:X6');
        $sheet->mergeCells('Y5:Y6');
        $sheet->mergeCells('Z5:Z6');
        $sheet->mergeCells('AA5:AA6');

        // Apply header styling
        $sheet->getStyle('A5:AA6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        $rowNum = 7;

        foreach ($rows as $r) {

            $igst = 0;
            $cgst = 0;
            $sgst = 0;
            $cess = 0;

            // if (!empty($r->gst_json)) {

            //     $json = trim($r->gst_json);

            //     // ✅ If already valid JSON array
            //     if (str_starts_with($json, '[')) {
            //         $gstItems = json_decode($json, true);
            //     }
            //     // ✅ If GROUP_CONCAT string
            //     else {
            //         $gstItems = json_decode('[' . $json . ']', true);
            //     }

            //     if (is_array($gstItems)) {
            //         foreach ($gstItems as $item) {
            //             if (!isset($item['name'])) continue;

            //             switch (strtoupper($item['name'])) {
            //                 case 'IGST':
            //                     $igst += (float)$item['amount'];
            //                     break;
            //                 case 'CGST':
            //                     $cgst += (float)$item['amount'];
            //                     break;
            //                 case 'SGST':
            //                     $sgst += (float)$item['amount'];
            //                     break;
            //                 case 'CESS':
            //                     $cess += (float)$item['amount'];
            //                     break;
            //             }
            //         }
            //     }
            // }

            if (!empty($r->gst_json)) {

                $json = trim($r->gst_json);

                // ✅ FIX INVALID JSON: wrap multiple arrays
                if (str_contains($json, '],[')) {
                    $json = '[' . $json . ']';
                }

                $gstItems = json_decode($json, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Optional: log error for debugging
                    // logger('Invalid GST JSON', ['json' => $r->gst_json]);
                    $gstItems = [];
                }

                foreach ($gstItems as $item) {

                    // CASE 1: Nested array
                    if (isset($item[0]) && is_array($item[0])) {
                        foreach ($item as $subItem) {
                            if (!isset($subItem['name'])) continue;

                            switch (strtoupper($subItem['name'])) {
                                case 'IGST':
                                    $igst += (float)$subItem['amount'];
                                    break;
                                case 'CGST':
                                    $cgst += (float)$subItem['amount'];
                                    break;
                                case 'SGST':
                                    $sgst += (float)$subItem['amount'];
                                    break;
                                case 'CESS':
                                    $cess += (float)$subItem['amount'];
                                    break;
                            }
                        }
                    }
                    // CASE 2: Single GST object
                    else {
                        if (!isset($item['name'])) continue;

                        switch (strtoupper($item['name'])) {
                            case 'IGST':
                                $igst += (float)$item['amount'];
                                break;
                            case 'CGST':
                                $cgst += (float)$item['amount'];
                                break;
                            case 'SGST':
                                $sgst += (float)$item['amount'];
                                break;
                            case 'CESS':
                                $cess += (float)$item['amount'];
                                break;
                        }
                    }
                }
            }




            $sheet->setCellValue('A' . $rowNum, $r->supplier_gstin);
            $sheet->setCellValue('B' . $rowNum, $r->supplier_name);

            $sheet->setCellValue('C' . $rowNum, $r->note_number);
            $sheet->setCellValue('D' . $rowNum, ucfirst($r->note_type));
            $sheet->setCellValue('E' . $rowNum, 'Regular');
            $sheet->setCellValue('F' . $rowNum, date('d-m-Y', strtotime($r->note_date)));
            $sheet->setCellValue('G' . $rowNum, $r->note_value);

            $stateCode = str_pad($r->place_of_supply, 2, '0', STR_PAD_LEFT);
            $stateName = $this->stateName($stateCode);

            $sheet->setCellValue(
                'H' . $rowNum,
                $stateName ? ($stateCode . '-' . $stateName) : $stateCode
            );
            $sheet->setCellValue('I' . $rowNum, '0.00');

            $sheet->setCellValue('J' . $rowNum, $r->taxable_value);

            $sheet->setCellValue('K' . $rowNum, $igst);
            $sheet->setCellValue('L' . $rowNum, $cgst);
            $sheet->setCellValue('M' . $rowNum, $sgst);
            $sheet->setCellValue('N' . $rowNum, $cess);

            $sheet->setCellValue('O' . $rowNum, 'No');
            $sheet->setCellValue('V' . $rowNum, 'Yes');
            $sheet->setCellValue('X' . $rowNum, '100%');
            $sheet->setCellValue('Y' . $rowNum, 'Supplier Uploaded');

            $rowNum++;
        }

        // Column widths
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('AA')->setWidth(15);
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('Z')->setWidth(40);
    }

    private function addB2B_CDNRASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2B-CDNRA');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:AA3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:AA4');
        $sheet->setCellValue('A4', 'Amendments to previously filed Credit/Debit notes by supplier');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 5: Section Headers
        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('D5:AA5');
        $sheet->setCellValue('D5', 'Revised Details');
        $sheet->getStyle('D5:AA5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 6 & 7: Headers
        $headers6 = [
            'A' => 'Note type',
            'B' => 'Note number',
            'C' => 'Note date',
            'D' => 'GSTIN of supplier',
            'E' => 'Trade/Legal name',
            'F' => 'Credit note/Debit note details',
            'K' => 'Place of supply',
            'L' => 'Supply Attract Reverse Charge',
            'M' => 'Taxable Value (₹)',
            'N' => 'Tax Amount',
            'R' => 'Whether ITC to be reduced (Taxpayer\'s Input)',
            'S' => 'Amount declared by taxpayer for ITC reduction',
            'W' => 'GSTR-1/IFF/GSTR-5 Period',
            'X' => 'GSTR-1/IFF/GSTR-5 Filing Date',
            'Y' => 'ITC Availability',
            'Z' => 'Reason',
            'AA' => 'Applicable % of Tax Rate'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        $headers7 = [
            'F' => 'Note number',
            'G' => 'Note type',
            'H' => 'Note Supply type',
            'I' => 'Note date',
            'J' => 'Note Value (₹)',
            'N' => 'Integrated Tax(₹)',
            'O' => 'Central Tax(₹)',
            'P' => 'State/UT Tax(₹)',
            'Q' => 'Cess(₹)',
            'S' => 'Integrated Tax(₹)',
            'T' => 'Central Tax(₹)',
            'U' => 'State/UT Tax(₹)',
            'V' => 'Cess(₹)'
        ];

        foreach ($headers7 as $col => $text) {
            $sheet->setCellValue($col . '7', $text);
        }

        // Header Merges
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:E7');
        $sheet->mergeCells('F6:J6'); // Note details group
        $sheet->mergeCells('K6:K7');
        $sheet->mergeCells('L6:L7');
        $sheet->mergeCells('M6:M7');
        $sheet->mergeCells('N6:Q6'); // Tax Amount group
        $sheet->mergeCells('R6:R7');
        $sheet->mergeCells('S6:V6'); // Reduction group
        $sheet->mergeCells('W6:W7');
        $sheet->mergeCells('X6:X7');
        $sheet->mergeCells('Y6:Y7');
        $sheet->mergeCells('Z6:Z7');
        $sheet->mergeCells('AA6:AA7');

        // Apply header styling
        $sheet->getStyle('A6:AA7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(35);
        $sheet->getRowDimension(7)->setRowHeight(35);

        // Dummy Data Rows
        // $data = [
        //     [
        //         'Credit Note',
        //         'CN/001',
        //         '01/10/2024',
        //         '24AAAAA0000A1Z5',
        //         'ABC TRADERS',
        //         'CN/001A',
        //         'Credit Note',
        //         'Regular',
        //         '05/10/2024',
        //         '500.00',
        //         'Gujarat',
        //         'No',
        //         '423.73',
        //         '76.27',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         'No',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         'Oct-24',
        //         '11/11/2024',
        //         'Yes',
        //         '',
        //         '100%'
        //     ],
        // ];

        // $sheet->fromArray($data, null, 'A8');
        // $lastRow = count($data) + 7;

        // // Set row heights for data
        // for ($i = 8; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(40);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:AA' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('A8:AA' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Column widths
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('AA')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(30);
    }

    private function addECOSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ECO');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:S3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:S4');
        $sheet->setCellValue('A4', 'Documents reported by ECO on which ECO is liable to pay tax u/s 9(5)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of ECO',
            'B' => 'Trade/Legal name',
            'C' => 'Document details',
            'G' => 'Place of supply',
            'H' => 'Taxable value (₹)',
            'I' => 'Tax amount',
            'M' => 'GSTR-1/1A/IFF period',
            'N' => 'GSTR-1/1A/IFF filing date',
            'O' => 'ITC availability',
            'P' => 'Reason',
            'Q' => 'Source',
            'R' => 'IRN',
            'S' => 'IRN Date'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        $headers6 = [
            'C' => 'Document number',
            'D' => 'Document type',
            'E' => 'Document date',
            'F' => 'Document value(₹)',
            'I' => 'Integrated Tax(₹)',
            'J' => 'Central Tax(₹)',
            'K' => 'State/UT Tax(₹)',
            'L' => 'Cess(₹)'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Header Merges
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:F5'); // Document details group
        $sheet->mergeCells('G5:G6');
        $sheet->mergeCells('H5:H6');
        $sheet->mergeCells('I5:L5'); // Tax amount group
        $sheet->mergeCells('M5:M6');
        $sheet->mergeCells('N5:N6');
        $sheet->mergeCells('O5:O6');
        $sheet->mergeCells('P5:P6');
        $sheet->mergeCells('Q5:Q6');
        $sheet->mergeCells('R5:R6');
        $sheet->mergeCells('S5:S6');

        // Apply header styling
        $sheet->getStyle('A5:S6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        // Dummy Data Rows
        // $data = [
        //     ['24ABCDE1234F1Z5', 'ECO SERVICES PVT LTD', 'DOC/001', 'Invoice', '01/11/2025', '5000.00', 'Gujarat', '4761.90', '238.10', '0.00', '0.00', '0.00', "Nov'25", '11/12/2025', 'Yes', '', 'E-Invoice', 'irn_hash_example_123', '01/12/2025'],
        // ];

        // $sheet->fromArray($data, null, 'A7');
        // $lastRow = count($data) + 6;

        // // Set row heights for data
        // for ($i = 7; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(50);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:S' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('A7:S' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Column widths
        foreach (range('A', 'S') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('R')->setWidth(40);
    }

    private function addECOASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ECOA');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:W3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:W4');
        $sheet->setCellValue('A4', 'Amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 5: Section Headers
        $sheet->mergeCells('A5:B5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:B5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('C5:W5');
        $sheet->setCellValue('C5', 'Revised Details');
        $sheet->getStyle('C5:W5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 6 & 7: Headers
        $headers6 = [
            'A' => 'Document number',
            'B' => 'Document date',
            'C' => 'GSTIN of ECO',
            'D' => 'Trade/Legal name',
            'E' => 'Document details',
            'I' => 'Place of supply',
            'J' => 'Taxable value (₹)',
            'K' => 'Tax amount',
            'O' => 'Whether ITC to be reduced (Taxpayer\'s Input)',
            'P' => 'Amount declared by taxpayer for ITC reduction',
            'T' => 'GSTR-1/1A/IFF period',
            'U' => 'GSTR-1/1A/IFF filing date',
            'V' => 'ITC availability',
            'W' => 'Reason'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        $headers7 = [
            'E' => 'Document number',
            'F' => 'Document type',
            'G' => 'Document date',
            'H' => 'Document value(₹)',
            'K' => 'Integrated Tax(₹)',
            'L' => 'Central Tax(₹)',
            'M' => 'State/UT Tax(₹)',
            'N' => 'Cess(₹)',
            'P' => 'Integrated Tax(₹)',
            'Q' => 'Central Tax(₹)',
            'R' => 'State/UT Tax(₹)',
            'S' => 'Cess(₹)'
        ];

        foreach ($headers7 as $col => $text) {
            $sheet->setCellValue($col . '7', $text);
        }

        // Header Merges
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:H6'); // Document details group
        $sheet->mergeCells('I6:I7');
        $sheet->mergeCells('J6:J7');
        $sheet->mergeCells('K6:N6'); // Tax amount group
        $sheet->mergeCells('O6:O7');
        $sheet->mergeCells('P6:S6'); // Reduction group
        $sheet->mergeCells('T6:T7');
        $sheet->mergeCells('U6:U7');
        $sheet->mergeCells('V6:V7');
        $sheet->mergeCells('W6:W7');

        // Apply header styling
        $sheet->getStyle('A6:W7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(35);
        $sheet->getRowDimension(7)->setRowHeight(35);

        // Dummy Data Rows
        // $data = [
        //     [
        //         'DOC/001',
        //         '01/10/2024',
        //         '24ABCDE1234F1Z5',
        //         'ECO SERVICES PVT LTD',
        //         'DOC/001A',
        //         'Invoice',
        //         '05/10/2024',
        //         '5000.00',
        //         'Gujarat',
        //         '4761.90',
        //         '238.10',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         'No',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         'Oct-24',
        //         '11/11/2024',
        //         'Yes',
        //         ''
        //     ],
        // ];

        // $sheet->fromArray($data, null, 'A8');
        // $lastRow = count($data) + 7;

        // // Set row heights for data
        // for ($i = 8; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(40);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:W' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('A8:W' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Column widths
        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
    }

    private function addISDSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ISD');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:N3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:N4');
        $sheet->setCellValue('A4', 'ISD Credits');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of ISD',
            'B' => 'Trade/Legal name',
            'C' => 'ISD Document type',
            'D' => 'ISD Document number',
            'E' => 'ISD Document date',
            'F' => 'Original Invoice Number',
            'G' => 'Original invoice date',
            'H' => 'Input tax distribution by ISD',
            'L' => 'ISD GSTR-6 Period',
            'M' => 'ISD GSTR-6 Filing Date',
            'N' => 'Eligibility of ITC'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        $headers6 = [
            'H' => 'Integrated Tax(₹)',
            'I' => 'Central Tax(₹)',
            'J' => 'State/UT Tax(₹)',
            'K' => 'Cess(₹)'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Header Merges
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('D5:D6');
        $sheet->mergeCells('E5:E6');
        $sheet->mergeCells('F5:F6');
        $sheet->mergeCells('G5:G6');
        $sheet->mergeCells('H5:K5'); // Input tax group
        $sheet->mergeCells('L5:L6');
        $sheet->mergeCells('M5:M6');
        $sheet->mergeCells('N5:N6');

        // Apply header styling
        $sheet->getStyle('A5:N6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        // Dummy Data Rows
        // $data = [
        //     ['24AAAAA0000A1Z5', 'ISD SUPPLIER LTD', 'ISD Invoice', 'ISD/001', '01/11/2025', 'INV/123', '25/10/2025', '1000.00', '0.00', '0.00', '0.00', "Nov'25", '13/12/2025', 'Yes'],
        // ];

        // $sheet->fromArray($data, null, 'A7');
        // $lastRow = count($data) + 6;

        // // Set row heights for data
        // for ($i = 7; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(40);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:N' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('A7:N' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Column widths
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
    }

    private function addISDASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ISDA');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:Q3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:Q4');
        $sheet->setCellValue('A4', 'Amendments ISD Credits received');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 5: Section Headers
        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('D5:Q5');
        $sheet->setCellValue('D5', 'Revised Details');
        $sheet->getStyle('D5:Q5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 6 & 7: Headers
        $headers6 = [
            'A' => 'ISD Document type',
            'B' => 'Document Number',
            'C' => 'Document date',
            'D' => 'GSTIN of ISD',
            'E' => 'Trade/Legal name',
            'F' => 'ISD Document type',
            'G' => 'ISD Document number',
            'H' => 'ISD Document date',
            'I' => 'Original Invoice Number',
            'J' => 'Original invoice date',
            'K' => 'Input tax distribution by ISD',
            'O' => 'ISD GSTR-6 Period',
            'P' => 'ISD GSTR-6 Filing Date',
            'Q' => 'Eligibility of ITC'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        $headers7 = [
            'K' => 'Integrated Tax(₹)',
            'L' => 'Central Tax(₹)',
            'M' => 'State/UT Tax(₹)',
            'N' => 'Cess(₹)'
        ];

        foreach ($headers7 as $col => $text) {
            $sheet->setCellValue($col . '7', $text);
        }

        // Header Merges
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:E7');
        $sheet->mergeCells('F6:F7');
        $sheet->mergeCells('G6:G7');
        $sheet->mergeCells('H6:H7');
        $sheet->mergeCells('I6:I7');
        $sheet->mergeCells('J6:J7');
        $sheet->mergeCells('K6:N6'); // Input tax group
        $sheet->mergeCells('O6:O7');
        $sheet->mergeCells('P6:P7');
        $sheet->mergeCells('Q6:Q7');

        // Apply header styling
        $sheet->getStyle('A6:Q7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(35);
        $sheet->getRowDimension(7)->setRowHeight(35);

        // Dummy Data Rows
        // $data = [
        //     [
        //         'ISD Invoice',
        //         'ISD/001',
        //         '01/10/2024',
        //         '24AAAAA0000A1Z5',
        //         'ISD SUPPLIER LTD',
        //         'ISD Invoice',
        //         'ISD/001A',
        //         '05/10/2024',
        //         'INV/123',
        //         '25/09/2024',
        //         '1000.00',
        //         '0.00',
        //         '0.00',
        //         '0.00',
        //         'Oct-24',
        //         '13/11/2024',
        //         'Yes'
        //     ],
        // ];

        // $sheet->fromArray($data, null, 'A8');
        // $lastRow = count($data) + 7;

        // // Set row heights for data
        // for ($i = 8; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(40);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:Q' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('A8:Q' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Column widths
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(30);
    }

    private function addIMPGSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('IMPG');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:G3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:G4');
        $sheet->setCellValue('A4', 'Import of goods from overseas on bill of entry');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'Icegate Reference Date',
            'B' => 'Port Code',
            'C' => 'Bill of Entry Details',
            'F' => 'Amount of tax (₹)'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        $headers6 = [
            'C' => 'Number',
            'D' => 'Date',
            'E' => 'Taxable Value',
            'F' => 'Integrated Tax(₹)',
            'G' => 'Cess(₹)'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Header Merges
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:E5'); // Bill of Entry Details Group
        $sheet->mergeCells('F5:G5'); // Amount of tax Group

        // Apply header styling
        $sheet->getStyle('A5:G6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        // Dummy Data Rows
        // $data = [
        //     ['01/11/2025', 'INNSA1', 'BOE/001', '25/10/2025', '100000.00', '18000.00', '0.00'],
        // ];

        // $sheet->fromArray($data, null, 'A7');
        // $lastRow = count($data) + 6;

        // // Set row heights for data
        // for ($i = 7; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(40);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:G' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('A7:G' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Column widths
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
    }

    private function addIMPGASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('IMPGA');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:H3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:H4');
        $sheet->setCellValue('A4', 'Import of goods from overseas on bill of entry (Amendments)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'Icegate Reference Date',
            'B' => 'Port Code',
            'C' => 'Bill of Entry Details',
            'F' => 'Amount of tax (₹)',
            'H' => 'Type of Amendment'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        $headers6 = [
            'C' => 'Number',
            'D' => 'Date',
            'E' => 'Taxable Value',
            'F' => 'Integrated Tax(₹)',
            'G' => 'Cess(₹)'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Header Merges
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:E5'); // Bill of Entry Details Group
        $sheet->mergeCells('F5:G5'); // Amount of tax Group
        $sheet->mergeCells('H5:H6');

        // Apply header styling
        $sheet->getStyle('A5:H6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        // Dummy Data Rows
        // $data = [
        //     ['01/11/2025', 'INNSA1', 'BOE/001-A', '28/10/2025', '110000.00', '19800.00', '0.00', 'Amendment'],
        // ];

        // $sheet->fromArray($data, null, 'A7');
        // $lastRow = count($data) + 6;

        // Set row heights for data
        // for ($i = 7; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(40);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:H' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('A7:H' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Column widths
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
    }

    private function addIMPGSEZSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('IMPGSEZ');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:I3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:I4');
        $sheet->setCellValue('A4', 'Import of goods from SEZ units/developers on bill of entry');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of supplier',
            'B' => 'Trade/Legal name',
            'C' => 'Icegate Reference Date',
            'D' => 'Port Code',
            'E' => 'Bill of Entry Details',
            'H' => 'Amount of tax (₹)'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        $headers6 = [
            'E' => 'Number',
            'F' => 'Date',
            'G' => 'Taxable Value',
            'H' => 'Integrated Tax(₹)',
            'I' => 'Cess(₹)'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Header Merges
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('D5:D6');
        $sheet->mergeCells('E5:G5'); // Bill of Entry Details Group
        $sheet->mergeCells('H5:I5'); // Amount of tax Group

        // Apply header styling
        $sheet->getStyle('A5:I6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        // Dummy Data Rows
        // $data = [
        //     ['24AAAAA0000A1Z5', 'SEZ SUPPLIER PVT LTD', '01/11/2025', 'INNSA1', 'BOE/SEZ/001', '25/10/2025', '50000.00', '9000.00', '0.00'],
        // ];

        // $sheet->fromArray($data, null, 'A7');
        // $lastRow = count($data) + 6;

        // // Set row heights for data
        // for ($i = 7; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(40);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:I' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('A7:I' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Column widths
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
    }

    private function addIMPGSEZASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('IMPGSEZA');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:J3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:J4');
        $sheet->setCellValue('A4', 'Import of goods from SEZ units/developers on bill of entry (Amendments)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of supplier',
            'B' => 'Trade/Legal name',
            'C' => 'Icegate Reference Date',
            'D' => 'Port Code',
            'E' => 'Bill of Entry Details',
            'H' => 'Amount of tax (₹)',
            'J' => 'Type of Amendment'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        $headers6 = [
            'E' => 'Number',
            'F' => 'Date',
            'G' => 'Taxable Value',
            'H' => 'Integrated Tax(₹)',
            'I' => 'Cess(₹)'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Header Merges
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('D5:D6');
        $sheet->mergeCells('E5:G5'); // Bill of Entry Details Group
        $sheet->mergeCells('H5:I5'); // Amount of tax Group
        $sheet->mergeCells('J5:J6');

        // Apply header styling
        $sheet->getStyle('A5:J6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        // Dummy Data Rows
        // $data = [
        //     ['24AAAAA0000A1Z5', 'SEZ SUPPLIER PVT LTD', '01/11/2025', 'INNSA1', 'BOE/SEZ/001-A', '30/10/2025', '55000.00', '9900.00', '0.00', 'Amendment'],
        // ];

        // $sheet->fromArray($data, null, 'A7');
        // $lastRow = count($data) + 6;

        // // Set row heights for data
        // for ($i = 7; $i <= $lastRow; $i++) {
        //     $sheet->getRowDimension($i)->setRowHeight(40);
        // }

        // // Styling and Borders
        // $sheet->getStyle('A5:J' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // $sheet->getStyle('A7:J' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Column widths
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
    }

    private function addB2B_ITCReversalSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2B (ITC Reversal)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:U3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:U4');
        $sheet->setCellValue('A4', 'ITC Reversed - Others');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of supplier',
            'B' => 'Trade/Legal name',
            'C' => 'Invoice Details',
            'G' => 'Place of supply',
            'H' => 'Supply Attract Reverse Charge',
            'I' => 'Taxable Value (₹)',
            'J' => 'Tax Amount',
            'N' => 'GSTR-1/IFF Period',
            'O' => 'GSTR-1/IFF Filing Date',
            'P' => 'ITC Availability',
            'Q' => 'Reason',
            'R' => 'Applicable % of Tax Rate',
            'S' => 'Source',
            'T' => 'IRN',
            'U' => 'IRN Date'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        // Sub-headers (Row 6)
        $sheet->setCellValue('C6', 'Invoice number');
        $sheet->setCellValue('D6', 'Invoice type');
        $sheet->setCellValue('E6', 'Invoice Date');
        $sheet->setCellValue('F6', 'Invoice Value(₹)');
        $sheet->setCellValue('J6', 'Integrated Tax(₹)');
        $sheet->setCellValue('K6', 'Central Tax(₹)');
        $sheet->setCellValue('L6', 'State/UT Tax(₹)');
        $sheet->setCellValue('M6', 'Cess(₹)');

        // Header Merges
        $sheet->mergeCells('A5:A6'); // GSTIN
        $sheet->mergeCells('B5:B6'); // Name
        $sheet->mergeCells('C5:F5'); // Invoice Details Group
        $sheet->mergeCells('G5:G6'); // POS
        $sheet->mergeCells('H5:H6'); // Reverse Charge
        $sheet->mergeCells('I5:I6'); // Taxable Value
        $sheet->mergeCells('J5:M5'); // Tax Amount Group
        $sheet->mergeCells('N5:N6'); // Period
        $sheet->mergeCells('O5:O6'); // Filing Date
        $sheet->mergeCells('P5:P6'); // ITC
        $sheet->mergeCells('Q5:Q6'); // Reason
        $sheet->mergeCells('R5:R6'); // %
        $sheet->mergeCells('S5:S6'); // Source
        $sheet->mergeCells('T5:T6'); // IRN
        $sheet->mergeCells('U5:U6'); // IRN Date

        $sheet->getStyle('A5:U6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Styling and Borders (Empty data row)
        $sheet->getStyle('A5:U7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'U') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('T')->setWidth(40); // IRN
    }

    private function addB2BA_ITCReversalSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2BA (ITC Reversal)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:T3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:T4');
        $sheet->setCellValue('A4', 'Amendments to previously filed invoices by supplier (ITC reversal)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Original/Revised Details (Row 5)
        $sheet->mergeCells('A5:B5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:B5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('C5:T5');
        $sheet->setCellValue('C5', 'Revised Details');
        $sheet->getStyle('C5:T5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 6 & 7)
        $sheet->setCellValue('A6', 'Invoice number');
        $sheet->setCellValue('B6', 'Invoice Date');
        $sheet->setCellValue('C6', 'GSTIN of supplier');
        $sheet->setCellValue('D6', 'Trade/Legal name');
        $sheet->setCellValue('E6', 'Invoice Details');
        $sheet->setCellValue('I6', 'Place of supply');
        $sheet->setCellValue('J6', 'Supply Attract Reverse Charge');
        $sheet->setCellValue('K6', 'Taxable Value (₹)');
        $sheet->setCellValue('L6', 'Tax Amount');
        $sheet->setCellValue('P6', 'GSTR-1/IFF Period');
        $sheet->setCellValue('Q6', 'GSTR-1/IFF Filing Date');
        $sheet->setCellValue('R6', 'ITC Availability');
        $sheet->setCellValue('S6', 'Reason');
        $sheet->setCellValue('T6', 'Applicable % of Tax Rate');

        // Sub-headers (Row 7)
        $sheet->setCellValue('E7', 'Invoice number');
        $sheet->setCellValue('F7', 'Invoice type');
        $sheet->setCellValue('G7', 'Invoice Date');
        $sheet->setCellValue('H7', 'Invoice Value(₹)');
        $sheet->setCellValue('L7', 'Integrated Tax(₹)');
        $sheet->setCellValue('M7', 'Central Tax(₹)');
        $sheet->setCellValue('N7', 'State/UT Tax(₹)');
        $sheet->setCellValue('O7', 'Cess(₹)');

        // Merges for Headers
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:H6'); // Invoice Details Group
        $sheet->mergeCells('I6:I7');
        $sheet->mergeCells('J6:J7');
        $sheet->mergeCells('K6:K7');
        $sheet->mergeCells('L6:O6'); // Tax Amount Group
        $sheet->mergeCells('P6:P7');
        $sheet->mergeCells('Q6:Q7');
        $sheet->mergeCells('R6:R7');
        $sheet->mergeCells('S6:S7');
        $sheet->mergeCells('T6:T7');

        $sheet->getStyle('A6:T7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(30);
        $sheet->getRowDimension(7)->setRowHeight(30);

        // Borders
        $sheet->getStyle('A5:T8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('C')->setWidth(25); // GSTIN
        $sheet->getColumnDimension('D')->setWidth(40); // Name
    }

    private function addB2B_DNRSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2B-DNR');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:V3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:V4');
        $sheet->setCellValue('A4', 'Debit notes (Original)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of supplier',
            'B' => 'Trade/Legal name',
            'C' => 'Debit note details',
            'H' => 'Place of supply',
            'I' => 'Supply Attract Reverse Charge',
            'J' => 'Taxable Value (₹)',
            'K' => 'Tax Amount',
            'O' => 'GSTR-1/IFF Period',
            'P' => 'GSTR-1/IFF Filing Date',
            'Q' => 'ITC Availability',
            'R' => 'Reason',
            'S' => 'Applicable % of Tax Rate',
            'T' => 'Source',
            'U' => 'IRN',
            'V' => 'IRN Date'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        // Sub-headers (Row 6)
        $sheet->setCellValue('C6', 'Note number');
        $sheet->setCellValue('D6', 'Note type');
        $sheet->setCellValue('E6', 'Note Supply type');
        $sheet->setCellValue('F6', 'Note date');
        $sheet->setCellValue('G6', 'Note Value (₹)');
        $sheet->setCellValue('K6', 'Integrated Tax(₹)');
        $sheet->setCellValue('L6', 'Central Tax(₹)');
        $sheet->setCellValue('M6', 'State/UT Tax(₹)');
        $sheet->setCellValue('N6', 'Cess(₹)');

        // Header Merges
        $sheet->mergeCells('A5:A6'); // GSTIN
        $sheet->mergeCells('B5:B6'); // Name
        $sheet->mergeCells('C5:G5'); // Debit note details Group
        $sheet->mergeCells('H5:H6'); // POS
        $sheet->mergeCells('I5:I6'); // Reverse Charge
        $sheet->mergeCells('J5:J6'); // Taxable Value
        $sheet->mergeCells('K5:N5'); // Tax Amount Group
        $sheet->mergeCells('O5:O6'); // Period
        $sheet->mergeCells('P5:P6'); // Filing Date
        $sheet->mergeCells('Q5:Q6'); // ITC
        $sheet->mergeCells('R5:R6'); // Reason
        $sheet->mergeCells('S5:S6'); // %
        $sheet->mergeCells('T5:T6'); // Source
        $sheet->mergeCells('U5:U6'); // IRN
        $sheet->mergeCells('V5:V6'); // IRN Date

        $sheet->getStyle('A5:V6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Styling and Borders
        $sheet->getStyle('A5:V7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('U')->setWidth(40); // IRN
    }

    private function addB2B_DNRASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2B-DNRA');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:W3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:W4');
        $sheet->setCellValue('A4', 'Amendments to previously filed Debit notes by supplier');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 5: Section Headers
        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('D5:W5');
        $sheet->setCellValue('D5', 'Revised Details');
        $sheet->getStyle('D5:W5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 6 & 7: Headers
        $headers6 = [
            'A' => 'Note type',
            'B' => 'Note number',
            'C' => 'Note date',
            'D' => 'GSTIN of supplier',
            'E' => 'Trade/Legal name',
            'F' => 'Debit note details',
            'K' => 'Place of supply',
            'L' => 'Supply Attract Reverse Charge',
            'M' => 'Rate(%)',
            'N' => 'Taxable Value (₹)',
            'R' => 'Tax Amount',
            'S' => 'GSTR-1/IFF Period',
            'T' => 'GSTR-1/IFF Filing Date',
            'U' => 'ITC Availability',
            'V' => 'Reason',
            'W' => 'Applicable % of Tax Rate'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        $headers7 = [
            'F' => 'Note number',
            'G' => 'Note type',
            'H' => 'Note Supply type',
            'I' => 'Note date',
            'J' => 'Note Value (₹)',
            'N' => 'Integrated Tax(₹)',
            'O' => 'Central Tax(₹)',
            'P' => 'State/UT Tax(₹)',
            'Q' => 'Cess(₹)'
        ];

        foreach ($headers7 as $col => $text) {
            $sheet->setCellValue($col . '7', $text);
        }

        // Header Merges
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:E7');
        $sheet->mergeCells('F6:J6'); // Debit note details Group
        $sheet->mergeCells('K6:K7');
        $sheet->mergeCells('L6:L7');
        $sheet->mergeCells('M6:M7');
        $sheet->mergeCells('N6:Q6'); // Taxable Value Group (Header over components)
        $sheet->mergeCells('R6:R7');
        $sheet->mergeCells('S6:S7');
        $sheet->mergeCells('T6:T7');
        $sheet->mergeCells('U6:U7');
        $sheet->mergeCells('V6:V7');
        $sheet->mergeCells('W6:W7');

        $sheet->getStyle('A6:W7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(30);
        $sheet->getRowDimension(7)->setRowHeight(30);

        // Styling and Borders
        $sheet->getStyle('A5:W8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(40);
    }

    private function addB2B_RejectedSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2B(Rejected)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:S3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:S4');
        $sheet->setCellValue('A4', 'ITC Rejected for taxable inward supplies received from registered persons');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of supplier',
            'B' => 'Trade/Legal name',
            'C' => 'Invoice Details',
            'G' => 'Place of supply',
            'H' => 'Taxable Value (₹)',
            'I' => 'Tax Amount',
            'M' => 'Remarks',
            'N' => 'GSTR-1/IFF/GSTR-5 Period',
            'O' => 'GSTR-1/IFF/GSTR-5 Filing Date',
            'P' => 'Applicable % of Tax Rate',
            'Q' => 'Source',
            'R' => 'IRN',
            'S' => 'IRN Date'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        // Sub-headers (Row 6)
        $sheet->setCellValue('C6', 'Invoice number');
        $sheet->setCellValue('D6', 'Invoice type');
        $sheet->setCellValue('E6', 'Invoice Date');
        $sheet->setCellValue('F6', 'Invoice Value(₹)');
        $sheet->setCellValue('I6', 'Integrated Tax(₹)');
        $sheet->setCellValue('J6', 'Central Tax(₹)');
        $sheet->setCellValue('K6', 'State/UT Tax(₹)');
        $sheet->setCellValue('L6', 'Cess(₹)');

        // Header Merges
        $sheet->mergeCells('A5:A6'); // GSTIN
        $sheet->mergeCells('B5:B6'); // Name
        $sheet->mergeCells('C5:F5'); // Invoice Details Group
        $sheet->mergeCells('G5:G6'); // POS
        $sheet->mergeCells('H5:H6'); // Taxable Value
        $sheet->mergeCells('I5:L5'); // Tax Amount Group
        $sheet->mergeCells('M5:M6'); // Remarks
        $sheet->mergeCells('N5:N6'); // Period
        $sheet->mergeCells('O5:O6'); // Filing Date
        $sheet->mergeCells('P5:P6'); // %
        $sheet->mergeCells('Q5:Q6'); // Source
        $sheet->mergeCells('R5:R6'); // IRN
        $sheet->mergeCells('S5:S6'); // IRN Date

        $sheet->getStyle('A5:S6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Styling and Borders
        $sheet->getStyle('A5:S7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'S') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('R')->setWidth(40); // IRN
    }

    private function addB2BA_RejectedSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2BA(Rejected)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:R3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => false, 'color' => ['rgb' => 'FFFFFF'], 'size' => 27],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:R4');
        $sheet->setCellValue('A4', 'Amendments to previously filed invoices by supplier (Rejected)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Original/Revised Details (Row 5)
        $sheet->mergeCells('A5:B5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:B5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('C5:R5');
        $sheet->setCellValue('C5', 'Revised Details');
        $sheet->getStyle('C5:R5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 6 & 7)
        $headers6 = [
            'A' => 'Invoice number',
            'B' => 'Invoice Date',
            'C' => 'GSTIN of supplier',
            'D' => 'Trade/Legal name',
            'E' => 'Invoice Details',
            'I' => 'Place of supply',
            'J' => 'Taxable Value (₹)',
            'K' => 'Tax Amount',
            'O' => 'Remarks',
            'P' => 'GSTR-1/IFF Period',
            'Q' => 'GSTR-1/IFF Filing Date',
            'R' => 'Applicable % of Tax Rate',

        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Sub-headers (Row 7)
        $headers7 = [
            'E' => 'Invoice number',
            'F' => 'Invoice type',
            'G' => 'Invoice Date',
            'H' => 'Invoice Value(₹)',
            'K' => 'Integrated Tax(₹)',
            'L' => 'Central Tax(₹)',
            'M' => 'State/UT Tax(₹)',
            'N' => 'Cess(₹)'
        ];

        foreach ($headers7 as $col => $text) {
            $sheet->setCellValue($col . '7', $text);
        }

        // Header Merges
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:H6'); // Invoice Details Group
        $sheet->mergeCells('I6:I7');
        $sheet->mergeCells('J6:J7');
        $sheet->mergeCells('K6:N6'); // Tax Amount Group
        $sheet->mergeCells('O6:O7');
        $sheet->mergeCells('P6:P7');
        $sheet->mergeCells('Q6:Q7');
        $sheet->mergeCells('R6:R7');

        $sheet->getStyle('A6:R7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(30);
        $sheet->getRowDimension(7)->setRowHeight(30);

        // Borders
        $sheet->getStyle('A5:R8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('C')->setWidth(25); // GSTIN
        $sheet->getColumnDimension('D')->setWidth(40); // Name
        $sheet->getColumnDimension('T')->setWidth(40); // IRN
    }

    private function addB2BCDNRRejectedSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2B-CDNR(Rejected)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:T3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:T4');
        $sheet->setCellValue('A4', 'ITC Rejected for Debit/Credit notes (Original)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of supplier',
            'B' => 'Trade/Legal name',
            'C' => 'Credit note/Debit note details',
            'H' => 'Place of supply',
            'I' => 'Taxable Value (₹)',
            'J' => 'Tax Amount',
            'N' => 'Remarks',
            'O' => 'GSTR-1/IFF/GSTR-5 Period',
            'P' => 'GSTR-1/IFF/GSTR-5 Filing Date',
            'Q' => 'Applicable % of Tax Rate',
            'R' => 'Source',
            'S' => 'IRN',
            'T' => 'IRN Date'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        // Sub-headers (Row 6)
        $sheet->setCellValue('C6', 'Note number');
        $sheet->setCellValue('D6', 'Note type');
        $sheet->setCellValue('E6', 'Note Supply type');
        $sheet->setCellValue('F6', 'Note date');
        $sheet->setCellValue('G6', 'Note Value (₹)');
        $sheet->setCellValue('J6', 'Integrated Tax(₹)');
        $sheet->setCellValue('K6', 'Central Tax(₹)');
        $sheet->setCellValue('L6', 'State/UT Tax(₹)');
        $sheet->setCellValue('M6', 'Cess(₹)');

        // Header Merges
        $sheet->mergeCells('A5:A6'); // GSTIN
        $sheet->mergeCells('B5:B6'); // Name
        $sheet->mergeCells('C5:G5'); // Note Details Group
        $sheet->mergeCells('H5:H6'); // POS
        $sheet->mergeCells('I5:I6'); // Taxable Value
        $sheet->mergeCells('J5:M5'); // Tax Amount Group
        $sheet->mergeCells('N5:N6'); // Remarks
        $sheet->mergeCells('O5:O6'); // Period
        $sheet->mergeCells('P5:P6'); // Filing Date
        $sheet->mergeCells('Q5:Q6'); // %
        $sheet->mergeCells('R5:R6'); // Source
        $sheet->mergeCells('S5:S6'); // IRN
        $sheet->mergeCells('T5:T6'); // IRN Date

        $sheet->getStyle('A5:T6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        // Styling and Borders
        $sheet->getStyle('A5:T7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('S')->setWidth(40); // IRN
    }

    private function addB2BCDNRARejectedSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('B2B-CDNRA(Rejected)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:T3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:T4');
        $sheet->setCellValue('A4', 'ITC Rejected for amendments to previously filed Credit/Debit notes by supplier');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 5: Section Headers
        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('D5:T5');
        $sheet->setCellValue('D5', 'Revised Details');
        $sheet->getStyle('D5:T5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 6 & 7: Headers
        $headers6 = [
            'A' => 'Note type',
            'B' => 'Note number',
            'C' => 'Note date',
            'D' => 'GSTIN of supplier',
            'E' => 'Trade/Legal name',
            'F' => 'Credit note/Debit note details',
            'K' => 'Place of supply',
            'L' => 'Taxable Value (₹)',
            'M' => 'Tax Amount',
            'Q' => 'Remarks',
            'R' => 'GSTR-1/IFF/GSTR-5 Period',
            'S' => 'GSTR-1/IFF/GSTR-5 Filing Date',
            'T' => 'Applicable % of Tax Rate'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        $headers7 = [
            'F' => 'Note number',
            'G' => 'Note type',
            'H' => 'Note Supply type',
            'I' => 'Note date',
            'J' => 'Note Value (₹)',
            'M' => 'Integrated Tax(₹)',
            'N' => 'Central Tax(₹)',
            'O' => 'State/UT Tax(₹)',
            'P' => 'Cess(₹)'
        ];

        foreach ($headers7 as $col => $text) {
            $sheet->setCellValue($col . '7', $text);
        }

        // Header Merges
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:E7');
        $sheet->mergeCells('F6:J6'); // Note details group
        $sheet->mergeCells('K6:K7');
        $sheet->mergeCells('L6:L7');
        $sheet->mergeCells('M6:P6'); // Tax Amount group
        $sheet->mergeCells('Q6:Q7');
        $sheet->mergeCells('R6:R7');
        $sheet->mergeCells('S6:S7');
        $sheet->mergeCells('T6:T7');

        $sheet->getStyle('A6:T7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(35);
        $sheet->getRowDimension(7)->setRowHeight(35);

        // Styling and Borders
        $sheet->getStyle('A5:T8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('D')->setWidth(25); // GSTIN
        $sheet->getColumnDimension('E')->setWidth(40); // Name
    }

    private function addECORejectedSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ECO(Rejected)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:R3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:R4');
        $sheet->setCellValue('A4', 'ITC Rejected for documents reported by ECO on which ECO is liable to pay tax us 9(5)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of ECO',
            'B' => 'Trade/Legal name',
            'C' => 'Document details',
            'G' => 'Place of supply',
            'H' => 'Taxable value (₹)',
            'I' => 'Tax amount',
            'M' => 'Remarks',
            'N' => 'GSTR-1/1A/IFF period',
            'O' => 'GSTR-1/1A/IFF filing date',
            'P' => 'Source',
            'Q' => 'IRN',
            'R' => 'IRN Date'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        $headers6 = [
            'C' => 'Document number',
            'D' => 'Document type',
            'E' => 'Document date',
            'F' => 'Document value(₹)',
            'I' => 'Integrated Tax(₹)',
            'J' => 'Central Tax(₹)',
            'K' => 'State/UT Tax(₹)',
            'L' => 'Cess(₹)'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Header Merges
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:F5'); // Document details group
        $sheet->mergeCells('G5:G6');
        $sheet->mergeCells('H5:H6');
        $sheet->mergeCells('I5:L5'); // Tax amount group
        $sheet->mergeCells('M5:M6');
        $sheet->mergeCells('N5:N6');
        $sheet->mergeCells('O5:O6');
        $sheet->mergeCells('P5:P6');
        $sheet->mergeCells('Q5:Q6');
        $sheet->mergeCells('R5:R6');

        // Apply header styling
        $sheet->getStyle('A5:R6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        // Styling and Borders
        $sheet->getStyle('A5:R7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('Q')->setWidth(40); // IRN
    }

    private function addECOARejectedSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ECOA(Rejected)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:Q3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:Q4');
        $sheet->setCellValue('A4', 'ITC Rejected for amendments to documents reported by ECO on which ECO is liable to pay tax u/s 9(5)');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 5: Section Headers
        $sheet->mergeCells('A5:B5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:B5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('C5:Q5');
        $sheet->setCellValue('C5', 'Revised Details');
        $sheet->getStyle('C5:Q5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 6 & 7: Headers
        $headers6 = [
            'A' => 'Document number',
            'B' => 'Document date',
            'C' => 'GSTIN of ECO',
            'D' => 'Trade/Legal name',
            'E' => 'Document details',
            'I' => 'Place of supply',
            'J' => 'Taxable value (₹)',
            'K' => 'Tax amount',
            'O' => 'Remarks',
            'P' => 'GSTR-1/1A/IFF period',
            'Q' => 'GSTR-1/1A/IFF filing date'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        $headers7 = [
            'E' => 'Document number',
            'F' => 'Document type',
            'G' => 'Document date',
            'H' => 'Document value(₹)',
            'K' => 'Integrated Tax(₹)',
            'L' => 'Central Tax(₹)',
            'M' => 'State/UT Tax(₹)',
            'N' => 'Cess(₹)'
        ];

        foreach ($headers7 as $col => $text) {
            $sheet->setCellValue($col . '7', $text);
        }

        // Header Merges
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:H6'); // Document details group
        $sheet->mergeCells('I6:I7');
        $sheet->mergeCells('J6:J7');
        $sheet->mergeCells('K6:N6'); // Tax amount group
        $sheet->mergeCells('O6:O7');
        $sheet->mergeCells('P6:P7');
        $sheet->mergeCells('Q6:Q7');

        // Apply header styling
        $sheet->getStyle('A6:Q7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(35);
        $sheet->getRowDimension(7)->setRowHeight(35);

        // Styling and Borders
        $sheet->getStyle('A5:Q8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('C')->setWidth(25); // GSTIN
        $sheet->getColumnDimension('D')->setWidth(40); // Name
    }

    private function addISDRejectedSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ISD(Rejected)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:M3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:M4');
        $sheet->setCellValue('A4', 'ITC Rejected for ISD Credits');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Headers (Row 5 & 6)
        $headers5 = [
            'A' => 'GSTIN of ISD',
            'B' => 'Trade/Legal name',
            'C' => 'ISD Document type',
            'D' => 'ISD Document number',
            'E' => 'ISD Document date',
            'F' => 'Original Invoice Number',
            'G' => 'Original invoice date',
            'H' => 'Input tax distribution by ISD',
            'L' => 'ISD GSTR-6 Period',
            'M' => 'ISD GSTR-6 Filing Date'
        ];

        foreach ($headers5 as $col => $text) {
            $sheet->setCellValue($col . '5', $text);
        }

        $headers6 = [
            'H' => 'Integrated Tax(₹)',
            'I' => 'Central Tax(₹)',
            'J' => 'State/UT Tax(₹)',
            'K' => 'Cess(₹)'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        // Header Merges
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('D5:D6');
        $sheet->mergeCells('E5:E6');
        $sheet->mergeCells('F5:F6');
        $sheet->mergeCells('G5:G6');
        $sheet->mergeCells('H5:K5'); // Input tax distribution by ISD group
        $sheet->mergeCells('L5:L6');
        $sheet->mergeCells('M5:M6');

        // Apply header styling
        $sheet->getStyle('A5:M6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(5)->setRowHeight(35);
        $sheet->getRowDimension(6)->setRowHeight(35);

        // Borders
        $sheet->getStyle('A5:M7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);
    }

    private function addISDARejectedSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ISDA(Rejected)');

        // Main Title (Row 1-3)
        $sheet->mergeCells('A1:P3');
        $sheet->setCellValue('A1', 'Goods and Services Tax - GSTR-2B');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Subtitle (Row 4)
        $sheet->mergeCells('A4:P4');
        $sheet->setCellValue('A4', 'ITC Rejected for amendments of ISD Credits received');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 5: Section Headers
        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', 'Original Details');
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->mergeCells('D5:P5');
        $sheet->setCellValue('D5', 'Revised Details');
        $sheet->getStyle('D5:P5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCC8DC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Row 6 & 7: Headers
        $headers6 = [
            'A' => 'ISD Document type',
            'B' => 'Document Number',
            'C' => 'Document date',
            'D' => 'GSTIN of ISD',
            'E' => 'Trade/Legal name',
            'F' => 'ISD Document type',
            'G' => 'ISD Document number',
            'H' => 'ISD Document date',
            'I' => 'Original Invoice Number',
            'J' => 'Original invoice date',
            'K' => 'Input tax distribution by ISD',
            'O' => 'ISD GSTR-6 Period',
            'P' => 'ISD GSTR-6 Filing Date'
        ];

        foreach ($headers6 as $col => $text) {
            $sheet->setCellValue($col . '6', $text);
        }

        $headers7 = [
            'K' => 'Integrated Tax(₹)',
            'L' => 'Central Tax(₹)',
            'M' => 'State/UT Tax(₹)',
            'N' => 'Cess(₹)'
        ];

        foreach ($headers7 as $col => $text) {
            $sheet->setCellValue($col . '7', $text);
        }

        // Header Merges
        $sheet->mergeCells('A6:A7');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:C7');
        $sheet->mergeCells('D6:D7');
        $sheet->mergeCells('E6:E7');
        $sheet->mergeCells('F6:F7');
        $sheet->mergeCells('G6:G7');
        $sheet->mergeCells('H6:H7');
        $sheet->mergeCells('I6:I7');
        $sheet->mergeCells('J6:J7');
        $sheet->mergeCells('K6:N6'); // Input tax group
        $sheet->mergeCells('O6:O7');
        $sheet->mergeCells('P6:P7');

        // Apply header styling
        $sheet->getStyle('A6:P7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->getRowDimension(6)->setRowHeight(35);
        $sheet->getRowDimension(7)->setRowHeight(35);

        // Borders
        $sheet->getStyle('A5:P8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('D')->setWidth(25); // GSTIN
        $sheet->getColumnDimension('E')->setWidth(40); // Name
    }
}
