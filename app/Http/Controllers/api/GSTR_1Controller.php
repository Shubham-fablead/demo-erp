<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\PurchaseInvoice;
use App\Models\DebitNoteItem;
use App\Models\CreditNoteItem;
use App\Models\UserDetail;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GSTR_1Controller extends Controller
{

    public function exportExcel()
    {
        $spreadsheet = new Spreadsheet();





        // 1.  Help instruction (First sheet)
        $this->addHelpInstructionSheet($spreadsheet);

        // 2. B2B, SEZ, DE
        $this->addB2B_SEZ_DESheet($spreadsheet);

        //3 . B2BA
        $this->addB2BASheet($spreadsheet);

        //4. B2CL
        $this->addB2CLSheet($spreadsheet);


        //5. B2CLA
        $this->addB2CLASheet($spreadsheet);

        //6. B2CS
        $this->addB2CSSheet($spreadsheet);

        //7. B2CSA
        $this->addB2CSASheet($spreadsheet);

        // 8. CDNR
        $this->addCDNRSheet($spreadsheet);

        // 9. CDNRA
        $this->addCDNRASheet($spreadsheet);

        // 10. CDNUR
        $this->addCDNURSheet($spreadsheet);

        // 11. CDNURA
        $this->addCDNURASheet($spreadsheet);

        // 12. EXP
        $this->addEXPSheet($spreadsheet);

        // 13. EXPA
        $this->addEXPASheet($spreadsheet);

        // 14. at
        $this->addatSheet($spreadsheet);

        // 15. ata
        $this->addataSheet($spreadsheet);

        // 16. atadj
        $this->addatadjSheet($spreadsheet);

        // 17. atadja
        $this->addatadjaSheet($spreadsheet);

        // 18. exemp
        $this->addexempSheet($spreadsheet);

        // 19. hsn(b2b)
        $this->addhsnb2bSheet($spreadsheet);

        // 20. hsn(b2c)
        $this->addhsnb2cSheet($spreadsheet);

        // 21. docs
        $this->adddocsSheet($spreadsheet);

        // 22. eco
        $this->addecoSheet($spreadsheet);

        // 23. ecoa
        $this->addecoaSheet($spreadsheet);

        // 24. ecob2b
        $this->addecob2bSheet($spreadsheet);

        // 25. ecourp2b
        $this->addecourp2bSheet($spreadsheet);

        // 26. ecob2c
        $this->addecob2cSheet($spreadsheet);

        // 27. ecourp2c
        $this->addecourp2cSheet($spreadsheet);

        // 28. ecoab2b
        $this->addecoab2b($spreadsheet);

        // 29. ecoab2c
        $this->addecoab2c($spreadsheet);

        // 30. ecoaurp2b
        $this->addecoaurp2bSheet($spreadsheet);

        // 31. ecoaurp2c
        $this->addecoaurp2cSheet($spreadsheet);

        //32. master
        $this->addMasterSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);

        // Use output buffering to capture the excel file content
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="GSTR1_Multiple_Sheets.xlsx"')
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

    // tab 1: Help instruction
    private function addHelpInstructionSheet($spreadsheet)
    {
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Help Instruction');
        // Column Widths
        $sheet1->getColumnDimension('A')->setWidth(5);
        foreach (range('B', 'K') as $col) {
            $sheet1->getColumnDimension($col)->setWidth(15);
        }

        // Row 1: Main Title
        $sheet1->mergeCells('B1:K1');
        $sheet1->setCellValue('B1', 'Invoice & other data upload for creation of GSTR 1');
        $sheet1->getStyle('B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet1->getRowDimension(1)->setRowHeight(30);

        // Row 2: Introduction Header (Blue background)
        $sheet1->mergeCells('B2:K2');
        $sheet1->setCellValue('B2', 'Introduction to Excel based template for data upload in Java offline tool');
        $sheet1->getStyle('B2:K2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet1->getRowDimension(2)->setRowHeight(25);

        // Row 3: Instruction Text Box
        $sheet1->mergeCells('B3:K3');
        $text = "s The Offline tool comes bundled with MS Excel Template and a java tool. This Excel workbook template has 19 data entry worksheets, 1 master sheet and 1 Help Instruction sheet i.e. total 21 worksheets. The 19 data entry worksheets are named: b2b, sez, de, b2ba,b2cl,b2cla, b2cs,b2csa, cdnr, cdra,cdnur,cdnura, exp,expa, at,ata, atadj, atadja, exemp, hsn and doc in which day-to-day business transaction required to be reported in GSTR 1 can be recorded or entered by the taxpayers. At desired interval, the data entered in the MS-Excel worksheet can be uploaded on the GST Portal using the java offline tool which will import the data from excel workbook and convert the same into a Json file which is understood by GST portal. (www.gst.gov.in)\n" .
            "s It has been designed to enable taxpayers to prepare GSTR 1 in offline mode (without Internet). It can also be used to carry out bulk upload of invoice/other details to GST portal.\n" .
            "s The appearance and functionalities of the Offline tool screens are similar to that of the returns filing screens on the GST Portal.\n" .
            "s Approximately 19,000 line items can be uploaded in one go using the java tool. In case a taxpayer has more invoice data, he can use the tool multiple times to upload the invoice data.\n\n" .
            "Data can be uploaded/entered to the offline tool in four ways:\n\n" .
            "1. Importing the entire excel workbook to the java tool where data in all sections (worksheets) of the excel file will be imported in the tool in one go.\n" .
            "2. Line by line data entry by return preparer on the java offline tool.\n" .
            "3. Copy from the excel worksheets from the top row including the summary and header and pasting it in the designated box in the import screen of the java offline tool. Precaution: All the columns including headers should be in the same format and have the same header as of the java offline tool.\n" .
            "4. Section by section of a particular return - using a .CSV file as per the format given along with the java tool. Many accounting software packages generate .CSV file in the specified format and the same can be imported in the tool.";

        $sheet1->setCellValue('B3', $text);
        $sheet1->getStyle('B3:K3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true
            ],
            'borders' => [
                'outline' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        $sheet1->getRowDimension(3)->setRowHeight(310);

        // Row 5: Understanding Header (Blue background)
        $sheet1->mergeCells('B5:K5');
        $sheet1->setCellValue('B5', 'Understanding the Excel Workbook Template');
        $sheet1->getStyle('B5:K5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet1->getRowDimension(5)->setRowHeight(25);

        // Row 6: Instruction Text Box for Understanding Template
        $sheet1->mergeCells('B6:K14');
        $understandingText = "a) It is always recommended to download the excel workbook template from the GST portal only.\n\n" .
            "b) The taxpayer can fill the excel workbook template with different worksheet for the applicable sections of the return and then import the excel file to the java tool. Data has to be filled in the sections (worksheets) applicable to him and the others may be left blank.\n" .
            "Note: Quarterly taxpayers opting to furnish invoices using invoice furnishing facility (IFF) can import details only for following tables:\n" .
            "1. Table 4A, 4B, 6B, 6C - B2B, SEZ, DE invoices\n" .
            "2. Table 9B – Credit/ Debit notes (Registered) – CDNR\n" .
            "3. Table 9A - Amended B2B invoices\n" .
            "4. Table 9C - Amended Credit/Debit notes (Registered)\n" .
            "5. Table 15 - Supplies U/s 9(5) - B2B\n" .
            "6. Table 15 - Supplies U/s 9(5) - URP2B\n" .
            "7. Table 15A - Amended Supplies U/s 9(5) - B2B\n" .
            "8. Table 15A - Amended Supplies U/s 9(5) - URP2B\n\n" .
            "Note: Table 15 is applicable from Oct 2022 and Table 15A is applicable from Nov 2022.\n\n" .
            "c) The data in the excel file should be in the format specified below in respective sections.\n\n" .
            "d) In a case where the taxpayer does not have data applicable for all sections, those sections may be left blank and the java tool will automatically take care of the data to be filled in the applicable sections only.\n\n" .
            "e) For Group import (all worksheets of workbook) taxpayer need to fill all the details into downloaded standard format GSTR1_Excel_Workbook_Template-V.xlsx file\n" .
            "f) User can export Data from local accounting software loaded in the above format as .CSV file and import it in the java tool to generate the file in .Json format for bulk. Warning: Your accounting software should generate .CSV file in the format specified by GST Systems.\n\n" .
            "g) In all the worksheets except hsn, the central tax, integrated tax, and state tax are not required to be furnished in the excel worksheets but would be computed on the furnished rate and taxable value in the java offline tool. The taxpayer can edit the tax amounts calculated in the java tool if the tax collected values are different.\n\n" .
            "h) In the \"doc's worksheet, the net issued column has not been provided, this value will be computed in the java offline tool based on the total number of documents and the number of cancelled documents furnished in this worksheet.";

        $sheet1->setCellValue('B6', $understandingText);
        $sheet1->getStyle('B6:K6')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true
            ],
            'borders' => [
                'outline' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        // Adjust row heights for the content
        for ($i = 6; $i <= 14; $i++) {
            $sheet1->getRowDimension($i)->setRowHeight(45);
        }

        // --- NEW DATA FROM THIRD IMAGE ---
        // Row 16: Descriptive Text
        $sheet1->mergeCells('B16:K16');
        $sheet1->setCellValue('B16', 'The table below provides the name, full form and detailed description for each field of the worksheets followed by a detailed instruction for filling the applicable worksheets. The fields marked with asterisk or star are mandatory.');
        $sheet1->getStyle('B16')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(16)->setRowHeight(35);

        // Row 17: Table Headers
        $sheet1->setCellValue('B17', 'Worksheet Name');
        $sheet1->mergeCells('C17:D17');
        $sheet1->setCellValue('C17', 'Reference');
        $sheet1->mergeCells('E17:G17');
        $sheet1->setCellValue('E17', 'Field name');
        $sheet1->mergeCells('H17:K17');
        $sheet1->setCellValue('H17', 'Help Instruction');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet1->getStyle('B17:K17')->applyFromArray($headerStyle);

        // Rows 18-31: Table Body
        // Merged Worksheet Name and Reference
        $sheet1->mergeCells('B18:B31');
        $sheet1->setCellValue('B18', 'b2b,sez,de');
        $sheet1->mergeCells('C18:D31');
        $sheet1->setCellValue('C18', 'B2B, SEZ, DE Supplies');

        $sheet1->getStyle('B18:D31')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 18: Field Name sub-header
        $sheet1->mergeCells('E18:K18');
        $sheet1->setCellValue('E18', 'Details of invoices of Taxable supplies made to other registered taxpayers');
        $sheet1->getStyle('E18')->getFont()->setBold(true);

        // Fields Data
        $fields = [
            ['1. GSTIN/UIN of Recipient*', 'Enter the GSTIN or UIN of the receiver. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the invoice from GST portal.'],
            ['2. Name of Recipient', 'Enter the name of the receiver'],
            ['3. Invoice number *', 'Enter the Invoice number of invoices issued to registered recipients. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-). The total number of characters should not be more than 16.'],
            ['4. Invoice Date*', 'Enter date of invoice in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['5. Invoice value*', 'Enter the total value indicated in the invoice of the supplied goods or services- with 2 decimal Digits.'],
            ['6. Place of Supply(POS)*', 'Select the code of the state from drop down list for the place of supply.'],
            ['7. Applicable % of Tax Rate', 'If the supply is eligible to be taxed at 65% of the existing rate of tax, select \'65%\' from dropdown; else blank.'],
            ['8. Reverse Charge*', 'Please select Y or N, if the supplies/services are subject to tax as per reverse charge mechanism.'],
            ['9. Invoice Type*', 'Select from the dropdown whether the supply is regular B2B, or to a SEZ unit/developer with or without payment of tax or deemed export.'],
            ['10. E-Commerce GSTIN*', 'Enter the GSTIN of the e-commerce company if the supplies are made through an e-Commerce operator.'],
            ['11. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax, as applicable.'],
            ['12. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item - with 2 decimal Digits. The taxable value has to be computed as per GST valuation provisions.'],
            ['13. Cess Amount', 'Enter the total Cess amount collected/payable.']
        ];

        $row = 19;
        foreach ($fields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(50);
            $row++;
        }

        // Apply borders to the whole table area
        $sheet1->getStyle('E18:K31')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR B2BA ---
        // Worksheet Name and Reference for B2BA
        $sheet1->mergeCells('B32:B47');
        $sheet1->setCellValue('B32', 'b2ba');
        $sheet1->mergeCells('C32:D47');
        $sheet1->setCellValue('C32', 'B2BA Supplies');

        $sheet1->getStyle('B32:D47')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 32: Field Name sub-header
        $sheet1->mergeCells('E32:K32');
        $sheet1->setCellValue('E32', 'Amended Details of invoices of Taxable supplies made to other registered taxpayers');
        $sheet1->getStyle('E32')->getFont()->setBold(true);

        // Fields Data for B2BA
        $b2baFields = [
            ['1. GSTIN/UIN of Recipient*', 'Enter the GSTIN or UIN of the receiver. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the invoice from GST portal.'],
            ['2. Name of Recipient', 'Enter the name of the receiver'],
            ['3. Original Invoice number *', 'Enter the Original Invoice number of invoices issued to registered recipients. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-). The total number of characters should not be more than 16.'],
            ['4. Original Invoice Date*', 'Enter Orginal date of invoice in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['5. Revised Invoice number *', 'Enter the Revised Invoice number of invoices issued to registered recipients. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-). The total number of characters should not be more than 16.'],
            ['6. Revised Invoice Date*', 'Enter date of invoice in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['7. Invoice value*', 'Enter the total value indicated in the invoice of the supplied goods or services- with 2 decimal Digits.'],
            ['8. Place of Supply(POS)*', 'Select the code of the state from drop down list for the place of supply.'],
            ['9. Reverse Charge*', 'Please select Y or N, if the supplies/services are subject to tax as per reverse charge mechanism.'],
            ['10. Applicable % of Tax Rate', 'If the supply is eligible to be taxed at 65% of the existing rate of tax, select \'65%\' from dropdown; else blank.'],
            ['11. Invoice Type*', 'Select from the dropdown whether the supply is regular B2B, or to a SEZ unit/developer with or without payment of tax or deemed export.'],
            ['12. E-Commerce GSTIN*', 'Enter the GSTIN of the e-commerce company if the supplies are made through an e-Commerce operator.'],
            ['13. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax, as applicable.'],
            ['14. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item - with 2 decimal Digits. The taxable value has to be computed as per GST valuation provisions.'],
            ['15. Cess Amount', 'Enter the total Cess amount collected/payable.']
        ];

        $row = 33;
        foreach ($b2baFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(50);
            $row++;
        }

        // Apply borders to the whole B2BA table area
        $sheet1->getStyle('E32:K47')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR B2C LARGE ---
        // Worksheet Name and Reference for B2C Large
        $sheet1->mergeCells('B48:B57');
        // $sheet1->setCellValue('B48', 'b2cl');
        $sheet1->mergeCells('C48:D57');
        $sheet1->setCellValue('C48', 'B2C Large');

        $sheet1->getStyle('B48:D57')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 48: Field Name sub-header
        $sheet1->mergeCells('E48:K48');
        $sheet1->setCellValue('E48', "Invoices for Taxable outward supplies to consumers where\na)The place of supply is outside the state where the supplier is registered and\nb)The total invoice value should be more than INR100000\nNote : Value should be more than INR 100000 from August 2024 return period onwards.");
        $sheet1->getStyle('E48')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(48)->setRowHeight(80);

        // Fields Data for B2C Large
        $b2clFields = [
            ['1. Invoice number*', "Enter the Invoice number of invoices issued to Unregistered Recipient of the other State with invoice value more than 1 lakh.\nNote : Value should be more than 1 lakh from August 2024 return period onwards. Value should be more than 2.5 lakh upto July 2024 return period.. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-)."],
            ['2. Invoice Date', 'Enter date of invoice in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['3. Invoice value*', "Invoice value should be more than Rs 100,000 and up to two decimal digits."],
            ['4. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['5. Place of Supply(POS)*', 'Select the code of the state from drop down list for the applicable place.'],
            ['6. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate, as applicable.'],
            ['7. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['8. Cess Amount', 'Enter the total Cess amount collected/payable.'],
            ['9. E-Commerce GSTIN', 'Enter the GSTIN of the e-commerce company if the supplies are made through an e-Commerce operator.']
        ];

        $row = 49;
        foreach ($b2clFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(50);
            $row++;
        }

        // Apply borders to the whole B2C Large table area
        $sheet1->getStyle('E48:K57')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR B2CLA ---
        // Worksheet Name and Reference for B2CLA
        $sheet1->mergeCells('B58:B69');
        $sheet1->setCellValue('B58', 'b2cla');
        $sheet1->mergeCells('C58:D69');
        $sheet1->setCellValue('C58', 'Amended B2C Large');

        $sheet1->getStyle('B58:D69')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 58: Field Name sub-header
        $sheet1->mergeCells('E58:K58');
        $sheet1->setCellValue('E58', "Amended Details of Invoices for Taxable outward supplies to consumers where\na)The place of supply is outside the state where the supplier is registered and\nb)The total invoice value should be more than INR100000\nNote : Value should be more than INR 100000 from August 2024 return period onwards. Value should be more than INR 250000 upto July 2024 return period.");
        $sheet1->getStyle('E58')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(58)->setRowHeight(100);

        // Fields Data for B2CLA
        $b2claFields = [
            ['5', 'Enter the Original Invoice number of invoices issued to Unregistered'],
            ['2. Orginal Invoice Date', 'Enter Original date of invoice in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['3. Revised Invoice number*', 'Enter the Revised Invoice number of invoices issued to Unregistered'],
            ['4. Revised Invoice Date', 'Enter Revised date of invoice in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['5. Invoice value*', 'Invoice value should be more than Rs 100,000 and up to two decimal'],
            ['6. Original Place of Supply(POS)*', 'Select the code of the state from drop down list for the applicable place'],
            ['7. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['8. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate, as'],
            ['9. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate'],
            ['10. Cess Amount', 'Enter the total Cess amount collected/payable.'],
            ['11. E-Commerce GSTIN', 'Enter the GSTIN of the e-commerce company if the supplies are made through an e-Commerce operator.']
        ];

        $row = 59;
        foreach ($b2claFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(50);
            $row++;
        }

        // Apply borders to the whole B2CLA table area
        $sheet1->getStyle('E58:K69')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR B2CS ---
        // Worksheet Name and Reference for B2CS
        $sheet1->mergeCells('B70:B77');
        $sheet1->setCellValue('B70', 'b2cs');
        $sheet1->mergeCells('C70:D77');
        $sheet1->setCellValue('C70', 'B2C Small');

        $sheet1->getStyle('B70:D77')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 70: Field Name sub-header
        $sheet1->mergeCells('E70:K70');
        $sheet1->setCellValue('E70', "Supplies made to consumers and unregistered persons of the following nature\na) Intra-State: any value");
        $sheet1->getStyle('E70')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(70)->setRowHeight(40);

        // Fields Data for B2CS
        $b2csFields = [
            ['1. Type*', 'In the Type column, enter E if the supply is done through E-Commerce or else enter OE (other than E-commerce)'],
            ['2. Place of Supply(POS)*', 'Select the code of the state from drop down list for the applicable place of supply.'],
            ['3. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['4. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['5. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['6. Cess Amount', 'Enter the total Cess amount collected/payable.'],
            ['7. E-Commerce GSTIN', 'Enter the GSTIN of the e-commerce company if the supplies are made through an e-Commerce operator.']
        ];

        $row = 71;
        foreach ($b2csFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(50);
            $row++;
        }

        // Apply borders to the whole B2CS table area
        $sheet1->getStyle('E70:K77')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR B2CSA ---
        // Worksheet Name and Reference for B2CSA
        $sheet1->mergeCells('B78:B88');
        $sheet1->setCellValue('B78', 'b2csa');
        $sheet1->mergeCells('C78:D88');
        $sheet1->setCellValue('C78', 'Amended B2C Small');

        $sheet1->getStyle('B78:D88')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 78: Field Name sub-header
        $sheet1->mergeCells('E78:K78');
        $sheet1->setCellValue('E78', "Amended Details of Supplies made to consumers and unregistered persons of the following nature\na) Intra-State: any value\nb) Inter-State: Invoice value Rs. 1.0 lakh or less\n Note: Value should be INR 100000 or less from August 2024 return period onwards.\n  Value should be INR 250000 or less upto July 2024 return period.");
        $sheet1->getStyle('E78')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(78)->setRowHeight(100);

        // Fields Data for B2CSA
        $b2csaFields = [
            ['1. Type*', 'In the Type column, enter E if the supply is done through E-Commerce or else enter OE (other than E-commerce).'],
            ['2.Financial Year', 'Select the financial year'],
            ['3.Original Month', 'Select the Month'],
            ['4.Original Place of Supply(POS)*', 'Select the code of the state from drop down list for the applicable place of supply.'],
            ['5.Revised Place of Supply(POS)*', 'Select the code of the state from drop down list for the applicable place of supply.'],
            ['6. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['7. Original Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['8. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['9. Cess Amount', 'Enter the total Cess amount collected/payable.'],
            ['10. E-Commerce GSTIN', 'Enter the GSTIN of the e-commerce company if the supplies are made through an e-Commerce operator.']
        ];

        $row = 79;
        foreach ($b2csaFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(50);
            $row++;
        }

        // Apply borders to the whole B2CSA table area
        $sheet1->getStyle('E78:K88')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR CDNR ---
        // Worksheet Name and Reference for CDNR
        $sheet1->mergeCells('B89:B102');
        $sheet1->setCellValue('B89', 'cdnr');
        $sheet1->mergeCells('C89:D102');
        $sheet1->setCellValue('C89', 'Credit/ Debit Note');

        $sheet1->getStyle('B89:D102')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 89: Field Name sub-header
        $sheet1->mergeCells('E89:K89');
        $sheet1->setCellValue('E89', "Credit/ Debit Notes issued to the registered taxpayers during the tax period. Debit or credit note issued against invoice will be reported here against original invoice, hence fill the details of original invoice also which was furnished in B2B,B2CL section of earlier/current period tax period.");
        $sheet1->getStyle('E89')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(89)->setRowHeight(60);

        // Fields Data for CDNR
        $cdnrFields = [
            ['1. GSTIN/UIN*', 'Receiver GSTIN/UIN'],
            ['2. Name of Recipient', 'Enter the name of the receiver'],
            ['3. Note Number*', 'Enter the credit/debit note number. Ensure that the format is'],
            ['4. Note date*', 'Enter credit/debit note date in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['5. Note Type*', "In the Note Type column, enter \"D\" if the note is Debit note, enter \"C\" if"],
            ['6. Place of Supply*', 'Declare the place of supply based on the original document.'],
            ['7. Reverse charge*', 'Please select Y or N , if the supplies/services are subject to tax as per'],
            ['8. Note Supply Type*', 'Select from the dropdown whether the supply is regular B2B, or to a'],
            ['9. Note value*', 'Amount should be with only up to 2 decimal digits.'],
            ['10. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['11. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax.'],
            ['12.Taxable value*', 'Enter the taxable value of the supplied goods or services for each rate'],
            ['13. Cess Amount', 'Enter the total Cess amount.']
        ];

        $row = 90;
        foreach ($cdnrFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole CDNR table area
        $sheet1->getStyle('E89:K102')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR CDNRA ---
        // Worksheet Name and Reference for CDNRA
        $sheet1->mergeCells('B103:B118');
        $sheet1->setCellValue('B103', 'cdnra');
        $sheet1->mergeCells('C103:D118');
        $sheet1->setCellValue('C103', 'Amended Credit/ Debit Note');

        $sheet1->getStyle('B103:D118')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 103: Field Name sub-header
        $sheet1->mergeCells('E103:K103');
        $sheet1->setCellValue('E103', "Amended Credit/ Debit Notes issued to the registered taxpayers during the tax period. Debit or credit note issued against invoice will be reported here against original invoice, hence fill the details of original invoice also which was furnished in B2B,B2CL section of earlier/current period tax period.");
        $sheet1->getStyle('E103')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(103)->setRowHeight(80);

        // Fields Data for CDNRA
        $cdnraFields = [
            ['1. GSTIN/UIN*', 'Receiver GSTIN/UIN'],
            ['2. Name of Recipient', 'Enter the name of the receiver'],
            ['3. Original Note Number*', 'Enter the original credit/debit note number. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-) of maximum length of 16 characters.'],
            ['4. Original Note date*', 'Enter original credit/debit note date in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['5. Revised Note Number*', 'Enter the revised credit/debit note number. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-) of maximum length of 16 characters.'],
            ['6. Revised Note date*', 'Enter revised credit/debit note/Refund voucher date in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['7. Note Type*', 'In the Note Type column, enter "D" if the note is Debit note, enter "C" if'],
            ['8. Place of Supply*', 'Declare the place of supply based on the original document.'],
            ['9. Reverse charge*', 'Please select Y or N , if the supplies/services are subject to tax as per'],
            ['10. Note Supply Type*', 'Select from the dropdown whether the supply is regular B2B, or to a'],
            ['11. Note value*', 'Amount should be with only up to 2 decimal digits.'],
            ['12. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['13. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax.'],
            ['14.Taxable value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['15. Cess Amount', 'Enter the total Cess amount.']
        ];

        $row = 104;
        foreach ($cdnraFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(50);
            $row++;
        }

        // Apply borders to the whole CDNRA table area
        $sheet1->getStyle('E103:K118')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR CDNUR ---
        // Worksheet Name and Reference for CDNUR
        $sheet1->mergeCells('B119:B129');
        $sheet1->setCellValue('B119', 'cdnur');
        $sheet1->mergeCells('C119:D129');
        $sheet1->setCellValue('C119', 'Credit/ Debit Note for unregistered Persons');

        $sheet1->getStyle('B119:D129')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 119: Field Name sub-header / Description
        $sheet1->mergeCells('E119:K119');
        $sheet1->setCellValue('E119', "Credit/ Debit Notes issued to the unregistered persons against interstate invoice value is more than Rs 1.0 lakh\nNote : Value should be more than INR 100000 from August 2024 return period onwards.\nValue should be more than INR 250000 upto July 2024 return period.");
        $sheet1->getStyle('E119')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(119)->setRowHeight(60);

        // Fields Data for CDNUR
        $cdnurFields = [
            ['1. UR Type*', 'Select the type of supply to Unregistered Taxpayers (UR) against which'],
            ['2. Note Number*', 'Enter the credit/debit note number. Ensure that the format is'],
            ['3. Note date*', 'Enter credit/debit note date in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['4. Note Type*', "In the Note Type column, enter \"D\" if the note is Debit note, enter \"C\" if note is credit note or enter \"R\" for refund voucher."],
            ['5. Place of Supply', 'Declare the place of supply based on the original document.'],
            ['6. Note value*', 'Amount should be up to 2 decimal digits.'],
            ['7. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['8. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['9.Taxable value', 'Enter the taxable value of the supplied goods or services for each rate line item -up to 2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['10. Cess Amount', 'Enter the total Cess amount.']
        ];

        $row = 120;
        foreach ($cdnurFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole CDNUR table area
        $sheet1->getStyle('E119:K129')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR CDNURA ---
        // Worksheet Name and Reference for CDNURA
        $sheet1->mergeCells('B130:B142');
        $sheet1->setCellValue('B130', 'cdnura');
        $sheet1->mergeCells('C130:D142');
        $sheet1->setCellValue('C130', 'Amended Credit/ Debit Note for unregistered Persons');

        $sheet1->getStyle('B130:D142')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 130: Field Name sub-header / Description
        $sheet1->mergeCells('E130:K130');
        $sheet1->setCellValue('E130', "Amended Credit/ Debit Notes issued to the unregistered persons against interstate invoice value is more than Rs 1.0 lakh\nNote : Value should be more than INR 100000 from August 2024 return period onwards.\nNote : Value should be more than INR 250000 upto July 2024 return period.");
        $sheet1->getStyle('E130')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(130)->setRowHeight(60);

        // Fields Data for CDNURA
        $cdnuraFields = [
            ['1. UR Type*', 'Select the type of supply to Unregistered Taxpayers (UR) against which the document has been issued. Select "EXPWP" or "EXPWOP" for export, "B2CL" for supplies to consumers for dropdown based on...'],
            ['2. Original Note Number*', 'Enter the original credit/debit note number. Ensure that the format is'],
            ['3. Original Note date*', 'Enter original credit/debit note date in DD-MMM-YYYY. E.g.'],
            ['4. Revised Note Number*', 'Enter the revised credit/debit note number. Ensure that the format is'],
            ['5. Revised Note date*', 'Enter revised credit/debit note/Refund voucher date in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['6. Note Type*', "In the Note Type column, enter \"D\" if the note is Debit note, enter \"C\" if note is credit note or enter \"R\" for refund voucher."],
            ['7. Place of Supply', 'Declare the place of supply based on the original document.'],
            ['8. Note value*', 'Amount should be up to 2 decimal digits.'],
            ['9. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['10. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['11.Taxable value', 'Enter the taxable value of the supplied goods or services for each rate line item -up to 2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['12. Cess Amount', 'Enter the total Cess amount.']
        ];

        $row = 131;
        foreach ($cdnuraFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole CDNURA table area
        $sheet1->getStyle('E130:K142')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR EXP ---
        // Worksheet Name and Reference for EXP
        $sheet1->mergeCells('B143:B153');
        $sheet1->setCellValue('B143', 'exp');
        $sheet1->mergeCells('C143:D153');
        $sheet1->setCellValue('C143', 'Export');

        $sheet1->getStyle('B143:D153')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 143: Field Name sub-header / Description
        $sheet1->mergeCells('E143:K143');
        $sheet1->setCellValue('E143', "Exports supplies including supplies to SEZ/SEZ Developer or deemed exports");
        $sheet1->getStyle('E143')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(143)->setRowHeight(30);

        // Fields Data for EXP
        $expFields = [
            ['1.Export Type*', 'In the Type column, enter WPAY if the Export is with payment of tax or else enter WOPAY.'],
            ['2. Invoice number*', 'Enter the Invoice number issued to the registered receiver. Ensure that the format is...'],
            ['3. Invoice Date*', 'Enter date of invoice in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['4. Invoice value*', 'Enter the invoice value of the goods or services- up to 2 decimal Digits.'],
            ['5. Port Code', 'Enter the six digit code of port through which goods were exported...'],
            ['6. Shipping Bill Number', 'Enter the unique reference number of shipping bill. This information if not available at the timing of submitting the return the same may be left blank and provided later.'],
            ['7. Shipping Bill Date', 'Enter the date of shipping bill. This information if not available at the timing of submitting the return the same may be left...'],
            ['8. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax..."],
            ['9. Rate', 'Enter the applicable integrated tax rate.'],
            ['10. Taxable Value', 'Enter the taxable value of the supplied goods or services for each rate line item -up to 2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.']
        ];

        $row = 144;
        foreach ($expFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole EXP table area
        $sheet1->getStyle('E143:K153')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR EXPA ---
        // Worksheet Name and Reference for EXPA
        $sheet1->mergeCells('B154:B166');
        $sheet1->setCellValue('B154', 'expa');
        $sheet1->mergeCells('C154:D166');
        $sheet1->setCellValue('C154', 'Amended Export'); // Fixed reference cell in logic below

        $sheet1->getStyle('B154:D166')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 154: Field Name sub-header / Description
        $sheet1->mergeCells('E154:K154');
        $sheet1->setCellValue('E154', "Amended Exports supplies including supplies to SEZ/SEZ Developer or deemed exports");
        $sheet1->getStyle('E154')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(154)->setRowHeight(30);

        // Fields Data for EXPA
        $expaFields = [
            ['1.Export Type*', 'In the Type column, enter WPAY if the Export is with payment of tax or else enter WOPAY.'],
            ['2. Original Invoice number*', 'Enter the Original Invoice number issued to the registered receiver.'],
            ['3. Original Invoice Date*', 'Enter original date of invoice in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['4. Revised Invoice number*', 'Enter the revised Invoice number issued to the registered receiver.'],
            ['5. Revised Invoice Date*', 'Enter revised date of invoice in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['6. Invoice value*', 'Enter the invoice value of the goods or services- up to 2 decimal Digits.'],
            ['7. Port Code', 'Enter the six digit code of port through which goods were exported. Please refer to the list of port codes available on the GST common portal. This is not required in case of export of services.'],
            ['8. Shipping Bill Number', 'Enter the unique reference number of shipping bill. This information if not available at the timing of submitting the return the same may be left blank and provided later.'],
            ['9. Shipping Bill Date', 'Enter the date of shipping bill. This information if not available at the timing of submitting the return the same may be left blank and provided later.'],
            ['10. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax..."],
            ['11. Rate', 'Enter the applicable integrated tax rate.'],
            ['12. Taxable Value', 'Enter the taxable value of the supplied goods or services for each rate line item -up to 2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.']
        ];

        $row = 155;
        foreach ($expaFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole EXPA table area
        $sheet1->getStyle('E154:K166')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR AT ---
        // Worksheet Name and Reference for AT
        $sheet1->mergeCells('B167:B172');
        $sheet1->setCellValue('B167', 'at');
        $sheet1->mergeCells('C167:D172');
        $sheet1->setCellValue('C167', 'Tax liability on advances');

        $sheet1->getStyle('B167:D172')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 167: Field Name sub-header / Description
        $sheet1->mergeCells('E167:K167');
        $sheet1->setCellValue('E167', "Tax liability arising on account of receipt of consideration for which invoices have not been issued in the same month");
        $sheet1->getStyle('E167')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(167)->setRowHeight(30);

        // Fields Data for AT
        $atFields = [
            ['1. Place of Supply(POS)*', 'Select the code of the state from drop down list for the place of supply.'],
            ['2. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['3. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['4. Gross advance received*', 'Enter the amount of advance received excluding the tax portion.'],
            ['5. Cess Amount', 'Enter the total Cess amount collected/payable.']
        ];

        $row = 168;
        foreach ($atFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole AT table area
        $sheet1->getStyle('E167:K172')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ATA ---
        // Worksheet Name and Reference for ATA
        $sheet1->mergeCells('B173:B180');
        $sheet1->setCellValue('B173', 'ata');
        $sheet1->mergeCells('C173:D180');
        $sheet1->setCellValue('C173', 'Amended Tax liability on advances');

        $sheet1->getStyle('B173:D180')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 173: Field Name sub-header / Description
        $sheet1->mergeCells('E173:K173');
        $sheet1->setCellValue('E173', "Amended Tax liability arising on account of receipt of consideration for which invoices have not been issued in the same tax period.");
        $sheet1->getStyle('E173')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(173)->setRowHeight(40);

        // Fields Data for ATA
        $ataFields = [
            ['1.Financial Year', 'Select the financial year'],
            ['2.Original Month', 'Select the Month'],
            ['3. Original Place of Supply(POS)*', 'Select the code of the state from drop down list for the place of supply.'],
            ['4. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['5. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['6. Gross advance received*', 'Enter the amount of advance received excluding the tax portion.'],
            ['7. Cess Amount', 'Enter the total Cess amount collected/payable.']
        ];

        $row = 174;
        foreach ($ataFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ATA table area
        $sheet1->getStyle('E173:K180')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ATADJ ---
        // Worksheet Name and Reference for ATADJ
        $sheet1->mergeCells('B181:B186');
        $sheet1->setCellValue('B181', 'atadj');
        $sheet1->mergeCells('C181:D186');
        $sheet1->setCellValue('C181', 'Advance adjustments');

        $sheet1->getStyle('B181:D186')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 181: Field Name sub-header / Description
        $sheet1->mergeCells('E181:K181');
        $sheet1->setCellValue('E181', "Adjustment of tax liability for tax already paid on advance receipt of consideration and invoices issued in the current period for the supplies.");
        $sheet1->getStyle('E181')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(181)->setRowHeight(40);

        // Fields Data for ATADJ
        $atadjFields = [
            ['1. Place of Supply(POS)*', 'Select the code of the state from drop down list for the place of supply.'],
            ['2. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['3. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['4. Gross advance adjusted*', 'Enter the amount of advance on which tax has already been paid in earlier tax period and invoices are declared during this tax period. This amount is excluding the tax portion'],
            ['5. Cess Amount', 'Enter the total Cess amount to be adjusted']
        ];

        $row = 182;
        foreach ($atadjFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ATADJ table area
        $sheet1->getStyle('E181:K186')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ATADJA ---
        // Worksheet Name and Reference for ATADJA
        $sheet1->mergeCells('B187:B194');
        $sheet1->setCellValue('B187', 'atadja');
        $sheet1->mergeCells('C187:D194');
        $sheet1->setCellValue('C187', 'Amended Advance adjustments');

        $sheet1->getStyle('B187:D194')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 187: Field Name sub-header / Description
        $sheet1->mergeCells('E187:K187');
        $sheet1->setCellValue('E187', "Amended Adjustment of tax liability for tax already paid on advance receipt of consideration and invoices issued in the current period for the supplies.");
        $sheet1->getStyle('E187')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(187)->setRowHeight(40);

        // Fields Data for ATADJA
        $atadjaFields = [
            ['1.Financial Year', 'Select the financial year'],
            ['2.Original Month', 'Select the Month'],
            ['3. Original Place of Supply(POS)*', 'Select the code of the state from drop down list for the place of supply.'],
            ['4. Applicable % of Tax Rate', "If the supply is eligible to be taxed at 65% of the existing rate of tax, select '65%' from dropdown; else blank."],
            ['5. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['6. Gross advance adjusted*', 'Enter the amount of advance on which tax has already been paid in earlier tax period and invoices are declared during this tax period. This amount is excluding the tax portion'],
            ['7. Cess Amount', 'Enter the total Cess amount to be adjusted']
        ];

        $row = 188;
        foreach ($atadjaFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ATADJA table area
        $sheet1->getStyle('E187:K194')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR EXEMP ---
        // Worksheet Name and Reference for EXEMP
        $sheet1->mergeCells('B195:B199');
        $sheet1->setCellValue('B195', 'exemp');
        $sheet1->mergeCells('C195:D199');
        $sheet1->setCellValue('C195', 'Nil Rated, Exempted and Non GST supplies');

        $sheet1->getStyle('B195:D199')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 195: Field Name sub-header / Description
        $sheet1->mergeCells('E195:K195');
        $sheet1->setCellValue('E195', "Details of Nil Rated, Exempted and Non GST Supplies made during the tax period");
        $sheet1->getStyle('E195')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(195)->setRowHeight(30);

        // Fields Data for EXEMP
        $exempFields = [
            ['1. Description', 'Indicates the type of supply.'],
            ['2.Nil rated supplies', 'Declare the value of supplies made under the "Nil rated" category for the supply type selected in 1. above. The amount to be declared here should exclude amount already declared in B2B and B2CL table as line items in tax invoice.'],
            ['3.Exempted (Other than Nil rated/non-GST supply)', 'Declare the value of supplies made under the "Exempted "category for the supply type selected in 1. above.'],
            ['4.Non GST Supplies', 'Declare the value of supplies made under the "Non GST" category for the supply type selected in 1. above. This column is to capture all the supplies made by the taxpayer which are out of the purview of GST']
        ];

        $row = 196;
        foreach ($exempFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(50);
            $row++;
        }

        // Apply borders to the whole EXEMP table area
        $sheet1->getStyle('E195:K199')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR HSN SUMMARY (B2B) ---
        // Worksheet Name and Reference for HSN Summary (B2B)
        $sheet1->mergeCells('B200:B211');
        $sheet1->setCellValue('B200', 'HSN Summary');
        $sheet1->mergeCells('C200:D211');
        $sheet1->setCellValue('C200', 'HSN Summary of B2B Supplies');

        $sheet1->getStyle('B200:D211')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 200: Field Name sub-header / Description
        $sheet1->mergeCells('E200:K200');
        $sheet1->setCellValue('E200', "HSN wise summary of goods /services supplied during the tax period");
        $sheet1->getStyle('E200')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(200)->setRowHeight(30);

        // Fields Data for HSN Summary (B2B)
        $hsnB2BFields = [
            ['1. HSN*', 'Enter the HSN Code for the supplied goods or Services. Minimum digit'],
            ['2. Description', 'Enter the description of the supplied goods or Services. Description'],
            ['3. UQC*', 'Select the applicable Unit Quantity Code from the drop down. For'],
            ['4. Total Quantity*', 'Enter the total quantity of the supplied goods or Services- up to 2'],
            ['5. Total Value', 'Enter the invoice value of the goods or services-up to 2 decimal Digits.'],
            ['6. Rate', 'Select the Rate of Tax for the HSN selected from the dropdown. This'],
            ['7. Taxable Value*', 'Enter the total taxable value of the supplied goods or services- up to 2'],
            ['8. Integrated Tax Amount', 'Enter the total Integrated tax amount collected/payable.'],
            ['9. Central Tax Amount', 'Enter the total Central tax amount collected/payable.'],
            ['10. State/UT Tax Amount', 'Enter the total State/UT tax amount collected/payable.'],
            ['11. Cess Amount', 'Enter the total Cess amount collected/payable.']
        ];

        $row = 201;
        foreach ($hsnB2BFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(30);
            $row++;
        }

        // Apply borders to the whole HSN B2B table area
        $sheet1->getStyle('E200:K211')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR HSN SUMMARY (B2C) ---
        // Worksheet Name and Reference for HSN Summary (B2C)
        $sheet1->mergeCells('B212:B223');
        $sheet1->setCellValue('B212', 'HSN Summary');
        $sheet1->mergeCells('C212:D223');
        $sheet1->setCellValue('C212', 'HSN Summary of B2C Supplies');

        $sheet1->getStyle('B212:D223')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 212: Field Name sub-header / Description
        $sheet1->mergeCells('E212:K212');
        $sheet1->setCellValue('E212', "HSN wise summary of goods /services supplied during the tax period");
        $sheet1->getStyle('E212')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(212)->setRowHeight(30);

        // Fields Data for HSN Summary (B2C)
        $hsnB2CFields = [
            ['1. HSN*', 'Enter the HSN Code for the supplied goods or Services. Minimum digit'],
            ['2. Description', 'Enter the description of the supplied goods or Services. Description'],
            ['3. UQC*', 'Select the applicable Unit Quantity Code from the drop down. For'],
            ['4. Total Quantity*', 'Enter the total quantity of the supplied goods or Services- up to 2'],
            ['5. Total Value', 'Enter the invoice value of the goods or services-up to 2 decimal Digits.'],
            ['6. Rate', 'Select the Rate of Tax for the HSN selected from the dropdown. This'],
            ['7. Taxable Value*', 'Enter the total taxable value of the supplied goods or services- up to 2'],
            ['8. Integrated Tax Amount', 'Enter the total Integrated tax amount collected/payable.'],
            ['9. Central Tax Amount', 'Enter the total Central tax amount collected/payable.'],
            ['10. State/UT Tax Amount', 'Enter the total State/UT tax amount collected/payable.'],
            ['11. Cess Amount', 'Enter the total Cess amount collected/payable.']
        ];

        $row = 213;
        foreach ($hsnB2CFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(30);
            $row++;
        }

        // Apply borders to the whole HSN B2C table area
        $sheet1->getStyle('E212:K223')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR DOCS ---
        // Worksheet Name and Reference for DOCS
        $sheet1->mergeCells('B224:B229');
        $sheet1->setCellValue('B224', 'docs');
        $sheet1->mergeCells('C224:D229');
        $sheet1->setCellValue('C224', 'List of Documents issued');

        $sheet1->getStyle('B224:D229')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 224: Field Name sub-header / Description
        $sheet1->mergeCells('E224:K224');
        $sheet1->setCellValue('E224', "Details of various documents issued by the taxpayer during the tax period");
        $sheet1->getStyle('E224')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(224)->setRowHeight(30);

        // Fields Data for DOCS
        $docsFields = [
            ['1. Nature of Document*', 'Select the applicable document type from the drop down.'],
            ['2. Sr. No From*', 'Enter the invoice/document series start number.'],
            ['3. Sr. No To*', 'Enter the invoice/document series end number.'],
            ['4. Total Number*', 'Enter the total no of documents in this particular series.'],
            ['5. Cancelled', 'No of documents cancelled in the particular series.']
        ];

        $row = 225;
        foreach ($docsFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(30);
            $row++;
        }

        // Apply borders to the whole DOCS table area
        $sheet1->getStyle('E224:K229')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ECO ---
        // Worksheet Name and Reference for ECO
        $sheet1->mergeCells('B230:B238');
        $sheet1->setCellValue('B230', 'eco');
        $sheet1->mergeCells('C230:D238');
        $sheet1->setCellValue('C230', 'Supplies through ECO');

        $sheet1->getStyle('B230:D238')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 230: Field Name sub-header / Description
        $sheet1->mergeCells('E230:K230');
        $sheet1->setCellValue('E230', "Details of supplies through Electronic Commerce Operator");
        $sheet1->getStyle('E230')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(230)->setRowHeight(30);

        // Fields Data for ECO
        $ecoFields = [
            ['1. Nature of Supply*', 'Select from the dropdown whether the supply is Liable to collect tax u/s 52(TCS) or Liable to pay tax u/s 9(5)'],
            ['2. E-Commerce Operator GSTIN*', 'Enter the GSTIN of E commerce operator'],
            ['3. E-Commerce Operator Name', 'Enter the name of E commerce operator'],
            ['4. Net value of supplies*', 'Enter the total value indicated in the document of the supplied goods or services- with 2 decimal Digits.'],
            ['5. Integrated Tax Amount', 'Enter the total integrated tax amount collected/payable.'],
            ['6. Central Tax Amount', 'Enter the total central tax amount collected/payable.'],
            ['7. State/UT Tax Amount', 'Enter the total state/UT tax amount collected/payable.'],
            ['8. Cess Amount', 'Enter the cess amount collected/payable.']
        ];

        $row = 231;
        foreach ($ecoFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ECO table area
        $sheet1->getStyle('E230:K238')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ECOA ---
        // Worksheet Name and Reference for ECOA
        $sheet1->mergeCells('B239:B249');
        $sheet1->setCellValue('B239', 'ecoa');
        $sheet1->mergeCells('C239:D249');
        $sheet1->setCellValue('C239', 'Amended Supplies through ECO');

        $sheet1->getStyle('B239:D249')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 239: Field Name sub-header / Description
        $sheet1->mergeCells('E239:K239');
        $sheet1->setCellValue('E239', "Details of amended supplies through Electronic Commerce Operator");
        $sheet1->getStyle('E239')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(239)->setRowHeight(30);

        // Fields Data for ECOA
        $ecoaFields = [
            ['1. Nature of Supply*', 'Select from the dropdown whether the supply is Liable to collect tax u/s 52(TCS) or Liable to pay tax u/s 9(5)'],
            ['2. Financial Year*', 'Select the financial year'],
            ['3. Original Month*', 'Select the Month'],
            ['4. E-Commerce Operator GSTIN*', 'Enter the GSTIN of E commerce operator'],
            ['5. E-Commerce Operator Name', 'Enter the name of E commerce operator'],
            ['6. Net value of supplies*', 'Enter the total value indicated in the document of the supplied goods or services- with 2 decimal Digits.'],
            ['7. Integrated Tax Amount', 'Enter the total integrated tax amount collected/payable.'],
            ['8. Central Tax Amount', 'Enter the total central tax amount collected/payable.'],
            ['9. State/UT Tax Amount', 'Enter the total state/UT tax amount collected/payable.'],
            ['10. Cess Amount', 'Enter the total cess amount collected/payable.']
        ];

        $row = 240;
        foreach ($ecoaFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ECOA table area
        $sheet1->getStyle('E239:K249')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ECOB2B ---
        // Worksheet Name and Reference for ECOB2B
        $sheet1->mergeCells('B250:B262');
        $sheet1->setCellValue('B250', 'ecob2b');
        $sheet1->mergeCells('C250:D262');
        $sheet1->setCellValue('C250', 'Supplies U/s 9(5)');

        $sheet1->getStyle('B250:D262')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 250: Field Name sub-header / Description
        $sheet1->mergeCells('E250:K250');
        $sheet1->setCellValue('E250', "Details of supplies U/s 9(5)-15-B2B");
        $sheet1->getStyle('E250')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(250)->setRowHeight(30);

        // Fields Data for ECOB2B
        $ecob2bFields = [
            ['1. GSTIN/UIN of Supplier', 'Enter the GSTIN or UIN of the supplier. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the document from GST portal'],
            ['2. Supplier Name', 'Enter the name of supplier'],
            ['3. GSTIN/UIN of Recipient', 'Enter the GSTIN or UIN of the receiver. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the document from GST portal'],
            ['4. Recipient Name', 'Enter the name of recipient'],
            ['5. Document Number', 'Enter the Document number of documents issued to registered recipients. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-) .The total number of characters should not be more than 16.6'],
            ['6. Document date', 'Enter the document date in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['7. Value of supplies made', 'Enter the total value indicated in the document of the supplied goods or services- with 2 decimal Digits.'],
            ['8. Place Of Supply*', 'Select the code of the state from drop down list for the place of supply.'],
            ['9. Document type', 'Select from the dropdown whether the supply is regular B2B, or deemed export, or SEZ supplies, or NA'],
            ['10. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['11. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['12. Cess Amount', 'Enter the cess amount collected/payable.']
        ];

        $row = 251;
        foreach ($ecob2bFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ECOB2B table area
        $sheet1->getStyle('E250:K262')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ECOURP2B ---
        // Worksheet Name and Reference for ECOURP2B
        $sheet1->mergeCells('B263:B273');
        $sheet1->setCellValue('B263', 'ecourp2b');
        $sheet1->mergeCells('C263:D273');
        $sheet1->setCellValue('C263', 'Supplies U/s 9(5)');

        $sheet1->getStyle('B263:D273')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 263: Field Name sub-header / Description
        $sheet1->mergeCells('E263:K263');
        $sheet1->setCellValue('E263', "Details of supplies U/s 9(5)-15-URP2B");
        $sheet1->getStyle('E263')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(263)->setRowHeight(30);

        // Fields Data for ECOURP2B
        $ecourp2bFields = [
            ['1. GSTIN/UIN of Recipient', 'Enter the GSTIN or UIN of the receiver. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the document from GST portal'],
            ['2. Recipient Name', 'Enter the name of recipient'],
            ['3. Document Number', 'Enter the Document number of documents issued to registered recipients. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-) .The total number of characters should not be more than 16.6'],
            ['4. Document date', 'Enter the document date in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['5. Value of supplies made', 'Enter the total value indicated in the document of the supplied goods or services- with 2 decimal Digits.'],
            ['6. Place Of Supply*', 'Select the code of the state from drop down list for the place of supply.'],
            ['7. Document type', 'Select from the dropdown whether the supply is regular B2B, or deemed export, or SEZ supplies, or NA'],
            ['8. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['9. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['10. Cess Amount', 'Enter the cess amount collected/payable.']
        ];

        $row = 264;
        foreach ($ecourp2bFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ECOURP2B table area
        $sheet1->getStyle('E263:K273')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ECOB2C ---
        // Worksheet Name and Reference for ECOB2C
        $sheet1->mergeCells('B274:B280');
        $sheet1->setCellValue('B274', 'ecob2c');
        $sheet1->mergeCells('C274:D280');
        $sheet1->setCellValue('C274', 'Supplies U/s 9(5)');

        $sheet1->getStyle('B274:D280')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 274: Field Name sub-header / Description
        $sheet1->mergeCells('E274:K274');
        $sheet1->setCellValue('E274', "Details of supplies U/s 9(5)-15-B2C");
        $sheet1->getStyle('E274')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(274)->setRowHeight(30);

        // Fields Data for ECOB2C
        $ecob2cFields = [
            ['1. GSTIN/UIN of Supplier', 'Enter the GSTIN or UIN of the supplier. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the document from GST portal'],
            ['2. Supplier Name', 'Enter the name of supplier'],
            ['3. Place of Supply*', 'Select the code of the state from drop down list for the place of supply.'],
            ['4. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['5. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['6. Cess Amount', 'Enter the cess amount collected/payable.']
        ];

        $row = 275;
        foreach ($ecob2cFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ECOB2C table area
        $sheet1->getStyle('E274:K280')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ECOURP2C ---
        // Worksheet Name and Reference for ECOURP2C
        $sheet1->mergeCells('B281:B285');
        $sheet1->setCellValue('B281', 'ecourp2c');
        $sheet1->mergeCells('C281:D285');
        $sheet1->setCellValue('C281', 'Supplies U/s 9(5)');

        $sheet1->getStyle('B281:D285')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 281: Field Name sub-header / Description
        $sheet1->mergeCells('E281:K281');
        $sheet1->setCellValue('E281', "Details of supplies U/s 9(5)-15-URP2C");
        $sheet1->getStyle('E281')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(281)->setRowHeight(30);

        // Fields Data for ECOURP2C
        $ecourp2cFields = [
            ['1. Place Of Supply*', 'Select the code of the state from drop down list for the place of supply.'],
            ['2. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['3. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['4. Cess Amount', 'Enter the cess amount collected/payable.']
        ];

        $row = 282;
        foreach ($ecourp2cFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ECOURP2C table area
        $sheet1->getStyle('E281:K285')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ECOAB2B ---
        // Worksheet Name and Reference for ECOAB2B
        $sheet1->mergeCells('B286:B300');
        $sheet1->setCellValue('B286', 'ecoab2b');
        $sheet1->mergeCells('C286:D300');
        $sheet1->setCellValue('C286', 'Amended Supplies U/s 9(5)');

        $sheet1->getStyle('B286:D300')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 286: Field Name sub-header / Description
        $sheet1->mergeCells('E286:K286');
        $sheet1->setCellValue('E286', "Details of Amended supplies U/s 9(5)-15A-B2B");
        $sheet1->getStyle('E286')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(286)->setRowHeight(30);

        // Fields Data for ECOAB2B
        $ecoab2bFields = [
            ['1. GSTIN/UIN of Supplier', 'Enter the GSTIN or UIN of the supplier. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the document from GST portal'],
            ['2. Supplier Name', 'Enter the name of supplier'],
            ['3. GSTIN/UIN of Recipient', 'Enter the GSTIN or UIN of the receiver. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the document from GST portal'],
            ['4. Recipient Name', 'Enter the name of recipient'],
            ['5. Original Document Number', 'Enter the Original Document number. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-) .The total number of characters should not be more than 16.'],
            ['6. Original Document date', 'Enter Original date of document in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['7. Revised Document Number', 'Enter the Revised Document number. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-) .The total number of characters should not be more than 16.'],
            ['8. Revised Document date', 'Enter date of document in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['9. Value of supplies made', 'Enter the total value indicated in the invoice of the supplied goods or services- with 2 decimal Digits.'],
            ['10. Place Of Supply', 'Select the code of the state from drop down list for the place of supply.'],
            ['11. Document type', 'Select from the dropdown whether the supply is regular B2B, or deemed export, or SEZ supplies, or NA'],
            ['12. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['13. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['14. Cess Amount', 'Enter the cess amount collected/payable.']
        ];

        $row = 287;
        foreach ($ecoab2bFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ECOAB2B table area
        $sheet1->getStyle('E286:K300')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- NEW DATA FOR ECOAURP2B ---
        // Worksheet Name and Reference for ECOAURP2B
        $sheet1->mergeCells('B301:B313');
        $sheet1->setCellValue('B301', 'ecoaurp2b');
        $sheet1->mergeCells('C301:D313');
        $sheet1->setCellValue('C301', 'Amended Supplies U/s 9(5)');

        $sheet1->getStyle('B301:D313')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 301: Field Name sub-header / Description
        $sheet1->mergeCells('E301:K301');
        $sheet1->setCellValue('E301', "Details of Amended supplies U/s 9(5)-15A-URP2B");
        $sheet1->getStyle('E301')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(301)->setRowHeight(30);

        // Fields Data for ECOAURP2B
        $ecoaurp2bFields = [
            ['1. GSTIN/UIN of Recipient', 'Enter the GSTIN or UIN of the receiver. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the document from GST portal'],
            ['2. Recipient Name', 'Enter the name of recipient'],
            ['3. Original Document Number', 'Enter the Original Document number. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-) .The total number of characters should not be more than 16.'],
            ['4. Original Document date', 'Enter Original date of document in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['5. Revised Document Number', 'Enter the Revised Document number. Ensure that the format is alpha-numeric with allowed special characters of slash(/) and dash(-) .The total number of characters should not be more than 16.'],
            ['6. Revised Document date', 'Enter date of document in DD-MMM-YYYY. E.g. 24-May-2017.'],
            ['7. Value of supplies made', 'Enter the total value indicated in the invoice of the supplied goods or services- with 2 decimal Digits.'],
            ['8. Place Of Supply', 'Select the code of the state from drop down list for the place of supply.'],
            ['9. Document type', 'Select from the dropdown whether the supply is regular B2B, or deemed export, or SEZ supplies, or NA'],
            ['10. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['11. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate line item -2 decimal Digits, The taxable value has to be computed as per GST valuation provisions.'],
            ['12. Cess Amount', 'Enter the cess amount collected/payable.']
        ];

        $row = 302;
        foreach ($ecoaurp2bFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // Apply borders to the whole ECOAURP2B table area
        $sheet1->getStyle('E301:K313')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- AMENDED SUPPLIES U/S 9(5) ---
        // Worksheet Name and Reference for ecoab2c (Amended B2C)
        $sheet1->mergeCells('B314:B322');
        $sheet1->setCellValue('B314', 'ecoab2c');
        $sheet1->mergeCells('C314:D322');
        $sheet1->setCellValue('C314', 'Amended Supplies U/s 9(5)');

        $sheet1->getStyle('B314:D322')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 314: Field Name sub-header / Description
        $sheet1->mergeCells('E314:K314');
        $sheet1->setCellValue('E314', "Details of Amended supplies U/s 9(5)-15A-B2C");
        $sheet1->getStyle('E314')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(314)->setRowHeight(30);

        // Fields Data for ecoab2c
        $ecoab2cFields = [
            ['1. Financial Year*', 'Select the financial year'],
            ['2. Original Month*', 'Select the Month'],
            ['3. GSTIN/UIN of Supplier', 'Enter the GSTIN or UIN of the supplier. E.g. 05AEJPP8087R1ZF. Check that the registration is active on the date of the document from GST portal'],
            ['4. Supplier Name', 'Enter the name of supplier'],
            ['5. Place of Supply', 'Select the code of the state from drop down list for the place of supply.'],
            ['6. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['7. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate'],
            ['8. Cess Amount', 'Enter the cess amount collected/payable.']
        ];

        $row = 315;
        foreach ($ecoab2cFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(45);
            $row++;
        }

        // Apply borders to the ecoab2c table area
        $sheet1->getStyle('E314:K322')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Worksheet Name and Reference for ecoaurp2c (Amended URP2C)
        $sheet1->mergeCells('B323:B329');
        $sheet1->setCellValue('B323', 'ecoaurp2c');
        $sheet1->mergeCells('C323:D329');
        $sheet1->setCellValue('C323', 'Amended Supplies U/s 9(5)');

        $sheet1->getStyle('B323:D329')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['color' => ['rgb' => '7030A0'], 'bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 323: Field Name sub-header / Description
        $sheet1->mergeCells('E323:K323');
        $sheet1->setCellValue('E323', "Details of Amended supplies U/s 9(5)-15A-URP2C");
        $sheet1->getStyle('E323')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['wrapText' => true]
        ]);
        $sheet1->getRowDimension(323)->setRowHeight(30);

        // Fields Data for ecoaurp2c
        $ecoaurp2cFields = [
            ['1. Financial Year*', 'Select the financial year'],
            ['2. Original Month*', 'Select the Month'],
            ['3. Place of Supply', 'Select the code of the state from drop down list for the place of supply.'],
            ['4. Rate*', 'Enter the combined (State tax + Central tax) or the integrated tax rate.'],
            ['5. Taxable Value*', 'Enter the taxable value of the supplied goods or services for each rate'],
            ['6. Cess Amount', 'Enter the cess amount collected/payable.']
        ];

        $row = 324;
        foreach ($ecoaurp2cFields as $field) {
            $sheet1->mergeCells("E$row:G$row");
            $sheet1->setCellValue("E$row", $field[0]);
            $sheet1->getStyle("E$row")->getFont()->setBold(true);

            $sheet1->mergeCells("H$row:K$row");
            $sheet1->setCellValue("H$row", $field[1]);
            $sheet1->getStyle("H$row")->getAlignment()->setWrapText(true);

            $sheet1->getRowDimension($row)->setRowHeight(45);
            $row++;
        }

        // Apply borders to the ecoaurp2c table area
        $sheet1->getStyle('E323:K329')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- Common mistakes in filling Excel template ---
        $row = 331;
        $sheet1->mergeCells("B$row:K$row");
        $sheet1->setCellValue("B$row", 'Common mistakes in filling Excel template');
        $sheet1->getStyle("B$row:K$row")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet1->getRowDimension($row)->setRowHeight(25);
        $row++;

        $mistakes = [
            "1. GSTIN of supplier/E-commerce should be a valid one. State code of supplier GSTIN and E-Commerce GSTIN should be the same.",
            "2. Duplication of invoices with the same tax rate shouldn't be there-otherwise System throws error as \"Non duplicated invoices were added & these invoices are duplicated\" at the time of import.",
            "3. Amount should be only up to 2 decimal digits",
            "4. Ensure that filling of excel should be strictly as per sample data to avoid errors.",
            "5. Copy paste data from the excel template not including the header rows 1 to 4 will throw an error.",
            "6. The work sheet name in the excel file of return data prepared by the return preparer should be the same as mentioned in the sample excel template.",
            "7. Master data sheet provides the inputs allowed in the mentioned data field. Inputs in the master data sheet have been used for the drop down lists in the worksheets."
        ];

        $startRowMistakes = $row;
        foreach ($mistakes as $mistake) {
            $sheet1->mergeCells("B$row:K$row");
            $sheet1->setCellValue("B$row", $mistake);
            $sheet1->getStyle("B$row")->getAlignment()->setWrapText(true);
            $sheet1->getRowDimension($row)->setRowHeight(35);
            $row++;
        }
        $endRowMistakes = $row - 1;

        // Apply outline border to mistakes section
        $sheet1->getStyle("B$startRowMistakes:K$endRowMistakes")->applyFromArray([
            'borders' => [
                'outline' => ['borderStyle' => Border::BORDER_THIN],
                'left' => ['borderStyle' => Border::BORDER_THIN],
                'right' => ['borderStyle' => Border::BORDER_THIN],
                'bottom' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Special Instructions Section
        $row = 340;
        $sheet1->mergeCells("B$row:K$row");
        $sheet1->setCellValue("B$row", 'Special Instructions');
        $sheet1->getStyle("B$row:K$row")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet1->getRowDimension($row)->setRowHeight(25);
        $row++;

        $instructions = [
            "1) To facilitate the declaration of date in the specified format \"dd-mmm-yyyy\", ensure the system date format of your computer is \"dd/mm/yyyy or dd-mm-yyyy\".",
            "2) For invoices containing multiple line items invoice level details like GSTIN/UIN, Invoice Number, Invoice Date and Place of Supply should be repeated for all the line items, in the absence of relevant information in the line items. This information if not available at the timing of submitting the return the same may be left blank and provided later.",
            "3) Taxable Value, Rate and cess amount as applicable to the line items may be different in the same invoice.",
            "4) On successful import of the data from the excel file to the offline utility tool, the tool takes care of proper placement of the same in the return format",
            "5) In the worksheets on the combined (central tax+state tax) tax or integrated tax rate has to be mentioned. The java tool will calculate the central tax, state tax or integrated tax. The tax pay",
            "6) In this first version worksheets are not being provided for uploading amendment details as these are not expected in the first GST return. Those will be provided in the next version.",
            "7) In the top of each excel worksheet , there is a summary row which gives the count or total of the key columns to help in reconciliation.",
            "8) The worksheets for furnishing exempt supplies details and issued documents details are being provided in this excel workbook template however the data cannot be imported fro",
            "9) The worksheets have some data as example. Please delete the data from all worksheets before use.",
            "10) The number mentioned in bracket in the top most row in each data entry worksheet refer to the corresponding table number in the notified GSTR 1 format. For example in b2b worksheet",
            "11) This excel workbook template works best with Microsoft Excel 2007 and above.",
            "12) Ensure that there are no stray entries in any cell of the sheets other than return related declaration under the indicated column headers.",
            "13) It is advisable that separate excel sheets be prepared for each month with the name having month name as a part of it's name. In case of multiple uploads for a month, the file name may",
            "14) In case of JSON files created by the offline tool , if the taxpayer is frequently importing invoice data in a tax period, he should name the different created JSON file of a part of a month/t",
            "15) Before importing the excel file in the offline tool for a particular tax period, it is advisable that the taxpayer should delete if any existing data of that tax period by clicking \"Delete All Data",
            "16) If one uploads the JSON file for a tax period with the same invoice number but differing details again, the later invoice details will overwrite the earlier details.",
            "17) In case of other sections where the consolidated details have to be furnished, the details of whole section furnished earlier would be overwritten by the later uploaded details.",
            "18) In case of b2b worksheet, if the invoice has been selected as subject to reverse charge, the top summary row excludes the value of cess amount as it is not collected by the supplier.",
            "19) HSN Summary is bifurcated into two sheets – one for B2B supplies and one for B2C supplies. Taxpayer would be required to provide details accordingly."
        ];

        $startRowInst = $row;
        foreach ($instructions as $inst) {
            $sheet1->mergeCells("B$row:K$row");
            $sheet1->setCellValue("B$row", $inst);
            $sheet1->getStyle("B$row")->getAlignment()->setWrapText(true);
            $sheet1->getRowDimension($row)->setRowHeight(35);
            $row++;
        }
        $endRowInst = $row - 1;
        $sheet1->getStyle("B$startRowInst:K$endRowInst")->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN], 'left' => ['borderStyle' => Border::BORDER_THIN], 'right' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Abbreviations Table
        $row = $row + 2;
        $abbreviations = [
            ['GSTIN', 'Goods and Services Taxpayer Identification Number'],
            ['GSTN', 'Goods and Services Tax Network'],
            ['HSN', 'Harmonized System of Nomenclature'],
            ['B2B', 'Registered Business to Registered Business'],
            ['SEZ', 'Special Economic Zone'],
            ['DE', 'Deemed Exports'],
            ['B2C', 'Registered Business to Unregistered Consumer'],
            ['POS', 'Place of Supply of Goods or Services – State code to be mentioned'],
            ['UIN', 'Unique Identity Number'],
            ['GSTR1', 'GST Return 1'],
            ['GST', 'Goods and Services Tax'],
            ['UQC', 'Unit Quantity Code'],
            ['ECO', 'E-Commerce Operator']
        ];

        $abbrStartRow = $row;
        foreach ($abbreviations as $abbr) {
            $sheet1->mergeCells("C$row:D$row");
            $sheet1->setCellValue("B$row", $abbr[0]);
            $sheet1->setCellValue("C$row", $abbr[1]);
            $sheet1->getStyle("B$row:D$row")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet1->getStyle("B$row")->getFont()->setBold(true);
            $sheet1->getRowDimension($row)->setRowHeight(25);
            $row++;
        }
    }

    // Tab 2: B2B, SEZ, DE
    // private function addB2B_SEZ_DESheet($spreadsheet)
    // {
    //     $sheet = $spreadsheet->createSheet();
    //     $sheet->setTitle('b2b,sez,de');

    //     // Column Widths
    //     $widths = [
    //         'A' => 20,
    //         'B' => 25,
    //         'C' => 15,
    //         'D' => 15,
    //         'E' => 25,
    //         'F' => 20,
    //         'G' => 15,
    //         'H' => 25,
    //         'I' => 15,
    //         'J' => 20,
    //         'K' => 10,
    //         'L' => 25,
    //         'M' => 15
    //     ];
    //     foreach ($widths as $col => $width) {
    //         $sheet->getColumnDimension($col)->setWidth($width);
    //     }

    //     // Summary Header Row 1
    //     // $sheet->mergeCells('A1:L1');
    //     $sheet->setCellValue('A1', 'Summary For B2B, SEZ, DE (4A, 4B, 6B, 6C)');
    //     $sheet->setCellValue('M1', 'HELP');


    //     // Summary Header Row 2
    //     $sheet->setCellValue('A2', 'No. of Recipients');
    //     $sheet->setCellValue('C2', 'No. of Invoices');
    //     $sheet->setCellValue('E2', 'Total Invoice Value');
    //     $sheet->setCellValue('L2', 'Total Taxable Value');
    //     $sheet->setCellValue('M2', 'Total Cess');

    //     // Summary Values Row 3
    //     $sheet->setCellValue('A3', '12');
    //     $sheet->setCellValue('C3', '20');
    //     $sheet->setCellValue('E3', '782223.00');
    //     $sheet->setCellValue('L3', '744974.03');
    //     $sheet->setCellValue('M3', '0.00');

    //     // Main Data Header Row 4
    //     $headers = [
    //         'GSTIN/UIN of Recipient',
    //         'Receiver Name',
    //         'Invoice Number',
    //         'Invoice date',
    //         'Invoice Value',
    //         'Place Of Supply',
    //         'Reverse Charge',
    //         'Applicable % of Tax Rate',
    //         'Invoice Type',
    //         'E-Commerce GSTIN',
    //         'Rate',
    //         'Taxable Value',
    //         'Cess Amount'
    //     ];
    //     $sheet->fromArray($headers, null, 'A4');

    //     // Dummy Data Rows 5+
    //     $data = [
    //         ['24BWNPH7876K1ZS', 'DHYEY COLLECTION', '168', '01-Nov-2025', '8299.00', '24-Gujarat', 'N', '', 'Regular B2B', '', '5.00', '7904.00', '0.00'],
    //         ['24BWNPH7876K1ZS', 'DHYEY COLLECTION', '169', '03-Nov-2025', '31920.00', '24-Gujarat', 'N', '', 'Regular B2B', '', '5.00', '30400.00', '0.00'],
    //         ['24AEUPY2881F1ZU', 'RUHU FASHION', '170', '04-Nov-2025', '54454.00', '24-Gujarat', 'N', '', 'Regular B2B', '', '5.00', '51860.50', '0.00'],
    //         ['24AGCPJ5470D1ZS', 'SHREEKALA DESIGNE', '171', '06-Nov-2025', '30385.00', '24-Gujarat', 'N', '', 'Regular B2B', '', '5.00', '28938.00', '0.00'],
    //         ['24ARKPS8310C2ZW', 'BALRAJ SAREES', '172', '07-Nov-2025', '47723.00', '24-Gujarat', 'N', '', 'Regular B2B', '', '5.00', '45450.00', '0.00'],
    //         ['24BWNPH7876K1ZS', 'DHYEY COLLECTION', '173', '08-Nov-2025', '21386.00', '24-Gujarat', 'N', '', 'Regular B2B', '', '5.00', '20368.00', '0.00'],
    //         ['24AEUPY2881F1ZU', 'RUHU FASHION', '174', '08-Nov-2025', '27227.00', '24-Gujarat', 'N', '', 'Regular B2B', '', '5.00', '25930.25', '0.00'],
    //         ['24BWNPH7876K1ZS', 'DHYEY COLLECTION', '175', '10-Nov-2025', '38304.00', '24-Gujarat', 'N', '', 'Regular B2B', '', '5.00', '36480.00', '0.00'],
    //     ];
    //     $sheet->fromArray($data, null, 'A5');

    //     // --- STYLING ---

    //     // Blue header styling (Rows 1 & 2)
    //     $blueHeaderStyle = [
    //         'font' => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    //         'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    //         'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
    //         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    //     ];
    //     $sheet->getStyle('A1:B1')->applyFromArray($blueHeaderStyle);
    //     $sheet->getStyle('A2:M2')->applyFromArray($blueHeaderStyle);
    //     $sheet->freezePane('A5');

    //     // Peach background for summary values (Row 3)
    //     $peachStyle = [
    //         // 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCE4D6']],
    //         'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    //         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    //     ];
    //     $sheet->getStyle('A3:M3')->applyFromArray($peachStyle);

    //     // Data Header Styling (Row 4)
    //     $dataHeaderStyle = [
    //         'font' => ['name' => 'Times New Roman', 'bold' => true],
    //         'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
    //         // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    //         'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    //     ];
    //     $sheet->getStyle('A4:M4')->applyFromArray($dataHeaderStyle);

    //     // Data Row Styling
    //     $sheet->getStyle('A5:M' . (count($data) + 4))->applyFromArray([
    //         // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    //         'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
    //     ]);
    //     $sheet->getRowDimension(4)->setRowHeight(30);
    // }
    private function addB2B_SEZ_DESheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();
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
        $sheet->setTitle('b2b,sez,de');

        /* ================= COLUMN WIDTHS ================= */
        $widths = [
            'A' => 20,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 25,
            'F' => 20,
            'G' => 15,
            'H' => 25,
            'I' => 20,
            'J' => 20,
            'K' => 10,
            'L' => 25,
            'M' => 15,
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        /* ================= FETCH ONLY VALID SALES ================= */
        $orders = Order::with(['user', 'orderItems'])
            ->where('isDeleted', 0)
            // ->where('gst_option', 'with_gst')
            ->where('branch_id', $branch_id)
            ->whereHas('user', function ($q) {
                $q->whereNotNull('gst_number')
                    ->where('gst_number', '!=', '');
            })
            ->get();

        /* ================= INITIALISE TOTALS ================= */
        $rows              = [];
        $uniqueRecipients  = [];
        $totalInvoiceValue = 0;
        $totalTaxableValue = 0;
        $totalCess         = 0;

        /* ================= PROCESS ORDERS ================= */
        foreach ($orders as $order) {

            $totalInvoiceValue                          += (float) $order->total_amount;
            $uniqueRecipients[$order->user->gst_number]  = true;

            // Group items by GST Rate
            $itemsByRate = [];

            foreach ($order->orderItems as $item) {

                $gstDetails = is_string($item->product_gst_details)
                    ? json_decode($item->product_gst_details, true)
                    : $item->product_gst_details;

                $rate = 0;
                if (is_array($gstDetails)) {
                    foreach ($gstDetails as $tax) {
                        $rate += (float) ($tax['tax_rate'] ?? 0);
                    }
                }

                $rateKey  = number_format($rate, 2, '.', '');

                if (! isset($itemsByRate[$rateKey])) {
                    $itemsByRate[$rateKey] = [
                        'taxable' => 0,
                        'cess'    => 0,
                    ];
                }

                $taxable                          = (float) $item->total_amount - (float) $item->product_gst_total;
                $itemsByRate[$rateKey]['taxable'] += $taxable;
                $totalTaxableValue                += $taxable;
            }

            // Dynamic POS
            $pos = $order->user->state_code
                ? $order->user->state_code . '-' . $this->stateName($order->user->state_code)
                : '';

            // Invoice Type
            $invoiceType = 'Regular B2B';
            if (! empty($order->is_sez)) {
                $invoiceType = $order->sez_with_payment ? 'SEZ with payment' : 'SEZ without payment';
            }
            if (! empty($order->is_deemed_export)) {
                $invoiceType = 'Deemed Export';
            }

            // Dynamic POS
            $pos = $order->user->state_code
                ? $order->user->state_code . '-' . $this->stateName($order->user->state_code)
                : '';

            // Invoice Type
            $invoiceType = 'Regular B2B';
            if (! empty($order->is_sez)) {
                $invoiceType = $order->sez_with_payment ? 'SEZ with payment' : 'SEZ without payment';
            }
            if (! empty($order->is_deemed_export)) {
                $invoiceType = 'Deemed Export';
            }

            foreach ($itemsByRate as $rate => $val) {
                $rows[] = [
                    $order->user->gst_number,
                    $order->user->name,
                    $order->order_number,
                    $order->created_at->format('d-M-Y'),
                    number_format($order->total_amount, 2, '.', ''),
                    $pos,
                    'N',
                    '',
                    $invoiceType,
                    '',
                    $rate,
                    number_format($val['taxable'], 2, '.', ''),
                    number_format($val['cess'], 2, '.', ''),
                ];
            }
        }

        /* ================= SUMMARY HEADER ================= */
        $sheet->setCellValue('A1', 'Summary For B2B, SEZ, DE (4A, 4B, 6B, 6C)');
        $sheet->setCellValue('M1', 'HELP');

        $sheet->setCellValue('A2', 'No. of Recipients');
        $sheet->setCellValue('C2', 'No. of Invoices');
        $sheet->setCellValue('E2', 'Total Invoice Value');
        $sheet->setCellValue('L2', 'Total Taxable Value');
        $sheet->setCellValue('M2', 'Total Cess');

        $sheet->setCellValue('A3', count($uniqueRecipients));
        $sheet->setCellValue('C3', $orders->count());
        $sheet->setCellValue('E3', number_format($totalInvoiceValue, 2, '.', ''));
        $sheet->setCellValue('L3', number_format($totalTaxableValue, 2, '.', ''));
        $sheet->setCellValue('M3', number_format($totalCess, 2, '.', ''));

        /* ================= TABLE HEADER ================= */
        $headers = [
            'GSTIN/UIN of Recipient',
            'Receiver Name',
            'Invoice Number',
            'Invoice date',
            'Invoice Value',
            'Place Of Supply',
            'Reverse Charge',
            'Applicable % of Tax Rate',
            'Invoice Type',
            'E-Commerce GSTIN',
            'Rate',
            'Taxable Value',
            'Cess Amount',
        ];
        $sheet->fromArray($headers, null, 'A4');

        if (! empty($rows)) {
            $sheet->fromArray($rows, null, 'A5');
        }

        /* ================= STYLING ================= */
        $blueHeaderStyle = [
            'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('A2:M2')->applyFromArray($blueHeaderStyle);
        $sheet->freezePane('A5');
        $summaryStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A3:M3')->applyFromArray($summaryStyle);

        $dataHeaderStyle  = [
            'font'      => ['name' => 'Times New Roman', 'bold' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A4:M4')->applyFromArray($dataHeaderStyle);

        if (! empty($rows)) {
            $sheet->getStyle('A5:M' . (count($rows) + 4))
                ->applyFromArray(['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]]);
        }

        $sheet->getRowDimension(4)->setRowHeight(30);
    }
    // Tab 3: B2BA
    private function addB2BASheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();
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
        $sheet->setTitle('b2ba');

        // --- COLUMN WIDTHS (A-O) ---
        $widths = [
            'A' => 22,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 22,
            'F' => 20,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 18,
            'M' => 10,
            'N' => 15,
            'O' => 15
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For B2BA');
        $sheet->mergeCells('B1:D1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('E1:N1');
        $sheet->setCellValue('E1', 'Revised Details');
        $sheet->setCellValue('O1', 'HELP');

        // Row 1 Styling
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $lightBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'b4c6e7']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:D1')->applyFromArray($orangeStyle);
        $sheet->getStyle('E1:N1')->applyFromArray($blueStyle);
        $sheet->getStyle('O1')->applyFromArray($blueStyle);
        $sheet->getStyle('E1:O1')->applyFromArray($lightBlueStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->mergeCells('A2:B2');
        $sheet->setCellValue('A2', 'No. of Recipients');
        // $sheet->mergeCells('C2:F2');
        $sheet->setCellValue('C2', 'No. of Invoices');
        // $sheet->mergeCells('G2:L2');
        $sheet->setCellValue('G2', 'Total Invoice Value');
        $sheet->setCellValue('M2', 'Total Taxable Value');
        $sheet->setCellValue('N2', 'Total Cess');
        $sheet->setCellValue('O2', ''); // Blank under Help

        $sheet->getStyle('A2:O2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->mergeCells('A3:B3');
        // $sheet->setCellValue('A3', '0');
        // $sheet->mergeCells('C3:F3');
        $sheet->setCellValue('C3', '0');
        // $sheet->mergeCells('G3:L3');
        $sheet->setCellValue('G3', '0.00');
        $sheet->setCellValue('M3', '0.00');
        $sheet->setCellValue('N3', '0.00');
        $sheet->setCellValue('O3', '0.00');

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            // 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A3:O3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'GSTIN/UIN of Recipient',
            'Receiver Name',
            'Original Invoice Number',
            'Original Invoice date',
            'Revised Invoice Number',
            'Revised Invoice date',
            'Invoice Value',
            'Place Of Supply',
            'Reverse Charge',
            'Applicable % of Tax Rate',
            'Invoice Type',
            'E-Commerce GSTIN',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A4:O4')->applyFromArray($dataHeaderStyle);
        $sheet->getStyle('E4:O4')->applyFromArray($lightBlueStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(40);
    }

    // Tab 4: B2CL
    private function addB2CLSheet($spreadsheet)
    {
        /* ================= BRANCH ID LOGIC ================= */
        $user               = Auth::guard('api')->user();
        $role               = $user->role;
        $userId             = $user->id;
        $userBranchId       = $user->branch_id;
        $selectedSubAdminId = request()->selectedSubAdminId ?? $userId;

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

        $branchSetting = Setting::where('branch_id', $branch_id)->first();
        $branchStateCode = $branchSetting ? $branchSetting->state_code : null;

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('b2cl');

        // Column Widths
        $widths = [
            'A' => 20,
            'B' => 15,
            'C' => 15,
            'D' => 20,
            'E' => 15,
            'F' => 10,
            'G' => 15,
            'H' => 15,
            'I' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        /* ================= FETCH B2CL ORDERS (BRANCH WISE) ================= */
        // B2CL: Unregistered, Inter-state, Invoice Value > 2.5 Lakh
        $orders = Order::with(['user', 'orderItems'])
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->where('total_amount', '>', 250000)
            ->whereHas('user', function ($q) use ($branchStateCode) {
                $q->where(function ($query) {
                    $query->whereNull('gst_number')
                        ->orWhere('gst_number', '');
                });
                if ($branchStateCode) {
                    $q->where('state_code', '!=', $branchStateCode)
                        ->whereNotNull('state_code')
                        ->where('state_code', '!=', '');
                }
            })
            ->get();

        /* ================= INITIALISE TOTALS ================= */
        $rows = [];
        $totalInvoiceValue = 0;
        $totalTaxableValue = 0;
        $totalCess = 0;

        /* ================= PROCESS ORDERS ================= */
        foreach ($orders as $order) {
            $pos = $order->user->state_code
                ? $order->user->state_code . '-' . $this->stateName($order->user->state_code)
                : '';

            $invoiceValue = (float)$order->total_amount;
            $totalInvoiceValue += $invoiceValue;

            // Group items by GST Rate
            $itemsByRate = [];

            foreach ($order->orderItems as $item) {
                // GST RATE CALCULATION
                $gstDetails = is_string($item->product_gst_details)
                    ? json_decode($item->product_gst_details, true)
                    : $item->product_gst_details;

                $rate = 0;
                if (is_array($gstDetails)) {
                    foreach ($gstDetails as $tax) {
                        $rate += (float)($tax['tax_rate'] ?? 0);
                    }
                }

                $rateKey = number_format($rate, 2, '.', '');

                if (! isset($itemsByRate[$rateKey])) {
                    $itemsByRate[$rateKey] = [
                        'taxable' => 0,
                        'cess'    => 0,
                    ];
                }

                $taxable = (float)$item->total_amount - (float)$item->product_gst_total;
                $cess = (float)($item->cess ?? 0);

                $itemsByRate[$rateKey]['taxable'] += $taxable;
                $itemsByRate[$rateKey]['cess']    += $cess;

                $totalTaxableValue += $taxable;
                $totalCess += $cess;
            }

            foreach ($itemsByRate as $rate => $val) {
                $rows[] = [
                    $order->order_number,                  // Invoice Number
                    Carbon::parse($order->created_at)->format('d-M-Y'), // Invoice date
                    number_format($invoiceValue, 2, '.', ''), // Invoice Value
                    $pos,                                 // Place of Supply
                    '',                                   // Applicable % of Tax Rate
                    $rate,                                // Rate
                    number_format($val['taxable'], 2, '.', ''),  // Taxable Value
                    number_format($val['cess'], 2, '.', ''),     // Cess
                    ''                                    // E-Commerce GSTIN
                ];
            }
        }

        // Summary Header Row 1
        // $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Summary For B2CL(5)');
        $sheet->setCellValue('I1', 'HELP');

        // Summary Header Row 2
        // $sheet->mergeCells('A2:B2');
        $sheet->setCellValue('A2', 'No. of Invoices');
        $sheet->setCellValue('C2', 'Total Invoice Value');
        $sheet->setCellValue('G2', 'Total Taxable Value');
        $sheet->setCellValue('H2', 'Total Cess');

        // Summary Values Row 3
        $sheet->setCellValue('A3', count($orders));
        $sheet->setCellValue('C3', number_format($totalInvoiceValue, 2, '.', ''));
        $sheet->setCellValue('G3', number_format($totalTaxableValue, 2, '.', ''));
        $sheet->setCellValue('H3', number_format($totalCess, 2, '.', ''));

        // Main Data Header Row 4
        $headers = [
            'Invoice Number',
            'Invoice date',
            'Invoice Value',
            'Place Of Supply',
            'Applicable % of Tax Rate',
            'Rate',
            'Taxable Value',
            'Cess Amount',
            'E-Commerce GSTIN'
        ];
        $sheet->fromArray($headers, null, 'A4');

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A5');
        }

        // --- STYLING ---

        // Blue header styling (Rows 1 & 2)
        $blueHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A1')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('A2:I2')->applyFromArray($blueHeaderStyle);

        // Summary Values Row 3 styling
        $sheet->getStyle('A3:I3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Data Header Styling (Row 4)
        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ];
        $sheet->getStyle('A4:I4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(35);
    }

    // Tab 5: B2CLA
    private function addB2CLASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('b2cla');

        // Column Widths
        $widths = [
            'A' => 22,
            'B' => 20,
            'C' => 22,
            'D' => 22,
            'E' => 20,
            'F' => 15,
            'G' => 15,
            'H' => 10,
            'I' => 15,
            'J' => 15,
            'K' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For B2CLA');
        $sheet->mergeCells('B1:c1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('E1:J1');
        $sheet->setCellValue('E1', 'Revised Details');
        $sheet->setCellValue('K1', 'HELP');

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('A2', 'No. of Invoices');
        $sheet->setCellValue('F2', 'Total Inv Value');
        $sheet->setCellValue('I2', 'Total Taxable Value');
        $sheet->setCellValue('J2', 'Total Cess');

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('A3', '0');
        $sheet->setCellValue('F3', '0.00');
        $sheet->setCellValue('I3', '0.00');
        $sheet->setCellValue('J3', '0.00');

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Original Invoice Number',
            'Original Invoice date',
            'Original Place Of Supply',
            'Revised Invoice Number',
            'Revised Invoice date',
            'Invoice Value',
            'Applicable % of Tax Rate',
            'Rate',
            'Taxable Value',
            'Cess Amount',
            'E-Commerce GSTIN'
        ];
        $sheet->fromArray($headers, null, 'A4');

        // --- STYLING ---

        // Blue header styling
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        // Peach/Orange styling
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        // Apply styles to Row 1
        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:D1')->applyFromArray($orangeStyle);
        $sheet->getStyle('E1:K1')->applyFromArray($blueStyle);
        $sheet->getStyle('D1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'b4c6e7']], // Light blue as seen in image
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);

        // Apply styles to Row 2
        $sheet->getStyle('A2:K2')->applyFromArray($blueStyle);

        // Summary Values Row 3 styling
        $sheet->getStyle('A3:K3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Data Header Styling (Row 4)
        $sheet->getStyle('A4:C4')->applyFromArray($orangeStyle);
        $sheet->getStyle('D4:K4')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'b4c6e7']], // Light blue as seen in image
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(40);
    }

    // tab 6: B2CS
    private function addB2CSSheet($spreadsheet)
    {
        /* ================= BRANCH ID LOGIC ================= */
        $user               = Auth::guard('api')->user();
        $role               = $user->role;
        $userId             = $user->id;
        $userBranchId       = $user->branch_id;
        $selectedSubAdminId = request()->selectedSubAdminId ?? $userId;

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
        $sheet->setTitle('b2cs');

        /* ================= COLUMN WIDTHS (UNCHANGED) ================= */
        $widths = [
            'A' => 20,
            'B' => 25,
            'C' => 15,
            'D' => 10,
            'E' => 20,
            'F' => 15,
            'G' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        /* ================= FETCH BRANCH STATE ================= */
        $branchSetting   = Setting::where('branch_id', $branch_id)->first();
        $branchStateCode = $branchSetting ? $branchSetting->state_code : null;

        /* ================= FETCH UNREGISTERED ORDERS ================= */
        $orders = Order::with(['user', 'orderItems'])
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->whereHas('user', function ($q) {
                $q->whereNull('gst_number')
                    ->orWhere('gst_number', '');
            })
            ->get();

        /* ================= APPLY B2CS RULES ================= */
        $orders = $orders->filter(function ($order) use ($branchStateCode) {

            if (!$order->user || !$order->user->state_code) {
                return false;
            }

            $invoiceValue = (float) $order->total_amount;
            $isIntra      = $branchStateCode === $order->user->state_code;
            $isInter      = $branchStateCode !== $order->user->state_code;

            return $isIntra || ($isInter && $invoiceValue <= 250000);
        });

        /* ================= GROUPING (ONLY NEW LOGIC) ================= */
        $grouped = [];

        foreach ($orders as $order) {

            $posCode = $order->user->state_code;
            $pos     = $posCode . '-' . $this->stateName($posCode);
            $type    = 'OE';

            foreach ($order->orderItems as $item) {

                $gstDetails = is_string($item->product_gst_details)
                    ? json_decode($item->product_gst_details, true)
                    : $item->product_gst_details;

                $rate = 0;
                if (is_array($gstDetails)) {
                    foreach ($gstDetails as $tax) {
                        $rate += (float) ($tax['tax_rate'] ?? 0);
                    }
                }

                $taxable = (float)$item->total_amount - (float)$item->product_gst_total;
                $cess    = (float)($item->cess ?? 0);

                // 🔑 GST B2CS RULE: Type + POS + Rate
                $key = $type . '|' . $pos . '|' . number_format($rate, 2);

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'type'    => $type,
                        'pos'     => $pos,
                        'rate'    => number_format($rate, 2, '.', ''),
                        'taxable' => 0,
                        'cess'    => 0
                    ];
                }

                $grouped[$key]['taxable'] += $taxable;
                $grouped[$key]['cess']    += $cess;
            }
        }

        /* ================= BUILD ROWS ================= */
        $rows = [];
        $totalTaxableValue = 0;
        $totalCess = 0;

        foreach ($grouped as $g) {

            $rows[] = [
                $g['type'],
                $g['pos'],
                '',
                $g['rate'],
                number_format($g['taxable'], 2, '.', ''),
                number_format($g['cess'], 2, '.', ''),
                ''
            ];

            $totalTaxableValue += $g['taxable'];
            $totalCess += $g['cess'];
        }

        /* ================= SUMMARY HEADER (UNCHANGED) ================= */
        $sheet->setCellValue('A1', 'Summary For B2CS(7)');
        $sheet->setCellValue('G1', 'HELP');

        $sheet->setCellValue('E2', 'Total Taxable Value');
        $sheet->setCellValue('F2', 'Total Cess');

        $sheet->setCellValue('E3', number_format($totalTaxableValue, 2, '.', ''));
        $sheet->setCellValue('F3', number_format($totalCess, 2, '.', ''));

        /* ================= TABLE HEADER (UNCHANGED) ================= */
        $headers = [
            'Type',
            'Place Of Supply',
            'Applicable % of Tax Rate',
            'Rate',
            'Taxable Value',
            'Cess Amount',
            'E-Commerce GSTIN'
        ];
        $sheet->fromArray($headers, null, 'A4');

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A5');
        }

        /* ================= STYLING (100% SAME AS YOUR CODE) ================= */
        $blueHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('A2:G2')->applyFromArray($blueHeaderStyle);

        $sheet->getStyle('A3:G3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:G4')->applyFromArray($dataHeaderStyle);

        if (!empty($rows)) {
            $sheet->getStyle('A5:G' . (count($rows) + 4))
                ->applyFromArray(['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]]);
        }

        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(35);
    }

    // tab 7: B2CSA
    private function addB2CSASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('b2csa');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 15,
            'E' => 22,
            'F' => 10,
            'G' => 20,
            'H' => 20,
            'I' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For B2CSA');
        // $sheet->mergeCells('B1:D1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('C1:H1');
        $sheet->setCellValue('C1', 'Revised details');
        $sheet->setCellValue('I1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $lightBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1')->applyFromArray($orangeStyle);
        $sheet->getStyle('C1:H1')->applyFromArray($lightBlueStyle);
        $sheet->getStyle('I1')->applyFromArray($lightBlueStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('G2', 'Total Taxable Value');
        $sheet->setCellValue('H2', 'Total Cess');
        $sheet->getStyle('A2:I2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('G3', '0.00');
        $sheet->setCellValue('H3', '0.00');
        $sheet->getStyle('A2:I3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Financial Year',
            'Original Month',
            'Place Of Supply',
            'Type',
            'Applicable % of Tax Rate',
            'Rate',
            'Taxable Value',
            'Cess Amount',
            'E-Commerce GSTIN'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A4:B4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');
        $sheet->getStyle('C4:I4')->applyFromArray([
            // 'font' => ['name' => 'Times New Roman', 'size'  => 13, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(30);
    }

    // tab 8: cdnr
    private function addCDNRSheet($spreadsheet)
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
        $sheet->setTitle('cdnr');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 25,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 20,
            'G' => 15,
            'H' => 20,
            'I' => 15,
            'J' => 15,
            'K' => 10,
            'L' => 20,
            'M' => 15
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        /* ================= FETCH DATA ================= */
        // 1. Sales Returns (Credit Notes)
        $returns = SalesReturn::with(['order.user', 'items'])
            ->where('branch_id', $branch_id)
            ->whereHas('order.user', function ($q) {
                $q->whereNotNull('gst_number')
                    ->where('gst_number', '!=', '');
            })
            ->get();

        // 1.1 Purchase Returns (Debit Notes)
        // $purchaseReturns = PurchaseReturn::with(['purchase.vendor', 'items'])
        //     ->where('branch_id', $branch_id)
        //     ->whereHas('purchase.vendor', function ($q) {
        //         $q->whereNotNull('gst_number')
        //             ->where('gst_number', '!=', '');
        //     })
        //     ->get();

        // 2. Credit Note Items (Credit Notes)
        // $creditNotes = \App\Models\CreditNoteItem::with(['order.user', 'purchaseInvoice.vendor'])
        //     ->where('branch_id', $branch_id)
        //     ->whereIn('type_id', ['receipt', 'payment'])
        //     ->where('isDeleted', 0)
        //     ->where(function ($query) {
        //         $query->whereHas('order.user', function ($q) {
        //             $q->whereNotNull('gst_number')
        //                 ->where('gst_number', '!=', '');
        //         })->orWhereHas('purchaseInvoice.vendor', function ($q) {
        //             $q->whereNotNull('gst_number')
        //                 ->where('gst_number', '!=', '');
        //         });
        //     })
        //     ->get();

        // ✅ SALES CREDIT NOTES ONLY
        $creditNotes = CreditNoteItem::with(['order.user'])
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0)
            ->whereNotNull('order_id')   // 🔴 IMPORTANT
            ->whereHas('order.user', function ($q) {
                $q->whereNotNull('gst_number')->where('gst_number', '!=', '');
            })
            ->get();



        // 3. Debit Note Items (Debit Notes)
        // $debitNotes = \App\Models\DebitNoteItem::with(['order.user', 'purchaseInvoice.vendor'])
        //     ->where('branch_id', $branch_id)
        //     ->whereIn('transaction_type', ['receipt', 'payment'])
        //     ->where(function ($q) {
        //         $q->where('isDeleted', 0)->orWhereNull('isDeleted');
        //     })
        //     ->where(function ($query) {
        //         $query->whereHas('order.user', function ($q) {
        //             $q->whereNotNull('gst_number')
        //                 ->where('gst_number', '!=', '');
        //         })->orWhereHas('purchaseInvoice.vendor', function ($q) {
        //             $q->whereNotNull('gst_number')
        //                 ->where('gst_number', '!=', '');
        //         });
        //     })
        //     ->get();
        // ✅ SALES DEBIT NOTES ONLY
        $debitNotes = DebitNoteItem::with(['order.user'])
            ->where('branch_id', $branch_id)
            ->where(function ($q) {
                $q->where('isDeleted', 0)->orWhereNull('isDeleted');
            })
            ->whereNotNull('order_id')   // 🔴 IMPORTANT
            ->whereHas('order.user', function ($q) {
                $q->whereNotNull('gst_number')->where('gst_number', '!=', '');
            })
            ->get();

        // dd($debitNotes);

        $rows = [];
        $uniqueRecipients = [];
        $totalNoteValue = 0;
        $totalTaxableValue = 0;
        $totalCess = 0;
        $noteCount = 0;

        // Process Sales Returns
        foreach ($returns as $return) {
            $totalNoteValue += (float)$return->total_amount;
            $uniqueRecipients[$return->order->user->gst_number] = true;
            $noteCount++;

            $itemsByRate = [];
            foreach ($return->items as $item) {
                $gstDetails = is_string($item->product_gst_details) ? json_decode($item->product_gst_details, true) : $item->product_gst_details;
                $rate = 0;
                $cess = 0;
                if (is_array($gstDetails)) {
                    foreach ($gstDetails as $tax) {
                        $rate += (float)($tax['tax_rate'] ?? 0);
                        if (str_contains(strtolower($tax['tax_type'] ?? ''), 'cess')) {
                            $cess += (float)($tax['tax_amount'] ?? 0);
                        }
                    }
                }
                $rateKey = number_format($rate, 2, '.', '');
                if (!isset($itemsByRate[$rateKey])) {
                    $itemsByRate[$rateKey] = ['taxable' => 0, 'cess' => 0];
                }
                $taxable = (float)$item->subtotal;
                $itemsByRate[$rateKey]['taxable'] += $taxable;
                $itemsByRate[$rateKey]['cess'] += $cess;
                $totalTaxableValue += $taxable;
                $totalCess += $cess;
            }

            $pos = $return->order->user->state_code ? $return->order->user->state_code . '-' . $this->stateName($return->order->user->state_code) : '';
            $invoiceType = 'Regular B2B';
            if (!empty($return->order->is_sez)) {
                $invoiceType = $return->order->sez_with_payment ? 'SEZ with payment' : 'SEZ without payment';
            }
            if (!empty($return->order->is_deemed_export)) {
                $invoiceType = 'Deemed Export';
            }

            foreach ($itemsByRate as $rate => $val) {
                $rows[] = [
                    '_sort_date' => Carbon::parse($return->created_at)->timestamp,
                    '_sort_id'   => $return->id,
                    $return->order->user->gst_number,
                    $return->order->user->name,
                    $return->return_number,
                    Carbon::parse($return->created_at)->format('d-M-Y'),
                    'C',
                    $pos,
                    'N',
                    $invoiceType,
                    number_format($return->total_amount, 2, '.', ''),
                    '',
                    $rate,
                    number_format($val['taxable'], 2, '.', ''),
                    number_format($val['cess'], 2, '.', '')
                ];
            }
        }

        // Process Purchase Returns
        // foreach ($purchaseReturns as $pReturn) {
        //     $totalNoteValue += (float)$pReturn->total_amount;
        //     $uniqueRecipients[$pReturn->purchase->vendor->gst_number] = true;
        //     $noteCount++;

        //     $itemsByRate = [];
        //     foreach ($pReturn->items as $item) {
        //         $gstDetails = is_string($item->product_gst_details) ? json_decode($item->product_gst_details, true) : $item->product_gst_details;
        //         $rate = 0;
        //         $cess = 0;
        //         if (is_array($gstDetails)) {
        //             foreach ($gstDetails as $tax) {
        //                 $rate += (float)($tax['tax_rate'] ?? 0);
        //                 if (str_contains(strtolower($tax['tax_type'] ?? ''), 'cess')) {
        //                     $cess += (float)($tax['tax_amount'] ?? 0);
        //                 }
        //             }
        //         }
        //         $rateKey = number_format($rate, 2, '.', '');
        //         if (!isset($itemsByRate[$rateKey])) {
        //             $itemsByRate[$rateKey] = ['taxable' => 0, 'cess' => 0];
        //         }
        //         $taxable = (float)$item->subtotal;
        //         $itemsByRate[$rateKey]['taxable'] += $taxable;
        //         $itemsByRate[$rateKey]['cess'] += $cess;
        //         $totalTaxableValue += $taxable;
        //         $totalCess += $cess;
        //     }

        //     $pos = $pReturn->purchase->vendor->state_code ? $pReturn->purchase->vendor->state_code . '-' . $this->stateName($pReturn->purchase->vendor->state_code) : '';
        //     $invoiceType = 'Regular B2B'; // Assuming Regular B2B for purchase returns as well for GSTR-1 if reported here

        //     foreach ($itemsByRate as $rate => $val) {
        //         $rows[] = [
        //             $pReturn->purchase->vendor->gst_number,
        //             $pReturn->purchase->vendor->name,
        //             $pReturn->return_no,
        //             Carbon::parse($pReturn->created_at)->format('d-M-Y'),
        //             'D',
        //             $pos,
        //             'N',
        //             $invoiceType,
        //             number_format($pReturn->total_amount, 2, '.', ''),
        //             '',
        //             $rate,
        //             number_format($val['taxable'], 2, '.', ''),
        //             number_format($val['cess'], 2, '.', '')
        //         ];
        //     }
        // }

        // Process Credit Note Items
        foreach ($creditNotes as $cn) {
            $settlementAmount = (float)($cn->settlement_amount ?? 0);
            $totalNoteValue += $settlementAmount;
            $noteCount++;

            $customer = $cn->order->user;
            if (!$customer) continue;

            $uniqueRecipients[$customer->gst_number] = true;
            $noteCount++;

            $pos = $customer->state_code ? $customer->state_code . '-' . $this->stateName($customer->state_code) : '';
            $invoiceType = 'Regular B2B';
            if ($cn->order) {
                if (!empty($cn->order->is_sez)) {
                    $invoiceType = $cn->order->sez_with_payment ? 'SEZ with payment' : 'SEZ without payment';
                }
                if (!empty($cn->order->is_deemed_export)) {
                    $invoiceType = 'Deemed Export';
                }
            }

            $noteNumber = (string)($cn->invoice_number ?? '');
            if (empty($noteNumber)) {
                if ($cn->order_id) {
                    $noteNumber = (string)($cn->order->order_number ?? '');
                } elseif ($cn->purchase_id) {
                    $noteNumber = (string)($cn->purchaseInvoice->invoice_number ?? '');
                }
            }

            $rows[] = [
                '_sort_date' => Carbon::parse($cn->created_at)->timestamp,
                '_sort_id'   => $cn->id,
                $customer->gst_number,
                $customer->name,
                $noteNumber, 
                Carbon::parse($cn->created_at)->format('d-M-Y'),
                'C',
                $pos,
                'N',
                $invoiceType,
                number_format($settlementAmount, 2, '.', ''),
                '',
                '0',
                '0.00',
                '0.00'
            ];
        }
        // dd($noteNumbers);

        // Process Debit Note Items
        foreach ($debitNotes as $dn) {
            $settlementAmount = (float)($dn->settlement_amount ?? 0);
            $totalNoteValue += $settlementAmount;
            $customer = $dn->order->user;
            // $customer = $dn->order->user ?? ($dn->purchaseInvoice->vendor ?? null);
            if (!$customer) continue;

            $uniqueRecipients[$customer->gst_number] = true;
            $noteCount++;

            $pos = $customer->state_code ? $customer->state_code . '-' . $this->stateName($customer->state_code) : '';
            $invoiceType = 'Regular B2B';
            if ($dn->order) {
                if (!empty($dn->order->is_sez)) {
                    $invoiceType = $dn->order->sez_with_payment ? 'SEZ with payment' : 'SEZ without payment';
                }
                if (!empty($dn->order->is_deemed_export)) {
                    $invoiceType = 'Deemed Export';
                }
            }

            $noteNumber = (string)($dn->invoice_number ?? '');
            if (empty($noteNumber)) {
                if ($dn->order_id) {
                    $noteNumber = (string)($dn->order->order_number ?? '');
                } elseif ($dn->purchase_id) {
                    $noteNumber = (string)($dn->purchaseInvoice->invoice_number ?? '');
                }
            }

            $rows[] = [
                '_sort_date' => Carbon::parse($dn->created_at)->timestamp,
                '_sort_id'   => $dn->id,
                $customer->gst_number,
                $customer->name,
                $noteNumber,
                Carbon::parse($dn->created_at)->format('d-M-Y'),
                'D',
                $pos,
                'N',
                $invoiceType,
                number_format($settlementAmount, 2, '.', ''),
                '',
                '0',
                '0.00',
                '0.00'
            ];
        }
        usort($rows, function ($a, $b) {
            $aDate = $a['_sort_date'] ?? 0;
            $bDate = $b['_sort_date'] ?? 0;

            if ($aDate === $bDate) {
                return ($a['_sort_id'] ?? 0) <=> ($b['_sort_id'] ?? 0);
            }
            return $aDate <=> $bDate;
        });


        // 🔹 REMOVE INTERNAL SORT KEYS BEFORE EXCEL OUTPUT
        $rows = array_map(function ($row) {
            unset($row['_sort_date'], $row['_sort_id']);
            return array_values($row); // reindex for PhpSpreadsheet
        }, $rows);

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For CDNR(9B)');
        $sheet->setCellValue('M1', 'HELP');

        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1:B1')->applyFromArray($blueStyle);
        $sheet->getStyle('M1')->applyFromArray($blueStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('A2', 'No. of Recipients');
        $sheet->setCellValue('C2', 'No. of Notes');
        $sheet->setCellValue('I2', 'Total Note Value');
        $sheet->setCellValue('L2', 'Total Taxable Value');
        $sheet->setCellValue('M2', 'Total Cess');
        $sheet->getStyle('A2:M2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('A3', count($uniqueRecipients));
        $sheet->setCellValue('C3', $noteCount);
        $sheet->setCellValue('I3', number_format($totalNoteValue, 2, '.', ''));
        $sheet->setCellValue('L3', number_format($totalTaxableValue, 2, '.', ''));
        $sheet->setCellValue('M3', number_format($totalCess, 2, '.', ''));

        $sheet->getStyle('A3:M3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = ['GSTIN/UIN of Recipient', 'Receiver Name', 'Note Number', 'Note Date', 'Note Type', 'Place Of Supply', 'Reverse Charge', 'Note Supply Type', 'Note Value', 'Applicable % of Tax Rate', 'Rate', 'Taxable Value', 'Cess Amount'];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A4:M4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(40);

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A5');

            // Set Note Number column (C) as string to avoid scientific notation
            foreach (range(5, count($rows) + 4) as $rowNum) {
                $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                // Also force it as a string just in case
                $cellValue = $sheet->getCell('C' . $rowNum)->getValue();
                $sheet->getCell('C' . $rowNum)->setValueExplicit($cellValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }

            $sheet->getStyle('A5:M' . (count($rows) + 4))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
        }
    }

    // tab 9: cdnra
    private function addCDNRASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('cdnra');

        // --- COLUMN WIDTHS (A-O) ---
        $widths = [
            'A' => 25,
            'B' => 20,
            'C' => 25,
            'D' => 20,
            'E' => 25,
            'F' => 25,
            'G' => 25,
            'H' => 25,
            'I' => 25,
            'J' => 25,
            'K' => 25,
            'L' => 25,
            'M' => 25,
            'N' => 25,
            'O' => 25
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For CDNRA');
        $sheet->mergeCells('B1:D1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('E1:N1');
        $sheet->setCellValue('E1', 'Revised details');
        $sheet->setCellValue('O1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $lightBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:D1')->applyFromArray($orangeStyle);
        $sheet->getStyle('E1:N1')->applyFromArray($lightBlueStyle);
        $sheet->getStyle('O1')->applyFromArray($lightBlueStyle);

        // --- ROW 2: SUMMARY LABELS ---
        // $sheet->mergeCells('A2:B2');
        $sheet->setCellValue('A2', 'No. of Recipients');
        // $sheet->mergeCells('C2:K2');
        $sheet->setCellValue('C2', 'No. of Notes/Vouchers');
        $sheet->setCellValue('L2', 'Total Note Value');
        $sheet->setCellValue('M2', ''); // Spacer
        $sheet->setCellValue('N2', 'Total Taxable Value');
        $sheet->setCellValue('O2', 'Total Cess');

        $sheet->getStyle('A2:O2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        // $sheet->mergeCells('A3:B3');
        $sheet->setCellValue('A3', '0');
        // $sheet->mergeCells('C3:K3');
        $sheet->setCellValue('C3', '0');
        $sheet->setCellValue('L3', '0.00');
        $sheet->setCellValue('M3', '');
        $sheet->setCellValue('N3', '0.00');
        $sheet->setCellValue('O3', '0.00');

        $sheet->getStyle('A2:O3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'GSTIN/UIN of Recipient',
            'Receiver Name',
            'Original Note Number',
            'Original Note Date',
            'Revised Note Number',
            'Revised Note Date',
            'Note Type',
            'Place Of Supply',
            'Reverse Charge',
            'Note Supply Type',
            'Note Value',
            'Applicable % of Tax Rate',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStylePeach = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A4:D4')->applyFromArray($dataHeaderStylePeach);
        $sheet->getStyle('E4:O4')->applyFromArray($lightBlueStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(40);
    }

    // tab 10: cdnur
    private function addCDNURSheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();
        $role               = $user->role;
        $userId             = $user->id;
        $userBranchId       = $user->branch_id;
        $request            = request();
        $selectedSubAdminId = $request->selectedSubAdminId ?? $userId;

        // ----------------------------
        // Branch Resolution
        // ----------------------------
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

        // ----------------------------
        // Seller State
        // ----------------------------
        $setting     = Setting::where('branch_id', $branch_id)->first();
        $sellerState = $setting->state_code ?? null;

        // ----------------------------
        // Sheet Design
        // ----------------------------
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('cdnur');

        $widths = [
            'A' => 25,
            'B' => 25,
            'C' => 20,
            'D' => 12,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 10,
            'I' => 20,
            'J' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Row 1: Top header
        $sheet->setCellValue('A1', 'Summary For CDNUR(9B)');
        $sheet->setCellValue('J1', 'HELP');

        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($blueStyle);

        // Row 2: Summary Labels
        $sheet->setCellValue('B2', 'No. of Notes/Vouchers');
        $sheet->setCellValue('F2', 'Total Note Value');
        $sheet->setCellValue('I2', 'Total Taxable Value');
        $sheet->setCellValue('J2', 'Total Cess');
        $sheet->getStyle('A2:J2')->applyFromArray($blueStyle);

        // Row 3: Summary Values (init)
        $sheet->setCellValue('B3', '0');
        $sheet->setCellValue('F3', '0.00');
        $sheet->setCellValue('I3', '0.00');
        $sheet->setCellValue('J3', '0.00');
        $sheet->getStyle('A3:J3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Row 4: Data headers
        $headers = [
            'UR Type',
            'Note Number',
            'Note Date',
            'Note Type',
            'Place Of Supply',
            'Note Value',
            'Applicable % of Tax Rate',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
         $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:J4')->applyFromArray([
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
        ]);
       
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(40);

        $returnPeriod = $request->return_period ?? now()->format('Y-m');
        $limit = ($returnPeriod >= '2024-08') ? 100000 : 250000;

        // $row = 5;
        $rows = [];
        $totalNotes = 0;
        $totalNoteValue = 0;
        $totalTaxable = 0;
        $totalCess = 0;

        // ----------------------------
        // 1️⃣ Sales Returns
        // ----------------------------
        $salesReturns = SalesReturn::with(['order.user', 'items'])
            ->where('branch_id', $branch_id)
            ->whereHas('order.user', function ($q) use ($sellerState) {
                $q->where(function ($query) {
                    $query->whereNull('gst_number')->orWhere('gst_number', '');
                })->where('state_code', '!=', $sellerState);
            })
            ->get();


        foreach ($salesReturns as $note) {
            // Apply threshold limit based on total invoice value
            $noteValue = (float)$note->total_amount;
            if ($noteValue <= $limit) continue;
            $totalNotes++;
            $totalNoteValue += (float)$note->total_amount;

            $itemsByRate = [];
            foreach ($note->items as $item) {
                $gstDetails = is_string($item->product_gst_details) ? json_decode($item->product_gst_details, true) : $item->product_gst_details;
                $rate = 0;
                $itemCess = 0;
                if (is_array($gstDetails)) {
                    foreach ($gstDetails as $tax) {
                        $rate += (float)($tax['tax_rate'] ?? 0);
                        if (str_contains(strtolower($tax['tax_type'] ?? ''), 'cess')) {
                            $itemCess += (float)($tax['tax_amount'] ?? 0);
                        }
                    }
                }
                $rateKey = number_format($rate, 2, '.', '');
                if (!isset($itemsByRate[$rateKey])) {
                    $itemsByRate[$rateKey] = ['taxable' => 0, 'cess' => 0];
                }
                $itemsByRate[$rateKey]['taxable'] += (float)$item->subtotal;
                $itemsByRate[$rateKey]['cess'] += $itemCess;
                $totalTaxable += (float)$item->subtotal;
                $totalCess += $itemCess;
            }

            foreach ($itemsByRate as $rate => $val) {
                $rows[] = [
                    '_sort_date' => Carbon::parse($note->created_at)->timestamp,
                    '_sort_id'   => $note->id,

                    'B2CL',
                    // $note->return_number . ' (Inv: ' . ($note->order->order_number ?? '') . ')',
                    $note->return_number ,
                    Carbon::parse($note->created_at)->format('d-M-Y'),
                    'C',
                    $note->order->user->state_code . '-' . $this->stateName($note->order->user->state_code),
                    number_format($note->total_amount, 2, '.', ''),
                    '',
                    $rate,
                    number_format($val['taxable'], 2, '.', ''),
                    number_format($val['cess'], 2, '.', '')
                ];
            }
        }

        // foreach ($purchaseReturns as $pReturn) {
        //     $invoiceValue = (float)($pReturn->purchase->total_amount ?? 0);
        //     if ($invoiceValue <= $limit) continue;

        //     $totalNotes++;
        //     $totalNoteValue += (float)$pReturn->total_amount;

        //     $itemsByRate = [];
        //     foreach ($pReturn->items as $item) {
        //         $gstDetails = is_string($item->product_gst_details) ? json_decode($item->product_gst_details, true) : $item->product_gst_details;
        //         $rate = 0;
        //         $itemCess = 0;
        //         if (is_array($gstDetails)) {
        //             foreach ($gstDetails as $tax) {
        //                 $rate += (float)($tax['tax_rate'] ?? 0);
        //                 if (str_contains(strtolower($tax['tax_type'] ?? ''), 'cess')) {
        //                     $itemCess += (float)($tax['tax_amount'] ?? 0);
        //                 }
        //             }
        //         }
        //         $rateKey = number_format($rate, 2, '.', '');
        //         if (!isset($itemsByRate[$rateKey])) {
        //             $itemsByRate[$rateKey] = ['taxable' => 0, 'cess' => 0];
        //         }
        //         $itemsByRate[$rateKey]['taxable'] += (float)$item->subtotal;
        //         $itemsByRate[$rateKey]['cess'] += $itemCess;
        //         $totalTaxable += (float)$item->subtotal;
        //         $totalCess += $itemCess;
        //     }

        //     foreach ($itemsByRate as $rate => $val) {
        //         $sheet->setCellValue('A' . $row, 'B2CL');
        //         $sheet->setCellValue('B' . $row, $pReturn->return_no . ' (Inv: ' . ($pReturn->purchase->invoice_number ?? '') . ')');
        //         $sheet->setCellValue('C' . $row, Carbon::parse($pReturn->created_at)->format('d-M-Y'));
        //         $sheet->setCellValue('D' . $row, 'D');
        //         $sheet->setCellValue('E' . $row, $pReturn->purchase->vendor->state_code . '-' . $this->stateName($pReturn->purchase->vendor->state_code));
        //         $sheet->setCellValue('F' . $row, number_format($pReturn->total_amount, 2, '.', ''));
        //         $sheet->setCellValue('G' . $row, '');
        //         $sheet->setCellValue('H' . $row, $rate);
        //         $sheet->setCellValue('I' . $row, number_format($val['taxable'], 2, '.', ''));
        //         $sheet->setCellValue('J' . $row, number_format($val['cess'], 2, '.', ''));
        //         $row++;
        //     }
        // }

        // ----------------------------
        // 2️⃣ Credit Notes
        // ----------------------------
        $creditNotes = CreditNoteItem::with(['order.user', 'purchaseInvoice'])
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0)
            ->whereHas('order.user', function ($q) use ($sellerState) {
                $q->where(function ($query) {
                    $query->whereNull('gst_number')->orWhere('gst_number', '');
                })->where('state_code', '!=', $sellerState);
            })
            ->get();
            // dd($creditNotes);

        foreach ($creditNotes as $note) {
            $noteValue = (float)$note->settlement_amount;
            if ($noteValue <= $limit) continue;

            $noteNum = $note->order_id ? ($note->order->order_number ?? '') : ($note->purchaseInvoice->invoice_number ?? '');

            $rows[] = [
                '_sort_date' => Carbon::parse($note->created_at)->timestamp,
                '_sort_id'   => $note->id,

                'B2CL',
                " " . $noteNum,
                Carbon::parse($note->created_at)->format('d-M-Y'),
                'C',
                $note->order->user->state_code . '-' . $this->stateName($note->order->user->state_code),
                number_format($note->settlement_amount, 2, '.', ''),
                '',
                '0',
                number_format($note->settlement_amount, 2, '.', ''),
                '0.00'
            ];
        }

        // ----------------------------
        // 3️⃣ Debit Notes
        // ----------------------------
        $debitNotes = DebitNoteItem::with(['order.user', 'purchaseInvoice'])
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0)
            ->whereHas('order.user', function ($q) use ($sellerState) {
                $q->where(function ($query) {
                    $query->whereNull('gst_number')->orWhere('gst_number', '');
                })->where('state_code', '!=', $sellerState);
            })
            ->get();

        foreach ($debitNotes as $note) {
            $noteValue = (float)$note->settlement_amount;
            if ($noteValue <= $limit) continue;

            $noteNum = $note->order_id ? ($note->order->order_number ?? '') : ($note->purchaseInvoice->invoice_number ?? '');

            $rows[] = [
                '_sort_date' => Carbon::parse($note->created_at)->timestamp,
                '_sort_id'   => $note->id,

                'B2CL',
                " " . $noteNum,
                Carbon::parse($note->created_at)->format('d-M-Y'),
                'D',
                $note->order->user->state_code . '-' . $this->stateName($note->order->user->state_code),
                number_format($note->settlement_amount, 2, '.', ''),
                '',
                '0',
                number_format($note->settlement_amount, 2, '.', ''),
                '0.00'
            ];
        }
        usort($rows, function ($a, $b) {
            if ($a['_sort_date'] === $b['_sort_date']) {
                return $a['_sort_id'] <=> $b['_sort_id'];
            }
            return $a['_sort_date'] <=> $b['_sort_date'];
        });

        $rows = array_map(function ($row) {
            unset($row['_sort_date'], $row['_sort_id']);
            return array_values($row);
        }, $rows);
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A5');

            $sheet->getStyle('A5:J' . (count($rows) + 4))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
        }


        // ----------------------------
        // Update Summary row
        // ----------------------------
        $sheet->setCellValue('B3', $totalNotes);
        $sheet->setCellValue('F3', number_format($totalNoteValue, 2, '.', ''));
        $sheet->setCellValue('I3', number_format($totalTaxable, 2, '.', ''));
        $sheet->setCellValue('J3', number_format($totalCess, 2, '.', ''));
    }

    // tab 11: cdnura
    private function addCDNURASheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('cdnura');

        // --- COLUMN WIDTHS (A-L) ---
        $widths = [
            'A' => 20,
            'B' => 22,
            'C' => 20,
            'D' => 22,
            'E' => 20,
            'F' => 15,
            'G' => 20,
            'H' => 15,
            'I' => 25,
            'J' => 10,
            'K' => 20,
            'L' => 15
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For CDNURA');
        $sheet->mergeCells('B1:C1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('E1:K1');
        $sheet->setCellValue('E1', 'Revised details');
        $sheet->setCellValue('L1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $lightBlueStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:C1')->applyFromArray($orangeStyle);
        $sheet->getStyle('D1:L1')->applyFromArray($lightBlueStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('B2', 'No. of Notes/Vouchers');
        $sheet->setCellValue('H2', 'Total Note Value');
        $sheet->setCellValue('K2', 'Total Taxable Value');
        $sheet->setCellValue('L2', 'Total Cess');

        $sheet->getStyle('A2:L2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('B3', '0');
        $sheet->setCellValue('H3', '0.00');
        $sheet->setCellValue('K3', '0.00');
        $sheet->setCellValue('L3', '0.00');

        $sheet->getStyle('A2:L3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'UR Type',
            'Original Note Number',
            'Original Note Date',
            'Revised Note Number',
            'Revised Note Date',
            'Note Type',
            'Place Of Supply',
            'Note Value',
            'Applicable % of Tax Rate',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStylePeach = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A4:C4')->applyFromArray($dataHeaderStylePeach);
        $sheet->getStyle('D4:L4')->applyFromArray($lightBlueStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(40);
    }

    // tab 12: exp
    private function addEXPSheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();
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
        $sheet->setTitle('exp');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 20,
            'B' => 20,
            'C' => 15,
            'D' => 20,
            'E' => 12,
            'F' => 20,
            'G' => 20,
            'H' => 10,
            'I' => 15,
            'J' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Fetch Export Data
        // Filter by orders that have items with IGST in product_gst_details
        $orders = Order::with(['user.userDetail', 'orderItems'])
            ->where('isDeleted', 0)
            ->where('gst_option', 'with_gst')
            ->where('branch_id', $branch_id)
            ->whereHas('orderItems', function ($query) {
                $query->where('product_gst_details', 'LIKE', '%IGST%');
            })
            ->get();

        $rows               = [];
        $totalInvoices      = 0;
        $totalInvoiceValue  = 0;
        $totalTaxableValue  = 0;
        $totalShippingBills = 0;

        foreach ($orders as $order) {
            $itemsByRate            = [];
            $orderTotalTaxableValue = 0;
            $hasIgstItem            = false;

            foreach ($order->orderItems as $item) {
                $gstDetails = is_string($item->product_gst_details)
                    ? json_decode($item->product_gst_details, true)
                    : $item->product_gst_details;

                $isIgst        = false;
                $totalItemRate = 0;
                if (is_array($gstDetails)) {
                    foreach ($gstDetails as $tax) {
                        $totalItemRate += (float) ($tax['tax_rate'] ?? 0);
                        if (isset($tax['tax_name']) && strtoupper($tax['tax_name']) === 'IGST') {
                            $isIgst = true;
                        }
                    }
                }

                // Only process items that have IGST
                if ($isIgst) {
                    $hasIgstItem = true;
                    $rateKey     = number_format($totalItemRate, 2, '.', '');
                    if (! isset($itemsByRate[$rateKey])) {
                        $itemsByRate[$rateKey] = ['taxable_value' => 0, 'cess' => 0];
                    }

                    $taxableValue                            = (float) $item->total_amount - (float) $item->product_gst_total;
                    $itemsByRate[$rateKey]['taxable_value'] += $taxableValue;
                    $orderTotalTaxableValue                 += $taxableValue;
                }
            }

            if ($hasIgstItem) {
                $totalInvoices++;
                $totalInvoiceValue += (float) $order->total_amount;
                $totalTaxableValue += $orderTotalTaxableValue;

                if (isset($order->shipping_bill_number) && ! empty($order->shipping_bill_number)) {
                    $totalShippingBills++;
                }

                foreach ($itemsByRate as $rate => $values) {
                    $rows[] = [
                        'WOP', // Export Type: Without Payment of Tax
                        $order->order_number,
                        $order->created_at->format('d-M-Y'),
                        number_format((float) $order->total_amount, 2, '.', ''),
                        $order->port_code ?? '-',
                        $order->shipping_bill_number ?? '-',
                        $order->shipping_bill_date ? date('d-M-Y', strtotime($order->shipping_bill_date)) : '',
                        $rate . '%',
                        number_format($values['taxable_value'], 2, '.', ''),
                        number_format($values['cess'], 2, '.', ''),
                    ];
                }
            }
        }

        // --- ROW 1: TOP HEADERS ---
        // $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'Summary For EXP(6)');
        $sheet->setCellValue('J1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);

        // --- ROW 2: SUMMARY LABELS ---
        // $sheet->mergeCells('B2:C2');
        $sheet->setCellValue('B2', 'No. of Invoices');
        // $sheet->mergeCells('D2:E2');
        $sheet->setCellValue('D2', 'Total Invoice Value');
        // $sheet->mergeCells('F2:G2');
        $sheet->setCellValue('F2', 'No. of Shipping Bill');
        // $sheet->setCellValue('I2', 'Total Taxable Value');
        $sheet->setCellValue('J2', 'Total Taxable Value');

        $sheet->getStyle('A2:J2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        // $sheet->mergeCells('B3:C3');
        $sheet->setCellValue('B3', '0');
        // $sheet->mergeCells('D3:E3');
        $sheet->setCellValue('D3', '0.00');
        // $sheet->mergeCells('F3:G3');
        $sheet->setCellValue('F3', '0');
        $sheet->setCellValue('I3', '0.00');
        $sheet->setCellValue('J3', '0.00');

        $sheet->getStyle('A2:J3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Export Type',
            'Invoice Number',
            'Invoice date',
            'Invoice Value',
            'Port Code',
            'Shipping Bill Number',
            'Shipping Bill Date',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
        ];
        $sheet->getStyle('A4:J4')->applyFromArray($dataHeaderStyle);

        if (! empty($rows)) {
            $sheet->fromArray($rows, null, 'A5');
        }

        $sheet->freezePane('A5');
        // $sheet->getRowDimension(4)->setRowHeight();
    }

    // Tab 13: expa
    private function addEXPASheet($spreadsheet)
    {


        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('expa');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 20,
            'B' => 25,
            'C' => 25,
            'D' => 25,
            'E' => 25,
            'F' => 22,
            'G' => 15,
            'H' => 25,
            'I' => 20,
            'J' => 10,
            'K' => 22,
            'L' => 15
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->mergeCells('A1');
        $sheet->setCellValue('A1', 'Summary For EXPA');
        $sheet->mergeCells('B1:C1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('D1:K1');
        $sheet->setCellValue('D1', 'Revised details');
        $sheet->setCellValue('L1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $skyblueStyle = [
            'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
        ];

        $orangeStyle = [
            'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:C1')->applyFromArray($orangeStyle);
        $sheet->getStyle('D1:K1')->applyFromArray($skyblueStyle);
        $sheet->getStyle('L1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('B2', 'No. of Invoices');
        $sheet->setCellValue('F2', 'Total Invoice Value');
        $sheet->setCellValue('H2', 'No. of Shipping Bill');
        $sheet->setCellValue('K2', 'Total Taxable Value');
        $sheet->setCellValue('L2', 'Total Cess');
        $sheet->getStyle('A2:L2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('A3', '0');
        $sheet->setCellValue('B3', '0.00');
        $sheet->setCellValue('D3', '0');
        $sheet->setCellValue('G3', '0.00');
        $sheet->setCellValue('K3', '0.00');

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:L3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Export Type',
            'Original Invoice Number',
            'Original Invoice date',
            'Revised Invoice Number',
            'Revised Invoice date',
            'Invoice Value',
            'Port Code',
            'Shipping Bill Number',
            'Shipping Bill Date',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeader_orangeStyle = [
            'font'      => ['name' => 'Times New Roman'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'wrapText' => true
            ]
        ];
        $dataHeader_skyblueStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:C4')->applyFromArray($dataHeader_orangeStyle);
        $sheet->getStyle('D4:L4')->applyFromArray($dataHeader_skyblueStyle);
        $sheet->freezePane('A5');
    }

    // Tab 14: at
    private function addatSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('at');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 23,
            'B' => 15,
            'C' => 15,
            'D' => 25,
            'E' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Advance Received (11B)');
        $sheet->setCellValue('E1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('E1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('D2', 'Total Advance Received');
        $sheet->setCellValue('E2', 'Total Cess');
        $sheet->getStyle('A2:E2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('D3', '0.00');
        $sheet->setCellValue('E3', '0.00');

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:E3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Place Of Supply',
            'Applicable % of Tax Rate',
            'Rate',
            'Gross Advance Received',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:E4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');

        $sheet->getRowDimension(4)->setRowHeight(35);
    }

    // Tab 15
    private function addataSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ata');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 25,
            'B' => 20,
            'C' => 28,
            'D' => 18,
            'E' => 15,
            'F' => 25,
            'G' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Amended Tax Liability(Advance Received)');
        $sheet->mergeCells('B1:C1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('D1:F1');
        $sheet->setCellValue('D1', 'Revised details');
        $sheet->setCellValue('G1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $skyblueStyle = [
            'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
        ];
        $whiteStyle = [
            'font' => ['size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font'      => ['name' => 'Times New Roman', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:C1')->applyFromArray($orangeStyle);
        $sheet->getStyle('D1:F1')->applyFromArray($skyblueStyle);
        $sheet->getStyle('G1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('F2', 'Total Advance Received');
        $sheet->setCellValue('G2', 'Total Cess');
        $sheet->getStyle('A2:G2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('F3', '0.00');
        $sheet->setCellValue('G3', '0.00');

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:G3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Financial Year',
            'Original Month',
            'Original Place Of Supply',
            'Applicable % of Tax Rate',
            'Rate',
            'Gross Advance Received',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeader_orangeStyle = [
            'font'      => ['name' => 'Times New Roman'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'wrapText' => true
            ]
        ];
        $dataHeader_skyblueStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:C4')->applyFromArray($dataHeader_orangeStyle);
        $sheet->getStyle('D4:G4')->applyFromArray($dataHeader_skyblueStyle);
        $sheet->freezePane('A5');

        $sheet->getRowDimension(4)->setRowHeight(40);
    }

    // Tab 16 
    private function addatadjSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('atadj');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 25,
            'B' => 15,
            'C' => 15,
            'D' => 25,
            'E' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Advance Adjusted (11B)');
        $sheet->setCellValue('E1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('E1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('D2', 'Total Advance Adjusted');
        $sheet->setCellValue('E2', 'Total Cess');
        $sheet->getStyle('A2:E2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('D3', '0.00');
        $sheet->setCellValue('E3', '0.00');

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:E3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Place Of Supply',
            'Applicable % of Tax Rate',
            'Rate',
            'Gross Advance Adjusted',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:E4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');

        $sheet->getRowDimension(4)->setRowHeight(35);
    }

    // Tab 17
    private function addatadjaSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('atadja');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 28,
            'B' => 20,
            'C' => 30,
            'D' => 25,
            'E' => 15,
            'F' => 25,
            'G' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->mergeCells('A1');
        $sheet->setCellValue('A1', 'Summary For Amendement Of Adjustment Advances');
        $sheet->mergeCells('B1:C1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('D1:F1');
        $sheet->setCellValue('D1', 'Revised details');
        $sheet->setCellValue('G1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
        ];
        $skyblueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
        ];
        $whiteStyle = [
            'font' => ['size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:C1')->applyFromArray($orangeStyle);
        $sheet->getStyle('D1:F1')->applyFromArray($skyblueStyle);
        $sheet->getStyle('G1')->applyFromArray($whiteStyle);

        $sheet->getRowDimension(1)->setRowHeight(40);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->mergeCells('F2:F2');
        $sheet->setCellValue('F2', 'Total Advance Adjusted');
        $sheet->setCellValue('G2', 'Total Cess');
        $sheet->getStyle('A2:G2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('F3', '0.00');
        $sheet->setCellValue('G3', '0.00');

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:G3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Financial Year',
            'Original Month',
            'Original Place Of Supply',
            'Applicable % of Tax Rate',
            'Rate',
            'Gross Advance Adjusted',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeader_orangeStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'wrapText' => true
            ]
        ];
        $dataHeader_skyblueStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'wrapText' => true
            ]
        ];
        $sheet->getRowDimension(4)->setRowHeight(30);
        $sheet->getStyle('A4:C4')->applyFromArray($dataHeader_orangeStyle);
        $sheet->getStyle('D4:G4')->applyFromArray($dataHeader_skyblueStyle);
        $sheet->freezePane('A5');
    }

    // Tab 18
    private function addexempSheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();
        $role               = $user->role;
        $userId             = $user->id;
        $userBranchId       = $user->branch_id;
        $selectedSubAdminId = request()->selectedSubAdminId ?? $userId;
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
        $sheet->setTitle('exemp');

        // ---------------- COLUMN WIDTH ----------------

        $widths = [
            'A' => 45,
            'B' => 25,
            'C' => 35,
            'D' => 25
        ];

        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ---------------- STYLES ----------------

        $blueStyle = [
            'font' => [
                'name' => 'Times New Roman',
                'size' => 11,
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0070C0']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];

        $whiteLinkStyle = [
            'font' => [
                'name' => 'Times New Roman',
                'size' => 11,
                'underline' => Font::UNDERLINE_SINGLE,
                'color' => ['rgb' => '2D63C5']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];

        $headerStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F7CAAC']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];

        $bodyStyle = [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];

        // ---------------- TOP HEADER ----------------

        $sheet->setCellValue('A1', 'Summary For Nil rated, exempted and non GST outward supplies (8)');
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('D1', 'HELP');

        $sheet->getStyle('A1:C1')->applyFromArray($blueStyle);
        $sheet->getStyle('D1')->applyFromArray($whiteLinkStyle);

        // ---------------- SUMMARY HEADER ----------------

        $sheet->setCellValue('B2', 'Total Nil Rated Supplies');
        $sheet->setCellValue('C2', 'Total Exempted Supplies');
        $sheet->setCellValue('D2', 'Total Non-GST Supplies');

        $sheet->getStyle('A2:D2')->applyFromArray($blueStyle);

        // ---------------- FETCH DATA ----------------

        $orders = Order::with(['user', 'orderItems'])
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->get();
        // dd($orders);
        $businessStateCode = '24';

        // ---------------- DATA BUCKETS ----------------

        $exempData = [
            'nil' => [
                'inter_reg' => 0,
                'intra_reg' => 0,
                'inter_unreg' => 0,
                'intra_unreg' => 0,
            ],
            'exempt' => [
                'inter_reg' => 0,
                'intra_reg' => 0,
                'inter_unreg' => 0,
                'intra_unreg' => 0,
            ],
            'nongst' => [
                'inter_reg' => 0,
                'intra_reg' => 0,
                'inter_unreg' => 0,
                'intra_unreg' => 0,
            ],
        ];

        // ---------------- PROCESS ORDERS ----------------

        foreach ($orders as $order) {

            $isRegistered = (!empty($order->user->gst_number));

            $customerStateCode = substr($order->user->state_code ?? '', 0, 2);

            $isInterState = ($customerStateCode !== $businessStateCode);


            $bucketKey = ($isInterState ? 'inter' : 'intra') . '_' . ($isRegistered ? 'reg' : 'unreg');

            foreach ($order->orderItems as $item) {

                $value = (float) $item->total_amount;

                if (is_array($item->product_gst_details)) {

                    $gstDetails = $item->product_gst_details;
                } else {

                    $gstDetails = json_decode($item->product_gst_details, true);
                }


                // -------- NON GST --------
                if (empty($gstDetails)) {

                    $exempData['nongst'][$bucketKey] += $value;
                    continue;
                }

                // -------- GST RATE CALC --------
                $gstRate = 0;

                foreach ($gstDetails as $row) {
                    $gstRate += (float) ($row['tax_rate'] ?? 0);
                }

                // -------- NIL RATED --------
                if ($gstRate == 0) {

                    $exempData['nil'][$bucketKey] += $value;
                }

                // taxable items ignored automatically
            }
        }

        // ---------------- SUMMARY TOTALS ----------------

        $totalNil     = array_sum($exempData['nil']);
        $totalExempt  = array_sum($exempData['exempt']);
        $totalNonGst  = array_sum($exempData['nongst']);

        $sheet->setCellValue('B3', number_format($totalNil, 2, '.', ''));
        $sheet->setCellValue('C3', number_format($totalExempt, 2, '.', ''));
        $sheet->setCellValue('D3', number_format($totalNonGst, 2, '.', ''));

        $sheet->getStyle('A3:D3')->applyFromArray($bodyStyle);

        // ---------------- TABLE HEADER ----------------

        $headers = [
            'Description',
            'Nil Rated Supplies',
            'Exempted(other than nil rated/non GST supply)',
            'Non-GST Supplies'
        ];

        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:D4')->applyFromArray($headerStyle);

        $sheet->freezePane('A5');

        // ---------------- TABLE DATA ----------------

        $data = [
            [
                'Inter-State supplies to registered persons',
                number_format($exempData['nil']['inter_reg'], 2, '.', ''),
                number_format($exempData['exempt']['inter_reg'], 2, '.', ''),
                number_format($exempData['nongst']['inter_reg'], 2, '.', '')
            ],
            [
                'Intra-State supplies to registered persons',
                number_format($exempData['nil']['intra_reg'], 2, '.', ''),
                number_format($exempData['exempt']['intra_reg'], 2, '.', ''),
                number_format($exempData['nongst']['intra_reg'], 2, '.', '')
            ],
            [
                'Inter-State supplies to unregistered persons',
                number_format($exempData['nil']['inter_unreg'], 2, '.', ''),
                number_format($exempData['exempt']['inter_unreg'], 2, '.', ''),
                number_format($exempData['nongst']['inter_unreg'], 2, '.', '')
            ],
            [
                'Intra-State supplies to unregistered persons',
                number_format($exempData['nil']['intra_unreg'], 2, '.', ''),
                number_format($exempData['exempt']['intra_unreg'], 2, '.', ''),
                number_format($exempData['nongst']['intra_unreg'], 2, '.', '')
            ],
        ];

        $sheet->fromArray($data, null, 'A5');

        $lastRow = count($data) + 4;

        $sheet->getStyle("A5:D{$lastRow}")->applyFromArray($bodyStyle);

        $sheet->getRowDimension(4)->setRowHeight(22);
    }



    // Tab 19 hsnb2b
    private function addhsnb2bSheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();
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
        $sheet->setTitle('hsn(b2b)');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 25,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 10,
            'G' => 25,
            'H' => 25,
            'I' => 20,
            'J' => 20,
            'K' => 15
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For HSN(12)');
        $sheet->setCellValue('K1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('K1')->applyFromArray($whiteStyle);

        // Fetch dynamic data for HSN (B2B)
        // $hsnB2BItems = OrderItem::with(['product', 'order.user', 'user'])
        //     ->where('isDeleted', 0)
        //     ->whereHas('user', function ($q) {
        //         $q->whereNotNull('gst_number')->where('gst_number', '!=', '');
        //     })
        //     ->whereNotNull('product_gst_details')
        //     ->where('product_gst_details', '!=', '[]')
        //     ->where('product_gst_details', '!=', '')
        //     ->get();

        $hsnB2BItems = OrderItem::with(['product', 'order.user', 'user'])
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->whereHas('user', function ($q) {
                $q->whereNotNull('gst_number')
                    ->where('gst_number', '!=', '');
            })
            ->get();



        $hsnB2BData = $hsnB2BItems->groupBy(function ($item) {
            // Calculate the total tax rate for grouping
            $gstDetails = is_string($item->product_gst_details)
                ? json_decode($item->product_gst_details, true)
                : $item->product_gst_details;
            $totalRate = 0;
            if (is_array($gstDetails)) {
                foreach ($gstDetails as $tax) {
                    $totalRate += (float)($tax['tax_rate'] ?? 0);
                }
            }
            // Group by HSN Code and Rate
            $hsnCode = $item->product->hsn_code ?? 'N/A';
            return $hsnCode . '_' . number_format($totalRate, 2, '.', '');
        });

        $rows = [];
        $summary = [
            'total_value' => 0,
            'total_taxable' => 0,
            'total_integrated' => 0,
            'total_central' => 0,
            'total_state' => 0,
            'total_cess' => 0
        ];

        foreach ($hsnB2BData as $groupKey => $items) {
            $firstItem = $items->first();
            $hsn = $firstItem->product->hsn_code ?? 'N/A';
            $description = $firstItem->product->description ?? ($firstItem->product->name ?? '');
            $uqc = 'OTH-OTHERS'; // Default UQC

            $totalQty = $items->sum('quantity');
            $totalGroupValue = $items->sum('total_amount');

            $taxableValue = 0;
            $igst = 0;
            $cgst = 0;
            $sgst = 0;

            foreach ($items as $item) {
                $taxableValue += (float)$item->total_amount - (float)$item->product_gst_total;

                $gstDetails = is_string($item->product_gst_details)
                    ? json_decode($item->product_gst_details, true)
                    : $item->product_gst_details;

                if (is_array($gstDetails)) {
                    foreach ($gstDetails as $tax) {
                        $name = strtolower($tax['tax_name'] ?? $tax['tax_type'] ?? '');
                        $amt = (float)($tax['tax_amount'] ?? 0);
                        if (str_contains($name, 'igst')) $igst += $amt;
                        elseif (str_contains($name, 'cgst')) $cgst += $amt;
                        elseif (str_contains($name, 'sgst') || str_contains($name, 'utgst')) $sgst += $amt;
                    }
                }
            }

            $rate = explode('_', $groupKey)[1];

            $rows[] = [
                $hsn,
                $description,
                $uqc,
                number_format($totalQty, 2, '.', ''),
                number_format($totalGroupValue, 2, '.', ''),
                $rate,
                number_format($taxableValue, 2, '.', ''),
                number_format($igst, 2, '.', ''),
                number_format($cgst, 2, '.', ''),
                number_format($sgst, 2, '.', ''),
                '0.00' // Cess
            ];

            // Add to summary
            $summary['total_value'] += $totalGroupValue;
            $summary['total_taxable'] += $taxableValue;
            $summary['total_integrated'] += $igst;
            $summary['total_central'] += $cgst;
            $summary['total_state'] += $sgst;
        }

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('A2', 'No. of HSN');
        $sheet->setCellValue('E2', 'Total Value');
        $sheet->setCellValue('G2', 'Total Taxable Value');
        $sheet->setCellValue('H2', 'Total Integrated Tax');
        $sheet->setCellValue('I2', 'Total Central Tax');
        $sheet->setCellValue('J2', 'Total State/UT Tax');
        $sheet->setCellValue('K2', 'Total Cess');
        $sheet->getStyle('A2:K2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('A3', count($rows));
        $sheet->setCellValue('E3', number_format($summary['total_value'], 2, '.', ''));
        $sheet->setCellValue('G3', number_format($summary['total_taxable'], 2, '.', ''));
        $sheet->setCellValue('H3', number_format($summary['total_integrated'], 2, '.', ''));
        $sheet->setCellValue('I3', number_format($summary['total_central'], 2, '.', ''));
        $sheet->setCellValue('J3', number_format($summary['total_state'], 2, '.', ''));
        $sheet->setCellValue('K3', number_format($summary['total_cess'], 2, '.', ''));

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:K3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'HSN',
            'Description',
            'UQC',
            'Total Quantity',
            'Total Value',
            'Rate',
            'Taxable Value',
            'Integrated Tax Amount',
            'Central Tax Amount',
            'State/UT Tax Amount',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:K4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');

        // Dynamic Data Row 5+
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A5');
            $sheet->getStyle('A5:K' . (count($rows) + 4))->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);
        }

        $sheet->getRowDimension(4)->setRowHeight(20);
    }

    // Tab 20 hsn b2c
    private function addhsnb2cSheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();
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
        $sheet->setTitle('hsn(b2c)');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 25,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 10,
            'G' => 20,
            'H' => 25,
            'I' => 25,
            'J' => 25,
            'K' => 18
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For HSN(12)');
        $sheet->setCellValue('K1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('K1')->applyFromArray($whiteStyle);

        // Fetch dynamic data for HSN (B2C) - Users WITHOUT GST
        // $hsnB2CItems = OrderItem::with(['product', 'order.user', 'user'])
        //     ->where('isDeleted', 0)
        //     ->where(function ($query) {
        //         $query->whereHas('user', function ($q) {
        //             $q->whereNull('gst_number')->orWhere('gst_number', '');
        //         })->orWhereDoesntHave('user');
        //     })
        //     ->whereNotNull('product_gst_details')
        //     ->where('product_gst_details', '!=', '[]')
        //     ->where('product_gst_details', '!=', '')
        //     ->get();

        $hsnB2CItems = OrderItem::with(['product', 'order.user', 'user'])
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->whereNull('gst_number')
                        ->orWhere('gst_number', '');
                })->orWhereDoesntHave('user');
            })
            ->get();



        $hsnB2CData = $hsnB2CItems->groupBy(function ($item) {
            $gstDetails = is_string($item->product_gst_details)
                ? json_decode($item->product_gst_details, true)
                : $item->product_gst_details;
            $totalRate = 0;
            if (is_array($gstDetails)) {
                foreach ($gstDetails as $tax) {
                    $totalRate += (float)($tax['tax_rate'] ?? 0);
                }
            }
            $hsnCode = $item->product->hsn_code ?? 'N/A';
            return $hsnCode . '_' . number_format($totalRate, 2, '.', '');
        });

        $rows = [];
        $summary = [
            'total_value' => 0,
            'total_taxable' => 0,
            'total_integrated' => 0,
            'total_central' => 0,
            'total_state' => 0,
            'total_cess' => 0
        ];

        foreach ($hsnB2CData as $groupKey => $items) {
            $firstItem = $items->first();
            $hsn = $firstItem->product->hsn_code ?? 'N/A';
            $description = $firstItem->product->description ?? ($firstItem->product->name ?? '');
            $uqc = 'OTH-OTHERS';

            $totalQty = $items->sum('quantity');
            $totalGroupValue = $items->sum('total_amount');

            $taxableValue = 0;
            $igst = 0;
            $cgst = 0;
            $sgst = 0;

            foreach ($items as $item) {
                $taxableValue += (float)$item->total_amount - (float)$item->product_gst_total;

                $gstDetails = is_string($item->product_gst_details)
                    ? json_decode($item->product_gst_details, true)
                    : $item->product_gst_details;

                if (is_array($gstDetails)) {
                    foreach ($gstDetails as $tax) {
                        $name = strtolower($tax['tax_name'] ?? $tax['tax_type'] ?? '');
                        $amt = (float)($tax['tax_amount'] ?? 0);
                        if (str_contains($name, 'igst')) $igst += $amt;
                        elseif (str_contains($name, 'cgst')) $cgst += $amt;
                        elseif (str_contains($name, 'sgst') || str_contains($name, 'utgst')) $sgst += $amt;
                    }
                }
            }

            $rate = explode('_', $groupKey)[1];

            $rows[] = [
                $hsn,
                $description,
                $uqc,
                number_format($totalQty, 2, '.', ''),
                number_format($totalGroupValue, 2, '.', ''),
                $rate,
                number_format($taxableValue, 2, '.', ''),
                number_format($igst, 2, '.', ''),
                number_format($cgst, 2, '.', ''),
                number_format($sgst, 2, '.', ''),
                '0.00' // Cess
            ];

            $summary['total_value'] += $totalGroupValue;
            $summary['total_taxable'] += $taxableValue;
            $summary['total_integrated'] += $igst;
            $summary['total_central'] += $cgst;
            $summary['total_state'] += $sgst;
        }

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('A2', 'No. of HSN');
        $sheet->setCellValue('E2', 'Total Value');
        $sheet->setCellValue('G2', 'Total Taxable Value');
        $sheet->setCellValue('H2', 'Total Integrated Tax');
        $sheet->setCellValue('I2', 'Total Central Tax');
        $sheet->setCellValue('J2', 'Total State/UT Tax');
        $sheet->setCellValue('K2', 'Total Cess');
        $sheet->getStyle('A2:K2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('A3', count($rows));
        $sheet->setCellValue('E3', number_format($summary['total_value'], 2, '.', ''));
        $sheet->setCellValue('G3', number_format($summary['total_taxable'], 2, '.', ''));
        $sheet->setCellValue('H3', number_format($summary['total_integrated'], 2, '.', ''));
        $sheet->setCellValue('I3', number_format($summary['total_central'], 2, '.', ''));
        $sheet->setCellValue('J3', number_format($summary['total_state'], 2, '.', ''));
        $sheet->setCellValue('K3', number_format($summary['total_cess'], 2, '.', ''));

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:K3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'HSN',
            'Description',
            'UQC',
            'Total Quantity',
            'Total Value',
            'Rate',
            'Taxable Value',
            'Integrated Tax Amount',
            'Central Tax Amount',
            'State/UT Tax Amount',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:K4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A5');
            $sheet->getStyle('A5:K' . (count($rows) + 4))->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);
        }

        $sheet->getRowDimension(4)->setRowHeight(30);
    }

    // Tab 21
    private function adddocsSheet($spreadsheet)
    {
        $user               = Auth::guard('api')->user();
        $role               = $user->role;
        $userId             = $user->id;
        $userBranchId       = $user->branch_id;
        $selectedSubAdminId = request()->selectedSubAdminId ?? $userId;
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
        $sheet->setTitle('docs');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 60,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary of documents issued during the tax period (13)');
        $sheet->setCellValue('E1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:E1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('D2', 'Total Number');
        $sheet->setCellValue('E2', 'Total Cancelled');
        $sheet->getStyle('A2:E2')->applyFromArray($blueStyle);

        // Fetch data for Invoices for outward supply
        $orders = Order::where('isDeleted', 0)->where('branch_id', $branch_id)->orderBy('order_number', 'asc')->get();
        $invoiceFrom = $orders->first()?->order_number ?? '';
        $invoiceTo = $orders->last()?->order_number ?? '';
        $invoiceTotalCount = $orders->count();
        $invoiceCancelledCount = Order::where('isDeleted', 1)->where('branch_id', $branch_id)->count();

        // Fetch data for Invoices for inward supply from unregistered person
        $inwardInvoices = PurchaseInvoice::with('vendor')
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->whereHas('vendor', function ($query) {
                $query->whereNull('gst_number')->orWhere('gst_number', '');
            })
            ->orderBy('invoice_number', 'asc')
            ->get();
        $inwardFrom = $inwardInvoices->first()?->invoice_number ?? '';
        $inwardTo = $inwardInvoices->last()?->invoice_number ?? '';
        $inwardTotalCount = $inwardInvoices->count();
        $inwardCancelledCount = PurchaseInvoice::where('isDeleted', 1)
            ->where('branch_id', $branch_id)
            ->whereHas('vendor', function ($query) {
                $query->whereNull('gst_number')->orWhere('gst_number', '');
            })->count();

        // Fetch data for Credit Note
        $returns = SalesReturn::where('branch_id', $branch_id)->orderBy('return_number', 'asc')->get();
        $returnFrom = $returns->first()?->return_number ?? '';
        $returnTo = $returns->last()?->return_number ?? '';
        $returnTotalCount = $returns->count();
        $returnCancelledCount = 0; // Adjust if you have a cancelled status for returns

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('D3', $invoiceTotalCount + $inwardTotalCount + $returnTotalCount);
        $sheet->setCellValue('E3', $invoiceCancelledCount + $inwardCancelledCount + $returnCancelledCount);
        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:E3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Nature of Document',
            'Sr. No. From',
            'Sr. No. To',
            'Total Number',
            'Cancelled'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:E4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');

        // Data
        $data = [
            ['Invoices for outward supply', $invoiceFrom, $invoiceTo, $invoiceTotalCount, $invoiceCancelledCount],
            ['Invoices for inward supply from un    registered person', $inwardFrom, $inwardTo, $inwardTotalCount, $inwardCancelledCount],
            ['Revised Invoice', '', '', '', '0'],
            ['Debit Note', '', '', '', '0'],
            ['Credit Note', $returnFrom, $returnTo, $returnTotalCount, $returnCancelledCount],
            ['Receipt Voucher', '', '', '', '0'],
            ['Refund Voucher', '', '', '', '0'],
            ['Payment Voucher', '', '', '', '0'],
            ['Delivery Challan for job work', '', '', '', '0'],
            ['Delivery Challan for supply on approval', '', '', '', '0'],
            ['Delivery Challan in case of liquid gas', '', '', '', '0'],
            ['Delivery Challan in cases other than by way of supply (excluding at S no 9 to 11)', '', '', '', '0'],
        ];
        $sheet->fromArray($data, null, 'A5');

        $sheet->getStyle('A5:E' . (count($data) + 4))->applyFromArray([
            'font' => ['name' => 'Times New Roman'],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
        ]);
    }

    // Tab 22: eco
    private function addecoSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('eco');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 50,
            'B' => 45,
            'C' => 30,
            'D' => 35,
            'E' => 25,
            'F' => 25,
            'G' => 25,
            'H' => 19
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Supplies through ECO-14');
        $sheet->setCellValue('H1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1:C1')->applyFromArray($blueStyle);
        $sheet->getStyle('D1:H1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('B2', 'No. of E-Commerce Operator');
        $sheet->setCellValue('D2', 'Total Net Value of Supplies');
        $sheet->setCellValue('E2', 'Total Integrated Tax');
        $sheet->setCellValue('F2', 'Total Central Tax');
        $sheet->setCellValue('G2', 'Total State/UT Tax');
        $sheet->setCellValue('H2', 'Total Cess');
        $sheet->getStyle('A2:H2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('B3', '0');
        $sheet->setCellValue('D3', '0.00');
        $sheet->setCellValue('E3', '0.00');
        $sheet->setCellValue('F3', '0.00');
        $sheet->setCellValue('G3', '0.00');
        $sheet->setCellValue('H3', '0.00');
        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:H3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Nature of Supply',
            'GSTIN of E-Commerce Operator',
            'E-Commerce Operator Name',
            'Net value of supplies',
            'Integrated tax',
            'Central tax',
            'State/UT tax',
            'Cess'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:H4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');
    }

    // Tab 23
    private function addecoaSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ecoa');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 55,
            'B' => 20,
            'C' => 25,
            'D' => 40,
            'E' => 40,
            'F' => 30,
            'G' => 30,
            'H' => 25,
            'I' => 23,
            'J' => 22,
            'K' => 23
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Amended Supplies through ECO-14A');
        $sheet->mergeCells('B1:C1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('F1:J1');
        $sheet->setCellValue('F1', 'Revised details');
        $sheet->setCellValue('K1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $lightBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:C1')->applyFromArray($orangeStyle);
        $sheet->getStyle('D1:J1')->applyFromArray($lightBlueStyle);
        $sheet->getStyle('K1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('D2', 'No. of E-Commerce Operator');
        $sheet->setCellValue('G2', 'Total Net Value of Supplies');
        $sheet->setCellValue('H2', 'Total Integrated Tax');
        $sheet->setCellValue('I2', 'Total Central Tax');
        $sheet->setCellValue('J2', 'Total State/UT Tax');
        $sheet->setCellValue('K2', 'Total Cess');
        $sheet->getStyle('A2:K2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('D3', '0');
        $sheet->setCellValue('G3', '0.00');
        $sheet->setCellValue('H3', '0.00');
        $sheet->setCellValue('I3', '0.00');
        $sheet->setCellValue('J3', '0.00');
        $sheet->setCellValue('K3', '0.00');
        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:K3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Nature of Supply',
            'Financial Year',
            'Original Month/Quarter',
            'Original GSTIN of E-Commerce Operator',
            'Revised GSTIN of E-Commerce Operator',
            'E-Commerce Operator Name',
            'Revised Net value of supplies',
            'Integrated tax',
            'Central tax',
            'State/UT tax',
            'Cess'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'size' => '11'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A4:C4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');

        $skyBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CC2E5']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('D4:G4')->applyFromArray($skyBlueStyle);
        $sheet->getStyle('H4:K4')->applyFromArray($dataHeaderStyle);
    }
    // Tab 24
    private function addecob2bSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ecob2b');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 50,
            'B' => 30,
            'C' => 25,
            'D' => 30,
            'E' => 25,
            'F' => 15,
            'G' => 30,
            'H' => 20,
            'I' => 20,
            'J' => 10,
            'K' => 30,
            'L' => 25
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Supplies U/s 9(5)-15-B2B');
        $sheet->setCellValue('L1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'size'  => 11, 'color' => ['rgb' => 'FFFFFF'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle1 = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'color' => ['rgb' => '000000'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:K1')->applyFromArray($whiteStyle1);
        $sheet->getStyle('L1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('A2', 'No. of Supplier');
        $sheet->setCellValue('C2', 'No. of Recipients');
        $sheet->setCellValue('E2', 'No. of Documents');
        $sheet->setCellValue('G2', 'Total value of supplies made');
        $sheet->setCellValue('K2', 'Total Taxable Value');
        $sheet->setCellValue('L2', 'Total Cess');
        $sheet->getStyle('A2:L2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('A3', '0');
        $sheet->setCellValue('C3', '0');
        $sheet->setCellValue('E3', '0');
        $sheet->setCellValue('G3', '0.00');
        $sheet->setCellValue('K3', '0.00');
        $sheet->setCellValue('L3', '0.00');

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:L3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Supplier GSTIN/UIN',
            'Supplier Name',
            'Recipient GSTIN/UIN',
            'Recipient Name',
            'Document Number',
            'Document Date',
            'Value of supplies made',
            'Place Of Supply',
            'Document type',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:L4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(30);
    }

    // Tab 25
    private function addecourp2bSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ecourp2b');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 50,
            'B' => 30,
            'C' => 30,
            'D' => 15,
            'E' => 35,
            'F' => 20,
            'G' => 20,
            'H' => 10,
            'I' => 25,
            'J' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Supplies U/s 9(5)-15-URP2B');
        $sheet->setCellValue('J1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'size'  => 11, 'color' => ['rgb' => 'FFFFFF'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle1 = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'color' => ['rgb' => '000000'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:I1')->applyFromArray($whiteStyle1);
        $sheet->getStyle('J1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('A2', 'No. of Recipients');
        $sheet->setCellValue('C2', 'No. of Documents');
        $sheet->setCellValue('E2', 'Total value of supplies made');
        $sheet->setCellValue('I2', 'Total Taxable Value');
        $sheet->setCellValue('J2', 'Total Cess');
        $sheet->getStyle('A2:J2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->mergeCells('A3:B3');
        $sheet->setCellValue('A3', '0');
        $sheet->mergeCells('C3:F3');
        $sheet->setCellValue('C3', '0');
        $sheet->mergeCells('G3:H3');
        $sheet->setCellValue('G3', '0.00');
        $sheet->setCellValue('I3', '0.00');
        $sheet->setCellValue('J3', '0.00');

        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:J3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Recipient GSTIN/UIN',
            'Recipient Name',
            'Document Number',
            'Document Date',
            'Value of supplies made',
            'Place Of Supply',
            'Document type',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:J4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(30);
    }

    // Tab 26 ecob2c
    private function addecob2cSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ecob2c');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 50,
            'B' => 30,
            'C' => 25,
            'D' => 35,
            'E' => 10,
            'F' => 20,
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Supplies U/s 9(5)-15-B2C');
        $sheet->setCellValue('F1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'size'  => 11, 'color' => ['rgb' => 'FFFFFF'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle1 = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'color' => ['rgb' => '000000'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:E1')->applyFromArray($whiteStyle1);
        $sheet->getStyle('F1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('A2', 'No. of Supplier');
        $sheet->setCellValue('D2', 'Total Taxable Value');
        $sheet->setCellValue('F2', 'Total Cess');
        $sheet->getStyle('A2:F2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('A3', '0');
        $sheet->setCellValue('E3', '0.00');
        $sheet->setCellValue('F3', '0.00');
        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:F3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Supplier GSTIN/UIN',
            'Supplier Name',
            'Place Of Supply',
            'Taxable Value',
            'Rate',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:F4')->applyFromArray($dataHeaderStyle);
        $sheet->getRowDimension(4)->setRowHeight(30);
    }

    // Tab 27 ecourp2c
    private function addecourp2cSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ecourp2c');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 50,
            'B' => 28,
            'C' => 15,
            'D' => 20,
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---        
        $sheet->setCellValue('A1', 'Summary For Supplies U/s 9(5)-15-URP2C');
        $sheet->setCellValue('D1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'size'  => 11, 'color' => ['rgb' => 'FFFFFF'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:D1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('B2', 'Total Taxable Value');
        $sheet->setCellValue('D2', 'Total Cess');
        $sheet->getStyle('A2:D2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('B3', '0.00');
        $sheet->setCellValue('D3', '0.00');
        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:D3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Place Of Supply',
            'Taxable Value',
            'Rate',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:D4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(30);
    }


    // Tab 28  ecoab2b
    private function addecoab2b($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ecoab2b');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 50,
            'B' => 25,
            'C' => 25,
            'D' => 25,
            'E' => 25,
            'F' => 20,
            'G' => 25,
            'H' => 20,
            'I' => 25,
            'J' => 20,
            'K' => 25,
            'L' => 10,
            'M' => 30,
            'N' => 25
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Supplies U/s 9(5) - 15A-B2B');
        $sheet->mergeCells('B1:F1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('G1:M1');
        $sheet->setCellValue('G1', 'Revised details');
        $sheet->setCellValue('N1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $lightBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:F1')->applyFromArray($orangeStyle);
        $sheet->getStyle('G1:N1')->applyFromArray($lightBlueStyle);
        $sheet->getStyle('N1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('A2', 'No. of Supplier');
        $sheet->setCellValue('C2', 'No. of Recipients');
        $sheet->setCellValue('E2', 'No. of Documents');
        $sheet->mergeCells('H3:J3');
        $sheet->setCellValue('H2', 'Total value of supplies made');
        $sheet->setCellValue('M2', 'Total Taxable Value');
        $sheet->setCellValue('N2', 'Total Cess');
        $sheet->getStyle('A2:N2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('A3', '0');
        $sheet->setCellValue('C3', '0');
        $sheet->setCellValue('E3', '0');
        $sheet->setCellValue('I3', '0.00');
        $sheet->setCellValue('M3', '0.00');
        $sheet->setCellValue('N3', '0.00');
        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:N3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Supplier GSTIN/UIN',
            'Supplier Name',
            'Recipient GSTIN/UIN',
            'Recipient Name',
            'Original Document Number',
            'Original Document Date',
            'Revised Document Number',
            'Revised Document Date',
            'Value of supplies made',
            'Document type',
            'Place Of Supply',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:L4')->applyFromArray($dataHeaderStyle);

        $skyBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 12, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CC2E5']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('G4:N4')->applyFromArray($skyBlueStyle);
        $sheet->getStyle('A4:F4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');

        $sheet->getRowDimension(4)->setRowHeight(40);
    }

    //Tab 29 ecoab2c
    private function addecoab2c($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ecoab2c');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 50,
            'B' => 15,
            'C' => 30,
            'D' => 25,
            'E' => 20,
            'F' => 10,
            'G' => 30,
            'H' => 25
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Supplies U/s 9(5)-15A-B2C');
        $sheet->mergeCells('B1:D1');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('E1:G1');
        $sheet->setCellValue('E1', 'Revised details');
        $sheet->setCellValue('H1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $lightBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['size'  => 11, 'underline' => FONT::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $skyBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 12, 'color' => ['rgb' => '00000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CC2E5']]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1:D1')->applyFromArray($orangeStyle);
        $sheet->getStyle('E1:G1')->applyFromArray($lightBlueStyle);
        $sheet->getStyle('H1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('C2', 'No. of Supplier');
        $sheet->setCellValue('G2', 'Total Taxable Value');
        $sheet->setCellValue('H2', 'Total Cess');
        $sheet->getStyle('A2:H2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('C3', '0');
        $sheet->setCellValue('G3', '0.00');
        $sheet->setCellValue('H3', '0.00');
        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:H3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Financial Year',
            'Original Month',
            'Supplier GSTIN/UIN',
            'Supplier Name',
            'Place Of Supply',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:D4')->applyFromArray($dataHeaderStyle);
        $sheet->getStyle('E4:H4')->applyFromArray($skyBlueStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(30);
    }

    // tab 30: ecoaurp2b
    public function addecoaurp2bSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ecoaurp2b');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 25,
            'B' => 25,
            'C' => 25,
            'D' => 20,
            'E' => 25,
            'F' => 20,
            'G' => 25,
            'H' => 20,
            'I' => 25,
            'J' => 10,
            'K' => 25,
            'L' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', 'Summary For Supplies U/s 9(5)-15A-URP2B');
        $sheet->mergeCells('C1:D1');
        $sheet->setCellValue('C1', 'Original details');
        $sheet->mergeCells('E1:K1');
        $sheet->setCellValue('E1', 'Revised details');
        $sheet->setCellValue('L1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $lightBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $whiteStyle = [
            'font' => ['size'  => 11, 'underline' => FONT::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $skyBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'color' => ['rgb' => '00000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CC2E5']]
        ];

        $sheet->getStyle('A1:B1')->applyFromArray($blueStyle);
        $sheet->getStyle('C1:D1')->applyFromArray($orangeStyle);
        $sheet->getStyle('E1:K1')->applyFromArray($lightBlueStyle);
        $sheet->getStyle('L1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('A2', 'No. of Recipients');
        $sheet->setCellValue('C2', 'No. of Documents');
        $sheet->setCellValue('G2', 'Total value of supplies made');
        $sheet->setCellValue('K2', 'Total Taxable Value');
        $sheet->setCellValue('L2', 'Total Cess');
        $sheet->getStyle('A2:L2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('A3', '0');
        $sheet->setCellValue('C3', '0');
        $sheet->setCellValue('G3', '0.00');
        $sheet->setCellValue('K3', '0.00');
        $sheet->setCellValue('K3', '0.00');
        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:L3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Recipient GSTIN/UIN',
            'Recipient Name',
            'Original Document Number',
            'Original Document Date',
            'Revised Document Number',
            'Revised Document Date',
            'Value of supplies made',
            'Document type',
            'Place Of Supply',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'color' => ['rgb' => '00000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];
        $sheet->getStyle('A4:D4')->applyFromArray($dataHeaderStyle);
        $sheet->getStyle('E4:L4')->applyFromArray($skyBlueStyle);
        $sheet->freezePane('A5');
        $sheet->getRowDimension(4)->setRowHeight(40);
    }

    //tab 31:ecoaurp2c
    private function addecoaurp2cSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ecoaurp2c');

        // --- COLUMN WIDTHS ---
        $widths = [
            'A' => 50,
            'B' => 20,
            'C' => 25,
            'D' => 15,
            'E' => 20,
            'F' => 20
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // --- ROW 1: TOP HEADERS ---
        $sheet->setCellValue('A1', 'Summary For Supplies U/s 9(5)-15A-URP2C');
        $sheet->setCellValue('B1', 'Original details');
        $sheet->mergeCells('C1:E1');
        $sheet->setCellValue('C1', 'Revised details');
        $sheet->setCellValue('F1', 'HELP');

        // Styles
        $blueStyle = [
            'font' => ['name' => 'Times New Roman', 'bold' => true, 'size'  => 11, 'color' => ['rgb' => 'FFFFFF'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0070C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $orangeStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $lightBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B4C6E7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $skyBlueStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'color' => ['rgb' => '00000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CC2E5']]
        ];
        $whiteStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => ['rgb' => '2D63C5'],],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A1')->applyFromArray($blueStyle);
        $sheet->getStyle('B1')->applyFromArray($orangeStyle);
        $sheet->getStyle('C1:F1')->applyFromArray($lightBlueStyle);
        $sheet->getStyle('C4:E4')->applyFromArray($skyBlueStyle);
        $sheet->getStyle('F1')->applyFromArray($whiteStyle);

        // --- ROW 2: SUMMARY LABELS ---
        $sheet->setCellValue('E2', 'Total Taxable Value');
        $sheet->setCellValue('F2', 'Total Cess');
        $sheet->getStyle('A2:F2')->applyFromArray($blueStyle);

        // --- ROW 3: SUMMARY VALUES ---
        $sheet->setCellValue('E3', '0.00');
        $sheet->setCellValue('F3', '0.00');
        $peachStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A3:F3')->applyFromArray($peachStyle);

        // --- ROW 4: MAIN DATA HEADERS ---
        $headers = [
            'Financial Year',
            'Original Month',
            'Place Of Supply',
            'Rate',
            'Taxable Value',
            'Cess Amount'
        ];
        $sheet->fromArray($headers, null, 'A4');

        $dataHeaderStyle = [
            'font' => ['name' => 'Times New Roman', 'size'  => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7CAAC']],
            // 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A4:B4')->applyFromArray($dataHeaderStyle);
        $sheet->freezePane('A5');
        $sheet->getStyle('F4')->applyFromArray($skyBlueStyle); // Apply to HELP column as well if needed

        // --- DUMMY DATA (Optional but good for design) ---
        // Since user said "do not remove any line", I'll just leave it ready for data.
    }
    //Tab 32: master
    private function addMasterSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('master');

        // ======================
        // HEADER ROW
        // ======================

        $headers = [
            'UQC',
            'Export Type',
            'Reverse Charge/Provisional Assessment',
            'Note Type',
            'Type',
            'Tax Rate',
            'POS',
            'Invoice Type',
            'Nature of Document',
            'UR Type',
            'Supply Type',
            'Month',
            'Financial Year',
            'Differential Percentage',
            'POS96',
            'Nature of Supply'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Header Styling
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'BDD7EE']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Decrease width of "Reverse Charge/Provisional Assessment" (Column C)
        $sheet->getColumnDimension('C')->setAutoSize(false);
        $sheet->getColumnDimension('C')->setWidth(8); // adjust as needed
        $sheet->getStyle('C1')->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('G')->setAutoSize(false);
        $sheet->getColumnDimension('G')->setWidth(17); // adjust as needed
        $sheet->getStyle('G')->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('H')->setAutoSize(false);
        $sheet->getColumnDimension('H')->setWidth(17); // adjust as needed
        $sheet->getStyle('H')->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('N')->setAutoSize(false);
        $sheet->getColumnDimension('N')->setWidth(17); // adjust as needed
        $sheet->getStyle('N')->getAlignment()->setWrapText(true);


        // ======================
        // DATA EXACT LIKE IMAGE
        // ======================

        // UQC LIST (COLUMN A)
        $uqc = [
            'BAG-BAGS',
            'BAL-BALE',
            'BDL-BUNDLES',
            'BKL-BUCKLES',
            'BOX-BOX',
            'BTL-BOTTLES',
            'BUN-BUNCHES',
            'CAN-CANS',
            'CBM-CUBIC METERS',
            'CCM-CUBIC CENTIMETERS',
            'CMS-CENTIMETERS',
            'CTN-CARTONS',
            'DOZ-DOZENS',
            'DRM-DRUMS',
            'GGR-GREAT GROSS',
            'GMS-GRAMMES',
            'GRS-GROSS',
            'GYD-GROSS YARDS',
            'KGS-KILOGRAMS',
            'KLR-KILOLITRE',
            'KME-KILOMETRE',
            'LTR-LITRES',
            'MLT-MILLILITRE',
            'MTR-METERS',
            'MTK-METRIC TON',
            'NOS-NUMBERS',
            'PAC-PACKS',
            'PCS-PIECES',
            'PRS-PAIRS',
            'QTL-QUINTAL',
            'ROL-ROLLS',
            'SET-SETS',
            'SQF-SQUARE FEET',
            'SQM-SQUARE METERS',
            'SQY-SQUARE YARDS',
            'TBS-TABLETS',
            'THD-THOUSANDS',
            'TON-TONNES',
            'TUB-TUBES',
            'UGS-US GALLONS',
            'UNT-UNITS',
            'YDS-YARDS',
            'OTH-OTHERS'
        ];

        // POS / POS96 LIST
        $states = [
            '01-Jammu & Kashmir',
            '02-Himachal Pradesh',
            '03-Punjab',
            '04-Chandigarh',
            '05-Uttarakhand',
            '06-Haryana',
            '07-Delhi',
            '08-Rajasthan',
            '09-Uttar Pradesh',
            '10-Bihar',
            '11-Sikkim',
            '12-Arunachal Pradesh',
            '13-Nagaland',
            '14-Manipur',
            '15-Mizoram',
            '16-Tripura',
            '17-Meghalaya',
            '18-Assam',
            '19-West Bengal',
            '20-Jharkhand',
            '21-Odisha',
            '22-Chhattisgarh',
            '23-Madhya Pradesh',
            '24-Gujarat',
            '25-Daman & Diu',
            '26-Dadra & Nagar Haveli & Daman & Diu',
            '27-Maharashtra',
            '29-Karnataka',
            '30-Goa',
            '31-Lakshadweep',
            '32-Kerala',
            '33-Tamil Nadu',
            '34-Puducherry',
            '35-Andaman & Nicobar Islands',
            '36-Telangana',
            '37-Andhra Pradesh',
            '38-Ladakh',
            '96-Foreign Country',
            '97-Other Territory'
        ];

        // EXPORT TYPE
        $exportTypes = ['WPAY', 'WOPAY'];
        // REVERSE CHARGE / PROVISIONAL ASSESSMENT
        $reverseCharge = ['N', 'Y'];

        // NOTE TYPE
        $noteTypes = ['C', 'D'];

        // TYPE
        $types = ['OE', 'E'];

        $taxRates = ['0.00', '0.10', '0.25', '1.00', '1.50', '3.00', '5.00', '12.00', '18.00', '28.00'];

        // INVOICE TYPE
        $invoiceTypes = [
            'Regular B2B',
            'SEZ supplies with payment',
            'SEZ supplies without payment',
            'Deemed Exp'
        ];
        // NATURE OF DOCUMENT
        $natureOfDocument = [
            'Invoices for outward supply',
            'Invoices for inward supply from unregistered person',
            'Revised Invoice',
            'Debit Note',
            'Credit Note',
            'Receipt Voucher',
            'Refund Voucher',
            'Payment Voucher',
            'Delivery Challan for job work'
        ];

        // UR TYPE
        $urTypes = ['B2CL', 'EXPWP', 'EXPWOP'];

        // SUPPLY TYPE
        $supplyTypes = ['Inter State', 'Intra State'];

        // DIFFERENTIAL %
        $differentialPercent = ['65.00'];


        // MONTH LIST
        $months = [
            'JANUARY',
            'FEBRUARY',
            'MARCH',
            'APRIL',
            'MAY',
            'JUNE',
            'JULY',
            'AUGUST',
            'SEPTEMBER',
            'OCTOBER',
            'NOVEMBER',
            'DECEMBER'
        ];

        // FINANCIAL YEAR LIST
        $years = [
            '2017-18',
            '2018-19',
            '2019-20',
            '2020-21',
            '2021-22',
            '2022-23',
            '2023-24',
            '2024-25'
        ];

        // ======================
        // FILL DATA COLUMN WISE
        // ======================

        $row = 2;

        // UQC
        foreach ($uqc as $val) {
            $sheet->setCellValue("A{$row}", $val);
            $row++;
        }

        // Export Type (B)
        $row = 2;
        foreach ($exportTypes as $val) {
            $sheet->setCellValue("B{$row}", $val);
            $row++;
        }

        // Reverse Charge (C)
        $row = 2;
        foreach ($reverseCharge as $val) {
            $sheet->setCellValue("C{$row}", $val);
            $row++;
        }

        // Note Type (D)
        $row = 2;
        foreach ($noteTypes as $val) {
            $sheet->setCellValue("D{$row}", $val);
            $row++;
        }

        // Type (E)
        $row = 2;
        foreach ($types as $val) {
            $sheet->setCellValue("E{$row}", $val);
            $row++;
        }

        // Tax Rate (F)
        $row = 2;
        foreach ($taxRates as $val) {
            $sheet->setCellValue("F{$row}", $val);
            $row++;
        }

        // POS + POS96
        $row = 2;
        foreach ($states as $val) {
            $sheet->setCellValue("G{$row}", $val);
            $sheet->setCellValue("O{$row}", $val);
            $row++;
        }

        // Invoice Type (H)
        $row = 2;
        foreach ($invoiceTypes as $val) {
            $sheet->setCellValue("H{$row}", $val);
            $row++;
        }

        // Nature Of Document (I)
        $row = 2;
        foreach ($natureOfDocument as $val) {
            $sheet->setCellValue("I{$row}", $val);
            $row++;
        }

        // UR Type (J)
        $row = 2;
        foreach ($urTypes as $val) {
            $sheet->setCellValue("J{$row}", $val);
            $row++;
        }

        // Supply Type (K)
        $row = 2;
        foreach ($supplyTypes as $val) {
            $sheet->setCellValue("K{$row}", $val);
            $row++;
        }

        // Month (L)
        $row = 2;
        foreach ($months as $val) {
            $sheet->setCellValue("L{$row}", $val);
            $row++;
        }

        // Financial Year (M)
        $row = 2;
        foreach ($years as $val) {
            $sheet->setCellValue("M{$row}", $val);
            $row++;
        }

        // Differential Percentage (N)
        $row = 2;
        foreach ($differentialPercent as $val) {
            $sheet->setCellValue("N{$row}", $val);
            $row++;
        }


        $lastRow = max(
            count($uqc),
            count($states),
            count($months),
            count($years)
        ) + 1;

        $sheet->getStyle("A2:P{$lastRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);
    }
}
