<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MerchantAccount;
use Illuminate\Http\Request;

class AdminMerchantController extends Controller
{
    public function index()
    {
        $merchants = MerchantAccount::orderBy('priority', 'desc')->orderBy('id', 'desc')->get();
        return view('admin.merchants.index', compact('merchants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'account_holder' => 'nullable|string|max:255',
            'upi_id' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:255',
            'daily_limit' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:1|max:100',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $qrPath = null;
        if ($request->hasFile('qr_image')) {
            $file = $request->file('qr_image');
            $filename = 'qr_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/qr');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $qrPath = 'uploads/qr/' . $filename;
        }

        MerchantAccount::create([
            'name' => $request->input('name'),
            'account_holder' => $request->input('account_holder'),
            'upi_id' => $request->input('upi_id'),
            'qr_image' => $qrPath,
            'bank_name' => $request->input('bank_name'),
            'account_number' => $request->input('account_number'),
            'ifsc' => $request->input('ifsc'),
            'daily_limit' => (float)$request->input('daily_limit'),
            'priority' => (int)$request->input('priority'),
            'status' => 'active',
            'supported_payment_types' => ['upi', 'bank_transfer', 'qr'],
        ]);

        return redirect()->back()->with('success', 'Merchant account created successfully!');
    }

    public function update(Request $request, MerchantAccount $merchant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'account_holder' => 'nullable|string|max:255',
            'upi_id' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:255',
            'daily_limit' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:1|max:100',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = [
            'name' => $request->input('name'),
            'account_holder' => $request->input('account_holder'),
            'upi_id' => $request->input('upi_id'),
            'bank_name' => $request->input('bank_name'),
            'account_number' => $request->input('account_number'),
            'ifsc' => $request->input('ifsc'),
            'daily_limit' => (float)$request->input('daily_limit'),
            'priority' => (int)$request->input('priority'),
        ];

        if ($request->hasFile('qr_image')) {
            $file = $request->file('qr_image');
            $filename = 'qr_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/qr');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $data['qr_image'] = 'uploads/qr/' . $filename;
        }

        $merchant->update($data);

        return redirect()->back()->with('success', 'Merchant account updated successfully!');
    }

    public function toggleStatus(MerchantAccount $merchant)
    {
        $merchant->status = $merchant->status === 'active' ? 'inactive' : 'active';
        $merchant->save();

        return redirect()->back()->with('success', "Merchant {$merchant->name} status changed to {$merchant->status}.");
    }

    public function resetDailyTotals()
    {
        MerchantAccount::query()->update(['current_daily_total' => 0.00]);
        return redirect()->back()->with('success', 'All merchant daily totals reset to ₹0.00!');
    }
}
