<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\GstReportService;

class GstReportController extends Controller
{
    protected $gstReportService;

    public function __construct(GstReportService $gstReportService)
    {
        $this->gstReportService = $gstReportService;
    }

    public function downloadGstr3b(Request $request)
    {
        $filter = $request->get('filter', '');

        // 👇 dynamic data
        $data = $this->gstReportService->getGstr3bData($filter);

        $pdf = Pdf::loadView('pdf.gstr3b', compact('data'))->setPaper('A4');
        return $pdf->download('GSTR-3B-Report.pdf');
    }

    // 👇 if you also want JSON for AJAX
    public function gstr3bData(Request $request)
    {
        $filter = $request->get('filter', '');
        return response()->json($this->gstReportService->getGstr3bData($filter));
    }
}
