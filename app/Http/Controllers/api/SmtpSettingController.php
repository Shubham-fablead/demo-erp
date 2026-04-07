<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SmtpSettingController extends Controller
{
      public function show(Request $request)
    {
        $subAdminId = $request->query('selectedSubAdminId');

        $query = SmtpSetting::query();
        if ($subAdminId) {
            $query->where('branch_id', $subAdminId);
        }

        $setting = $query->first();

        return response()->json([
            'success' => true,
            'settings' => $setting,
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'host'     => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'port'     => 'required|integer',
            'encryption' => 'nullable|string|in:ssl,tls,none',
            'status'   => 'nullable|in:0,1',
            'password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $subAdminId = $request->input('selectedSubAdminId');

        $query = SmtpSetting::query();
        if ($subAdminId) {
            $query->where('branch_id', $subAdminId);
        }

        $setting = $query->first();

        $data = [
            'host'         => $request->host,
            'username'     => $request->username,
            'port'         => $request->port,
            'encryption'   => $request->encryption,
            'status'       => $request->status ?? 1,
            'from_address' => $request->username, // use username as from_address or add separate field
            'from_name'    => $request->from_name ?? '',
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = encrypt($request->password);
        }


        if ($subAdminId) {
            $data['branch_id'] = $subAdminId;
        }

        if ($setting) {
            $setting->update($data);
        } else {
            $setting = SmtpSetting::create(array_merge($data, ['mailer' => 'smtp']));
        }

        return response()->json([
            'success' => true,
            'message' => 'SMTP Settings updated successfully!',
            'settings' => $setting,
        ]);
    }
}
