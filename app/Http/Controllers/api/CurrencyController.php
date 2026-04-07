<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    // Fetch all tax rates
    public function index()
    {
        // Fetch the tax rates sorted by created_at in descending order
        $taxRates = Currency::orderBy('created_at', 'desc')->get(); // Call get() to fetch data

        return response()->json(['status' => true, 'taxRates' => $taxRates], 200);
    }


    // Store a new tax rate
    public function store(Request $request)
    {
        $request->validate([
            'currency_name' => 'required|string|max:255',
            'currency_symbol' => 'required|string|max:10',
        ]);

        $currency = Currency::create([
            'currency_name' => $request->currency_name,
            'currency_symbol' => $request->currency_symbol,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Currency added successfully!',
            'currency' => $currency
        ], 201);
    }

    // Fetch a specific tax rate for editing
    public function edit($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'status' => false,
                'message' => 'Currency not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'currency' => $currency
        ], 200);
    }

    // Update tax rate
    public function update(Request $request, $id)
    {
        $request->validate([
            'currency_name' => 'required|string|max:255',
            'currency_symbol' => 'required|string|max:10',
        ]);

        $currency = Currency::find($id);
        if (!$currency) {
            return response()->json([
                'status' => false,
                'message' => 'Currency not found'
            ], 404);
        }

        $currency->update([
            'currency_name' => $request->currency_name,
            'currency_symbol' => $request->currency_symbol,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Currency updated successfully!',
            'currency' => $currency
        ], 200);
    }
    // Delete tax rate
    public function destroy($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'status' => false,
                'message' => 'Currency not found'
            ], 404);
        }

        $currency->delete();

        return response()->json([
            'status' => true,
            'message' => 'Currency deleted successfully!'
        ], 200);
    }
}
