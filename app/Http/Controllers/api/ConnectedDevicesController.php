<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ConnectedDevice;
use App\Models\ConnectedDeviceScan;
use Illuminate\Http\Request;

class ConnectedDevicesController extends Controller
{
    public function connect(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $device = ConnectedDevice::where('device_code', $validated['code'])->first();

        if (! $device) {
            return response()->json(['status' => false, 'message' => 'Device not found'], 404);
        }

        $device->update([
            'device_name' => $validated['device_name'] ?? 'Mobile',
            'status'      => 1,
        ]);

        return response()->json([
            'status'      => true,
            'message'     => 'Device connected successfully',
            'device_code' => $device->device_code,
            'session_id'  => $device->session_id,
        ]);
    }

    public function check(string $code)
    {
        $device = ConnectedDevice::where('device_code', $code)->first();

        return response()->json([
            'connected'   => $device ? (int) $device->status === 1 : false,
            'device_name' => $device->device_name ?? null,
            'session_id'  => $device->session_id ?? null,
        ]);
    }

    public function disconnect(string $code)
    {
        $updated = ConnectedDevice::where('device_code', $code)->update([
            'status' => 0,
        ]);

        return response()->json([
            'status'  => true,
            'updated' => $updated > 0,
        ]);
    }

    public function submitScan(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'barcode' => ['required', 'string', 'max:191'],
        ]);

        $device = ConnectedDevice::where('device_code', $validated['code'])
            ->where('status', 1)
            ->first();

        if (! $device) {
            return response()->json([
                'status' => false,
                'message' => 'Device is not connected',
            ], 404);
        }

        $scan = ConnectedDeviceScan::create([
            'device_code' => $device->device_code,
            'barcode' => trim($validated['barcode']),
        ]);

        return response()->json([
            'status' => true,
            'scan_id' => $scan->id,
            'barcode' => $scan->barcode,
        ]);
    }
}
