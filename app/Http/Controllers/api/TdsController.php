<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TdsController extends Controller
{
    private string $storePath = 'tds/entries.json';

    private function loadEntries(): array
    {
        if (!Storage::exists($this->storePath)) {
            return [];
        }
        $json = Storage::get($this->storePath);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    private function saveEntries(array $entries): void
    {
        Storage::makeDirectory('tds');
        Storage::put($this->storePath, json_encode(array_values($entries), JSON_PRETTY_PRINT));
    }

    // Create a TDS entry
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_name'   => 'required|string|max:255',
            'vendor_pan'    => 'required|string|max:10',
            'section'       => 'nullable|string|max:50', // e.g., 194C / 194Q etc.
            'base_amount'   => 'required|numeric|min:0',
            'payment_date'  => 'required|date',
            'reference_no'  => 'nullable|string|max:100',
            'narration'     => 'nullable|string|max:500',
            'rate_mode'     => 'nullable|in:auto,manual',
            'tds_rate'      => 'nullable|numeric|min:0',
        ]);

        // Determine rate
        $rateMode = $validated['rate_mode'] ?? 'auto';
        $rate = 0.0;
        if ($rateMode === 'manual' && isset($validated['tds_rate'])) {
            $rate = (float)$validated['tds_rate'];
        } else {
            // Auto by PAN type: P/H -> 1%, C/F -> 2%
            $first = strtoupper(substr($validated['vendor_pan'], 0, 1));
            if (in_array($first, ['P', 'H'])) {
                $rate = 1.0;
            } elseif (in_array($first, ['C', 'F'])) {
                $rate = 2.0;
            } else {
                // Default 2% if unknown
                $rate = 2.0;
            }
        }

        $base = (float)$validated['base_amount'];
        $tdsAmount = round($base * $rate / 100, 2);

        $entries = $this->loadEntries();
        $entry = [
            'id'           => time() . rand(1000, 9999),
            'vendor_name'  => $validated['vendor_name'],
            'vendor_pan'   => strtoupper($validated['vendor_pan']),
            'section'      => $validated['section'] ?? '',
            'base_amount'  => round($base, 2),
            'tds_rate'     => $rate,
            'tds_amount'   => $tdsAmount,
            'payment_date' => $validated['payment_date'],
            'reference_no' => $validated['reference_no'] ?? '',
            'narration'    => $validated['narration'] ?? '',
            'created_at'   => now()->toDateTimeString(),
        ];
        $entries[] = $entry;
        $this->saveEntries($entries);

        return response()->json(['success' => true, 'data' => $entry]);
    }

    // List with optional filters
    public function list(Request $request)
    {
        $entries = collect($this->loadEntries());
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = $request->input('from_date');
            $to   = $request->input('to_date');
            $entries = $entries->filter(function ($e) use ($from, $to) {
                return ($e['payment_date'] >= $from && $e['payment_date'] <= $to);
            });
        }

        // Currency settings
        $settings = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $totalBase = round($entries->sum('base_amount'), 2);
        $totalTds  = round($entries->sum('tds_amount'), 2);

        return response()->json([
            'success' => true,
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'total_base' => $totalBase,
            'total_tds' => $totalTds,
            'data' => $entries->values()->all(),
        ]);
    }

    // Export CSV for ids or date range
    public function export(Request $request)
    {
        $entries = collect($this->loadEntries());

        if ($request->filled('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $entries = $entries->whereIn('id', $ids);
        } elseif ($request->filled('from_date') && $request->filled('to_date')) {
            $from = $request->input('from_date');
            $to   = $request->input('to_date');
            $entries = $entries->filter(function ($e) use ($from, $to) {
                return ($e['payment_date'] >= $from && $e['payment_date'] <= $to);
            });
        }

        if ($entries->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No TDS entries to export'], 404);
        }

        $filename = 'tds_report_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/public/exports/' . $filename);
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        $file = fopen($filepath, 'w');
        fwrite($file, "sep=,\r\n");
        $headers = ['Vendor Name','PAN','Section','Base Amount','TDS Rate (%)','TDS Amount','Payment Date','Reference No','Narration'];
        fputcsv($file, $headers);
        foreach ($entries as $e) {
            fputcsv($file, [
                $e['vendor_name'],
                $e['vendor_pan'],
                $e['section'],
                number_format((float)$e['base_amount'], 2, '.', ''),
                number_format((float)$e['tds_rate'], 2, '.', ''),
                number_format((float)$e['tds_amount'], 2, '.', ''),
                $e['payment_date'],
                $e['reference_no'],
                $e['narration'],
            ]);
        }
        fclose($file);
        $downloadUrl = url('storage/exports/' . $filename);

        return response()->json(['success' => true, 'download_url' => $downloadUrl, 'filename' => $filename]);
    }
}
