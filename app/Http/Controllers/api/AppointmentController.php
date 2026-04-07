<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Appointment;
use App\Models\AutoCarModel;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AppointmentController extends Controller
{
    public function getAllAppointments(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id;
        $selectedSubAdminId = $request->query('selectedSubAdminId');

        $query = Appointment::where('is_deleted', 0);

        if ($selectedSubAdminId !== "null" && $role !== 'staff') {
            // dd($selectedSubAdminId);

            $query->where('branch_id', $selectedSubAdminId ?? $userBranchId);
        } elseif ($role === 'admin') {
            $query->where('branch_id', $userBranchId);
            // dd($role);
        } elseif ($role === 'sub-admin') {
            $query->where('branch_id', $userBranchId);
        } elseif ($role === 'staff') {

            $query->where('branch_id', $BranchId);
        } else {
            $query->where('branch_id', $BranchId);
        }

        // ✅ Apply filters OUTSIDE role logic
        if ($request->filled('year')) {
            $query->whereYear('appointment_date', $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth('appointment_date', $request->month);
        }

        if ($request->filled('date')) {
            try {
                $date = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
                $query->whereDate('appointment_date', $date);
            } catch (\Exception $e) {
                // Invalid date format
            }
        }

        $appointments = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['status' => true, 'data' => $appointments], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric',
            'address' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'vehicle_type' => 'required',
            'vehicle_model' => 'required|integer|exists:auto_car_models,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'nullable',
            'status' => 'nullable|string|in:pending,confirmed,completed,cancelled',
            'remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $selectedSubAdminId = $request->selectedSubAdminId ?? $userBranchId;
        if ($selectedSubAdminId !== "null") {
            $userBranchId = $selectedSubAdminId;
        }

        // ✅ Assign user ID to branch_id
        $data = $request->all();
        $data['branch_id'] = $BranchId ?? $userBranchId;

        // ✅ Default status to 'confirmed' if not provided
        $data['status'] = $data['status'] ?? 'confirmed';

        $appointment = Appointment::create($data);

        return response()->json(['status' => true, 'appointment' => $appointment], 200);
    }

    public function show($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['status' => false, 'message' => 'Appointment not found'], 404);
        }
        return response()->json(['status' => true, 'appointment' => $appointment], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric',
            'address' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|string|max:255',
            'vehicle_model' => 'required|integer|exists:auto_car_models,id',
            // 'appointment_date' => 'required|date|after_or_equal:today',
            // 'appointment_time' => 'required',
            'status' => 'nullable|string|in:pending,confirmed,completed,cancelled',
            'remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['status' => false, 'message' => 'Appointment not found'], 404);
        }

        $appointment->update($request->all());
        return response()->json(['status' => true, 'appointment' => $appointment], 200);
    }

    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment || $appointment->is_deleted) {
            return response()->json(['status' => false, 'message' => 'Appointment not found'], 404);
        }

        // Mark the record as deleted
        $appointment->is_deleted = 1;
        $appointment->save();

        return response()->json(['status' => true, 'message' => 'Appointment deleted successfully'], 200);
    }

    public function getBrandByModel($model)
    {
        $brand = AutoCarModel::where('model_name', $model)
            ->where('is_deleted', 0)
            ->value('model_brand'); // fetch single brand

        return response()->json(['brand' => $brand]);
    }
    public function exportAppointments(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not authenticated.'], 401);
        }

        $role = $user->role;
        $userBranchId = $user->id;        // For admin & sub-admin (their own branch)
        $branchId = $user->branch_id;     // For staff (assigned branch)
        $selectedSubAdminId = $request->query('selectedSubAdminId'); // Optional filter
        $month = $request->input('month'); // Optional filter
        $year = $request->input('year');   // Optional filter

        // Base query
        $query = Appointment::where('is_deleted', 0);

        // ✅ Branch Filtering Logic
        if (!empty($selectedSubAdminId) && $selectedSubAdminId !== "null" && $role !== 'staff') {
            // If sub-admin branch explicitly selected (only admin/sub-admin can filter like this)
            $query->where('branch_id', $selectedSubAdminId);
        } elseif ($role === 'admin') {
            // Admin sees their own branch
            $query->where('branch_id', $userBranchId);
        } elseif ($role === 'sub-admin') {
            // Sub-admin sees their own branch
            $query->where('branch_id', $userBranchId);
        } elseif ($role === 'staff') {
            // Staff sees only their assigned branch
            $query->where('branch_id', $branchId);
        } else {
            // Default fallback (staff-like behavior)
            $query->where('branch_id', $branchId);
        }

        // ✅ Date filters
        if (!empty($month)) {
            $query->whereMonth('appointment_date', $month);
        }
        if (!empty($year)) {
            $query->whereYear('appointment_date', $year);
        }

        $appointments = $query->get();

        if ($appointments->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No appointments found.'], 404);
        }

        // ✅ Excel rows
        $rows = [];
        $rows[] = [
            'ID',
            'Name',
            'Phone',
            'Address',
            'Vehicle Number',
            'Vehicle Type',
            'Brand',
            'Model',
            'Appointment Date',
            'Appointment Time',
            'Status',
            'Remarks'
        ];

        foreach ($appointments as $app) {
            $rows[] = [
                $app->id,
                $app->name,
                $app->phone,
                $app->address,
                $app->vehicle_number,
                $app->vehicle_type,
                $app->vehicle_brand,
                $app->vehicle_model,
                $app->appointment_date,
                $app->appointment_time,
                ucfirst($app->status),
                $app->remarks,
            ];
        }

        // ✅ Generate Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($rows, null, 'A1');

        // Style header row
        $headerRange = 'A1:L1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('2196F3'); // Blue header

        // Auto column width
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Borders
        $sheet->getStyle('A1:L' . count($rows))
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // File download
        $writer = new Xlsx($spreadsheet);
        $filename = 'appointments_export_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
