<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GeneralSettingController extends Controller
{

    public function show(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->id;
        $BranchId     = $user->branch_id;

        $selectedSubAdminId = $request->query('selectedSubAdminId');

        if ($role === 'admin' && ! empty($selectedSubAdminId)) {
            $subAdmin = User::where('id', $selectedSubAdminId)->first();
            // dd($subAdmin);
            if (! $subAdmin) {
                $selectedSubAdminId = $subAdmin->id;
            }
        } else {
            $selectedSubAdminId = $userBranchId;
        }
        // dd($selectedSubAdminId);
        // Common: fetch settings (filter by branch if needed)
        $settings = Setting::where('branch_id', $selectedSubAdminId)->first();

        return response()->json([
            'status'   => true,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->id;

        $selectedSubAdminId = $request->selectedSubAdminId;

        if ($role === 'admin' && ! empty($selectedSubAdminId)) {
            $subAdmin = User::where('id', $selectedSubAdminId)->first();
            // dd($subAdmin);
            if (! $subAdmin) {
                $selectedSubAdminId = $subAdmin->id;
            }
        } else {
            $selectedSubAdminId = $userBranchId;
        }
        // dd($selectedSubAdminId);
        $request->validate([
            'shop_name'         => 'required|string',
            'gst_num'           => 'nullable',
            'low_stock'         => 'nullable',
            'state_code'        => 'nullable',
            'email'             => 'required|email',
            'phone'             => 'required',
            'address'           => 'required|string',
            'currency_symbol'   => 'required|string',
            'currency_position' => 'required|string',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'favicon'           => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'qr_code'           => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'bank_name' => 'required|string',
            'branch' => 'required|string',
            'ac_no' => 'required|string',
            'ifsc_code' => 'required|string',
            // ✅ New Rules validation
            'working_hours'     => 'nullable|numeric|min:0',
            'sunday_off'        => 'nullable|in:yes,no',
            'grace_period'      => 'nullable|numeric|min:0',
            'lunch_break'       => 'nullable|numeric|min:0',
            'open_time'         => 'nullable|date_format:H:i',
            'close_time'        => 'nullable|date_format:H:i',
            'invoice_size' => 'nullable|in:small,big',
            'send_mail'         => 'nullable|boolean',
        ]);

        // Fetch or create branch-specific settings
        $settings = Setting::where('branch_id', $selectedSubAdminId)->first();

        if (! $settings) {
            $settings            = new Setting();
            $settings->branch_id = $selectedSubAdminId; // assign branch
        }

        $settings->gst_num           = $request->gst_num;
        $settings->low_stock         = $request->low_stock;
        $settings->name              = $request->shop_name;
        $settings->email             = $request->email;
        $settings->phone             = $request->phone;
        $settings->state_code        = $request->state_code;
        $settings->address           = $request->address;
        $settings->currency_position = $request->currency_position;
        $settings->currency_symbol   = $request->currency_symbol;
        $settings->bank_name = $request->bank_name;
        $settings->branch = $request->branch;
        $settings->ac_no = $request->ac_no;
        $settings->ifsc_code = $request->ifsc_code;
        // 🕒 Company Rules
        $settings->working_hours = $request->working_hours;
        $settings->sunday_off    = $request->sunday_off;
        $settings->grace_period  = $request->grace_period;
        $settings->lunch_break   = $request->lunch_break;
        $settings->open_time     = $request->open_time;
        $settings->close_time    = $request->close_time;
        $settings->invoice_size      = $request->invoice_size;
        $settings->send_mail     = $request->has('send_mail')
            ? (int) $request->send_mail
            : ($settings->send_mail ?? 1);

        if ($request->hasFile('logo')) {
            $logoPath       = $request->file('logo')->store('logos', 'public');
            $settings->logo = $logoPath;
        }

        if ($request->hasFile('favicon')) {
            $faviconPath       = $request->file('favicon')->store('favicons', 'public');
            $settings->favicon = $faviconPath;
        }

        if ($request->hasFile('qr_code')) {
            $qr_code           = $request->file('qr_code')->store('qr_codes', 'public');
            $settings->qr_code = $qr_code;
        }

        $settings->save();

        return response()->json([
            'status'   => true,
            'message'  => 'Settings updated successfully',
            'settings' => $settings,
        ]);
    }

    public function updateCompanyRules(Request $request)
    {
        $user               = Auth::guard('api')->user();
        $role               = $user->role;
        $userBranchId       = $user->id;
        $selectedSubAdminId = $request->selectedSubAdminId;

        // 🔹 Role-based branch selection
        if ($role === 'admin' && ! empty($selectedSubAdminId)) {
            $subAdmin = User::where('id', $selectedSubAdminId)->first();
            if ($subAdmin) {
                $selectedSubAdminId = $subAdmin->id;
            }
        } else {
            $selectedSubAdminId = $userBranchId;
        }

        // 🔹 Validation
        $validator = Validator::make($request->all(), [
            'working_hours' => 'required',
            'sunday_off'    => 'required|in:yes,no',
            'grace_period'  => 'required',
            'lunch_break'   => 'nullable',
            'open_time'     => 'required|date_format:H:i',
            'close_time'    => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // 🔹 Fetch Setting for Branch
        $settings = Setting::where('branch_id', $selectedSubAdminId)->first();

        if (! $settings) {
            return response()->json([
                'status'  => false,
                'message' => 'Settings not found for this branch',
            ], 404);
        }

        // 🔹 Update Company Rules
        $settings->update([
            'working_hours' => $request->working_hours,
            'sunday_off'    => $request->sunday_off,
            'grace_period'  => $request->grace_period,
            'lunch_break'   => $request->lunch_break,
            'open_time'     => $request->open_time,
            'close_time'    => $request->close_time,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Company rules updated successfully',
            'data'    => $settings,
        ]);
    }
}
