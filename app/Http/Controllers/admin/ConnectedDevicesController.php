<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ConnectedDevice;
use App\Models\ConnectedDeviceScan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConnectedDevicesController extends Controller
{
    public function connected_devices(Request $request)
    {
        $device = ConnectedDevice::where('session_id', session()->getId())
            ->where('status', 1)
            ->latest()
            ->first();

        if ($device) {
            session(['connected_device' => $device->device_code]);
        } else {
            session()->forget('connected_device');
        }

        return view('connected-devices', [
            'device' => $device,
        ]);
    }

    public function scanner(Request $request)
    {
        return view('scanner');
    }

    public function getSessionDevice()
    {
        $device = ConnectedDevice::where('session_id', session()->getId())
            ->where('status', 1)
            ->latest()
            ->first();

        if (! $device) {
            session()->forget('connected_device');
            return response()->json(['connected' => false]);
        }

        session(['connected_device' => $device->device_code]);

        return response()->json([
            'connected'   => true,
            'device_code' => $device->device_code,
            'device_name' => $device->device_name,
        ]);
    }

    public function generateCode()
    {
        $device = ConnectedDevice::where('session_id', session()->getId())
            ->whereIn('status', [0, 1])
            ->latest()
            ->first();

        if (! $device) {
            $device = ConnectedDevice::create([
                'device_code' => 'DEV_' . Str::upper(Str::random(10)),
                'status'      => 0,
                'session_id'  => session()->getId(),
            ]);
        }

        if ((int) $device->status === 1) {
            session(['connected_device' => $device->device_code]);
        }

        return response()->json([
            'code'      => $device->device_code,
            'connected' => (int) $device->status === 1,
        ]);
    }

    public function connect(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $device = ConnectedDevice::where('device_code', $validated['code'])->first();

        if (! $device) {
            return response()->json(['status' => false, 'message' => 'Device not found']);
        }

        $device->update([
            'device_name' => $validated['device_name'] ?? 'Mobile',
            'status'      => 1,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Device connected successfully',
            'code'    => $device->device_code,
            'device_name' => $device->device_name,
        ]);
    }

    public function check(Request $request, $code)
    {
        $globalCheck = $request->boolean('global');

        $query = ConnectedDevice::where('device_code', $code);

        if (! $globalCheck) {
            $query->where('session_id', session()->getId());
        }

        $device = $query->first();

        if (! $device) {
            if (! $globalCheck) {
                session()->forget('connected_device');
            }

            return response()->json([
                'connected'   => false,
                'device_name' => null,
            ]);
        }

        if (! $globalCheck) {
            if ((int) $device->status === 1) {
                session(['connected_device' => $device->device_code]);
            } else {
                session()->forget('connected_device');
            }
        }

        return response()->json([
            'connected'   => (int) $device->status === 1,
            'device_name' => $device->device_name ?? null,
        ]);
    }

    public function disconnect(Request $request, $code)
    {
        $globalDisconnect = $request->boolean('global');

        $query = ConnectedDevice::where('device_code', $code);

        if (! $globalDisconnect) {
            $query->where('session_id', session()->getId());
        }

        $updated = $query->update(['status' => 0]);

        session()->forget('connected_device');

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

    public function pullScans(Request $request)
    {
        $device = ConnectedDevice::where('session_id', session()->getId())
            ->where('status', 1)
            ->latest()
            ->first();

        if (! $device) {
            session()->forget('connected_device');

            return response()->json([
                'connected' => false,
                'scans' => [],
            ]);
        }

        session(['connected_device' => $device->device_code]);

        $limit = (int) $request->query('limit', 12);
        $limit = max(1, min($limit, 50));

        $scans = ConnectedDeviceScan::where('device_code', $device->device_code)
            ->whereNull('consumed_at')
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'barcode', 'created_at']);

        if ($scans->isNotEmpty()) {
            ConnectedDeviceScan::whereIn('id', $scans->pluck('id'))
                ->update(['consumed_at' => now()]);
        }

        return response()->json([
            'connected' => true,
            'device_code' => $device->device_code,
            'device_name' => $device->device_name,
            'scans' => $scans,
        ]);
    }
}
