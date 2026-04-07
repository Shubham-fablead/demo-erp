<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{

    public function index(Request $request)
    {
        $user         = Auth::user();
        $role         = $user->role;
        $userBranchId = $user->id;
        $subAdminId   = session('selectedSubAdminId');

        $monthInput  = $request->input('month', now()->format('Y-m'));
        $month       = Carbon::parse($monthInput)->format('m');
        $year        = Carbon::parse($monthInput)->format('Y');
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $currentMonth = $monthInput;

        // ✅ Fetch sunday_off setting for the branch
        if ($role === 'admin' && ! empty($subAdminId)) {
            $settings = Setting::where('branch_id', $subAdminId)->first();
        } else {
            $settings = Setting::where('branch_id', $userBranchId)->first();
        }

        $sundayOff = $settings->sunday_off ?? 'no'; // default 'no'

        // Attendance query with month/year filter
        $attendanceQuery = Attendance::whereMonth('date', $month)
            ->whereYear('date', $year);

        $attendances = $attendanceQuery->get()->groupBy(function ($item) {
            return $item->user_id . '_' . $item->date;
        });

        // Staff query
        $staffQuery = User::query();

        if ($role == 'staff') {
            $staffQuery->where('id', $user->id);
        } else {
            $staffQuery->where('role', 'staff')->where('isDeleted', 0);

            if ($request->has('search') && ! empty($request->search)) {
                $staffQuery->where('name', 'like', '%' . $request->search . '%');
            }

            if (! empty($subAdminId)) {
                $staffQuery->where('branch_id', $subAdminId);
            } elseif ($role == 'sub-admin') {
                $staffQuery->where('branch_id', $userBranchId);
            } else {
                $staffQuery->where('branch_id', $userBranchId);
            }
        }

        $staffUsers = $staffQuery->orderBy('id', 'desc')->get();

        return view('attendance.view', compact('staffUsers', 'attendances', 'currentMonth', 'year', 'month', 'daysInMonth', 'sundayOff'));
    }

    public function add(Request $request)
    {
        $user         = Auth::user();
        $role         = $user->role;
        $userBranchId = $user->id;
        $subAdminId   = session('selectedSubAdminId');
        if (! empty($subAdminId)) {
            $query      = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $subAdminId)->orderBy('id', 'desc');
            $staffUsers = $query->get();
        } elseif ($role == 'sub-admin') {
            $query      = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userBranchId)->orderBy('id', 'desc');
            $staffUsers = $query->get();
        } else {
            $query      = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userBranchId)->orderBy('id', 'desc');
            $staffUsers = $query->get();
        }
        return view('attendance.add', compact('staffUsers'));
    }
}
