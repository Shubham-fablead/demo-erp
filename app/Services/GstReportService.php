<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TaxRate;
use App\Models\PurchaseInvoice;

class GstReportService
{
    public function getGstr3bData($filter = null)
    {
        // Date range resolution
        [$from, $to] = $this->resolveDateRange($filter);

        // Currency Settings
        $settings = DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Active tax rates
        $activeTaxes = TaxRate::where('status', 'active')->get();
        $totalRatePercent = $activeTaxes->sum('tax_rate');
        $cgstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'CGST')->tax_rate ?? 0;
        $sgstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'SGST')->tax_rate ?? 0;
        $igstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'IGST')->tax_rate ?? 0;

        // 1. Sales Data (Outward Supplies)
        $salesData = $this->getSalesData($from, $to, $cgstRate, $sgstRate, $igstRate, $totalRatePercent);
        
        // 2. Purchase Data (Inward Supplies)
        $purchaseData = $this->getPurchaseData($from, $to, $cgstRate, $sgstRate, $igstRate);
        
        // 3. Sales Return Data
        $salesReturnData = $this->getSalesReturnData($from, $to, $cgstRate, $sgstRate, $igstRate, $totalRatePercent);
        
        // 4. Purchase Return Data
        $purchaseReturnData = $this->getPurchaseReturnData($from, $to, $cgstRate, $sgstRate, $igstRate);

        return [
            'company_name' => 'SNEHTRADING',
            'from_date' => $from ? $from->format('d/m/Y') : '01/04/2025',
            'to_date' => $to ? $to->format('d/m/Y') : '20/08/2025',
            'gstin' => $settings->gstin ?? '',
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            
            // Sales Section
            'sales' => $salesData,
            'sales_return' => $salesReturnData,
            'total_sales' => [
                'taxable_value' => $salesData['total']['taxable_value'] - $salesReturnData['total']['taxable_value'],
                'igst_payable' => $salesData['total']['igst'] - $salesReturnData['total']['igst'],
                'cgst_payable' => $salesData['total']['cgst'] - $salesReturnData['total']['cgst'],
                'sgst_payable' => $salesData['total']['sgst'] - $salesReturnData['total']['sgst']
            ],
            
            // Purchase Section
            'purchase' => $purchaseData,
            'purchase_return' => $purchaseReturnData,
            'total_purchase' => [
                'taxable_value' => $purchaseData['total']['taxable_value'] - $purchaseReturnData['total']['taxable_value'],
                'igst_receivable' => $purchaseData['total']['igst'] - $purchaseReturnData['total']['igst'],
                'cgst_receivable' => $purchaseData['total']['cgst'] - $purchaseReturnData['total']['cgst'],
                'sgst_receivable' => $purchaseData['total']['sgst'] - $purchaseReturnData['total']['sgst']
            ],
            
            // RCM Section
            'rcm_purchase' => [
                'book' => [],
                'total' => [
                    'taxable_value' => 0.00,
                    'igst_payable' => 0.00,
                    'cgst_payable' => 0.00,
                    'sgst_payable' => 0.00
                ]
            ],
            
            // Other Income
            'other_income' => [
                'taxable_value' => 0.00,
                'igst_payable' => 0.00,
                'cgst_payable' => 0.00,
                'sgst_payable' => 0.00
            ],
            
            // Total Payable
            'total_payable' => [
                'taxable_value' => 0.00,
                'igst_payable' => 0.00,
                'cgst_payable' => 0.00,
                'sgst_payable' => 0.00
            ],
            
            // Total Receivable
            'total_receivable' => [
                'taxable_value' => 0.00,
                'igst_receivable' => 0.00,
                'cgst_receivable' => 0.00,
                'sgst_receivable' => 0.00
            ],
            
            // Net Balance
            'net_balance' => [
                'taxable_value' => 0.00,
                'igst' => 0.00,
                'cgst' => 0.00,
                'sgst' => 0.00
            ]
        ];
    }

    private function getSalesData($from, $to, $cgstRate, $sgstRate, $igstRate, $totalRatePercent)
    {
        $salesQuery = Order::with(['orderItems' => function ($q) {
            $q->where('isDeleted', 0);
        }])
        ->where('isDeleted', 0)
        ->where('payment_status', 'completed');

        if ($from && $to) {
            $salesQuery->whereBetween('created_at', [$from, $to]);
        }

        $sales = $salesQuery->get();
        
        $book = [];
        $totalTaxable = 0.0;
        $totalCGST = 0.0;
        $totalSGST = 0.0;
        $totalIGST = 0.0;

        foreach ($sales as $order) {
            $subtotal = $order->orderItems->sum(function ($item) {
                return (float)$item->price * (float)$item->quantity;
            });

            $discountPercent = (float)($order->discount ?? 0);
            $discountAmount = ($subtotal * $discountPercent) / 100.0;
            $taxableAmount = max(0, $subtotal - $discountAmount);

            $cgstAmount = $cgstRate > 0 ? ($taxableAmount * $cgstRate) / 100.0 : 0.0;
            $sgstAmount = $sgstRate > 0 ? ($taxableAmount * $sgstRate) / 100.0 : 0.0;
            $igstAmount = $igstRate > 0 ? ($taxableAmount * $igstRate) / 100.0 : 0.0;

            if ($cgstRate === 0 && $sgstRate === 0 && $igstRate === 0 && $totalRatePercent > 0) {
                $totalTax = ($taxableAmount * $totalRatePercent) / 100.0;
                $cgstAmount = $totalTax / 2.0;
                $sgstAmount = $totalTax / 2.0;
            }

            $book[] = [
                'invoice_no' => $order->invoice_number ?? $order->id,
                'date' => $order->created_at->format('d/m/Y'),
                'taxable_value' => $taxableAmount,
                'igst' => $igstAmount,
                'cgst' => $cgstAmount,
                'sgst' => $sgstAmount
            ];

            $totalTaxable += $taxableAmount;
            $totalCGST += $cgstAmount;
            $totalSGST += $sgstAmount;
            $totalIGST += $igstAmount;
        }

        return [
            'book' => $book,
            'total' => [
                'taxable_value' => round($totalTaxable, 2),
                'igst' => round($totalIGST, 2),
                'cgst' => round($totalCGST, 2),
                'sgst' => round($totalSGST, 2)
            ]
        ];
    }

    private function getPurchaseData($from, $to, $cgstRate, $sgstRate, $igstRate)
    {
        $purchaseQuery = PurchaseInvoice::query()->where('isDeleted', 0);
        if ($from && $to) {
            $purchaseQuery->whereBetween('created_at', [$from, $to]);
        }
        $purchaseInvoices = $purchaseQuery->get();

        $book = [];
        $totalTaxable = 0.0;
        $totalCGST = 0.0;
        $totalSGST = 0.0;
        $totalIGST = 0.0;

        foreach ($purchaseInvoices as $pi) {
            $taxableAmount = (float)($pi->total_amount ?? 0);
            $cgstAmount = 0.0;
            $sgstAmount = 0.0;
            $igstAmount = 0.0;

            if (!empty($pi->taxes)) {
                $taxes = json_decode($pi->taxes, true);
                if (is_array($taxes)) {
                    foreach ($taxes as $tax) {
                        $name = strtoupper(trim($tax['name'] ?? $tax['tax_name'] ?? ''));
                        $amount = (float)($tax['amount'] ?? 0);
                        
                        if ($name === '' && isset($tax['tax_id'])) {
                            $tr = TaxRate::find($tax['tax_id']);
                            if ($tr) $name = strtoupper(trim($tr->tax_name));
                        }
                        if ($name === '' && isset($tax['id'])) {
                            $tr = TaxRate::find($tax['id']);
                            if ($tr) $name = strtoupper(trim($tr->tax_name));
                        }

                        if ($name === 'CGST') $cgstAmount += $amount;
                        elseif ($name === 'SGST') $sgstAmount += $amount;
                        elseif ($name === 'IGST') $igstAmount += $amount;
                        else {
                            if ($name === '' && $cgstRate > 0 && $sgstRate > 0 && $igstRate === 0) {
                                $cgstAmount += $amount / 2.0;
                                $sgstAmount += $amount / 2.0;
                            }
                        }
                    }
                }
            }

            $book[] = [
                'invoice_no' => $pi->invoice_number ?? $pi->id,
                'date' => $pi->created_at->format('d/m/Y'),
                'taxable_value' => $taxableAmount,
                'igst_receivable' => $igstAmount,
                'cgst_receivable' => $cgstAmount,
                'sgst_receivable' => $sgstAmount
            ];

            $totalTaxable += $taxableAmount;
            $totalCGST += $cgstAmount;
            $totalSGST += $sgstAmount;
            $totalIGST += $igstAmount;
        }

        return [
            'book' => $book,
            'total' => [
                'taxable_value' => round($totalTaxable, 2),
                'igst' => round($totalIGST, 2),
                'cgst' => round($totalCGST, 2),
                'sgst' => round($totalSGST, 2)
            ]
        ];
    }

    private function getSalesReturnData($from, $to, $cgstRate, $sgstRate, $igstRate, $totalRatePercent)
    {
        // This would need to be implemented based on your sales return structure
        // For now, returning empty data
        return [
            'book' => [],
            'total' => [
                'taxable_value' => 0.00,
                'igst' => 0.00,
                'cgst' => 0.00,
                'sgst' => 0.00
            ]
        ];
    }

    private function getPurchaseReturnData($from, $to, $cgstRate, $sgstRate, $igstRate)
    {
        // This would need to be implemented based on your purchase return structure
        // For now, returning empty data
        return [
            'book' => [],
            'total' => [
                'taxable_value' => 0.00,
                'igst' => 0.00,
                'cgst' => 0.00,
                'sgst' => 0.00
            ]
        ];
    }

    private function resolveDateRange($filter)
    {
        $now = Carbon::now();
        $from = null;
        $to = null;

        switch ($filter) {
            case 'this_week':
                $from = $now->copy()->startOfWeek();
                $to = $now->copy()->endOfWeek();
                break;
            case 'this_month':
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                break;
            case 'last_6_months':
                $from = $now->copy()->subMonths(6)->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case 'this_year':
                $from = $now->copy()->startOfYear();
                $to = $now->copy()->endOfYear();
                break;
            case 'previous_year':
                $from = $now->copy()->subYear()->startOfYear();
                $to = $now->copy()->subYear()->endOfYear();
                break;
            default:
                // Default to current financial year (April to March)
                $currentYear = $now->year;
                if ($now->month < 4) {
                    $currentYear--;
                }
                $from = Carbon::create($currentYear, 4, 1)->startOfDay();
                $to = Carbon::create($currentYear + 1, 3, 31)->endOfDay();
        }
        return [$from, $to];
    }
}
