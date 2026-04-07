<?php

namespace App\Http\Controllers\admin;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AdvancePayment;
use App\Models\Salary;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
class SalaryController extends Controller
{
    public function List(Request $request)
    {
        return view('salary/salary_list');
    }
    public function viewList(Request $request)
    {
        $staffId = $request->input('staff_id');
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $staff = User::find($staffId);

        if (!$staff) {
            return response()->json(['status' => false, 'message' => 'Staff not found.'], 404);
        }

        $salary = Salary::where('staff_id', $staffId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->first();

        if (!$salary) {
            return response()->json(['status' => false, 'message' => 'Salary not generated yet.'], 404);
        }

        // Fetch settings
        $settings = Setting::first();
        
        $data = [[
            'staff_id' => $staff->id,
            'staff_name' => $staff->name,
            'present' => $salary->present,
            'absent' => $salary->absent,
            'extra_present' => $salary->extra_present,
            'advance_payment' => $salary->advance_pay,
            'salary' => $salary->salary,
            'extra_amount' => $salary->extra_amount,
            'total_salary' => $salary->total_salary,
            'status' => $salary->status,
        ]];
        return view('salary/staff_pdf',compact('data', 'month', 'year', 'settings'));
    }
}
