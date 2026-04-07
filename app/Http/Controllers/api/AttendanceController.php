<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        // dd($request->all());
        $today = Carbon::today()->toDateString();

        $request->validate([
            'id' => 'nullable|exists:attendances,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:P,A,H',
            'extraday' => 'required|boolean',

            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',

            'reason' => $request->status === 'A'
                ? 'required|string|max:255'
                : 'nullable|string|max:255',
        ], [
            'user_id.required' => 'Staff name is required.',
            'user_id.exists' => 'Selected staff does not exist.',
            'date.before_or_equal' => 'You cannot mark attendance for a future date.',
            'reason.required' => 'Reason is required for Absent status.',
            'extraday.required' => 'Extraday field is required.',
        ]);


        $user = Auth::guard('api')->user();
        $userId = $user->id;

        if ($request->filled('selectedSubAdminId') && is_numeric($request->input('selectedSubAdminId'))) {
            $userId = (int) $request->input('selectedSubAdminId');
        }

        Attendance::updateOrCreate(
            ['user_id' => $request->user_id, 'date' => $request->date],
            [
                'branch_id' => $userId,
                'status' => $request->status,
                'check_in_time' => $request->check_in_time,
                'check_out_time' => $request->check_out_time,
                'reason' => $request->reason,
                'extraday' => $request->extraday,
            ]
        );

        return response()->json(['message' => 'Attendance saved successfully']);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date_format:d/m/Y|before_or_equal:today',
            'end_date' => 'required|date_format:d/m/Y|after_or_equal:start_date|before_or_equal:today',
            'status' => 'required|in:P,A,H',
            'extraday' => 'required|boolean',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
            'reason' => $request->status === 'A' ? 'required|string|max:255' : 'nullable|string|max:255',
        ]);

        $user = Auth::guard('api')->user();
        $branchId = $user->id;
        $subAdminId = $request->input('selectedSubAdminId');

        if (!empty($subAdminId) && is_numeric($subAdminId)) {
            $branchId = (int) $subAdminId;
        }

        // Get staff IDs
        if ($request->filled('user_id')) {
            $staffIds = [$request->user_id];
        } else {
            $staffIds = User::where('role', 'staff')
                ->where('isDeleted', 0)
                ->where('branch_id', $branchId)
                ->pluck('id');
        }

        $startDate = Carbon::createFromFormat('d/m/Y', $request->start_date);
        $endDate = Carbon::createFromFormat('d/m/Y', $request->end_date);

        for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
            $currentDate = $date->toDateString();
            foreach ($staffIds as $staffId) {
                Attendance::updateOrCreate(
                    ['user_id' => $staffId, 'date' => $currentDate],
                    [
                        'branch_id' => $branchId,
                        'status' => $request->status,
                        'check_in_time' => $request->check_in_time,
                        'check_out_time' => $request->check_out_time,
                        'reason' => $request->reason,
                        'extraday' => $request->extraday,
                    ]
                );
            }
        }

        return response()->json(['message' => 'All attendance saved successfully']);
    }
}
