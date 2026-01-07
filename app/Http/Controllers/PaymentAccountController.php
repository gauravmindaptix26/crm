<?php

namespace App\Http\Controllers;

use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentAccountController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Admin', 'Project Manager'])) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }

        // Get the search query from the request
        $search = $request->input('search');

        // Build the query
        $query = PaymentAccount::query();

        // Apply search filter if search term is provided
        if ($search) {
            $query->where('account_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
        }

        // Paginate the results with the selected per-page value (default to 10)
        $perPage = $request->input('per_page', 10);
        $bankAccounts = $query->latest()->paginate($perPage);

        // Append query parameters to pagination links to persist search and per_page
        $bankAccounts->appends(['search' => $search, 'per_page' => $perPage]);

        return view('payment_accounts.index', compact('bankAccounts'));
    }

    public function create()
    {
        return view('payment_accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        // Handle NOT NULL constraints with defaults (if schema not updated to nullable)
        $validated['account_name'] = $validated['account_name'] ?? 'Unnamed Account';
        $validated['account_number'] = $validated['account_number'] ?? 'N/A';
        $validated['created_by'] = auth()->id();

        $bankAccount = PaymentAccount::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bank account added successfully!',
            'data' => $bankAccount
        ]);
    }
    
    public function edit($id)
    {
        $account = PaymentAccount::findOrFail($id);
        return response()->json($account);
    }
    public function update(Request $request, $id)
    {
        $account = PaymentAccount::findOrFail($id);

        $validated = $request->validate([
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        // Handle NOT NULL constraints with defaults (if schema not updated to nullable)
        $validated['account_name'] = $validated['account_name'] ?? 'Unnamed Account';
        $validated['account_number'] = $validated['account_number'] ?? 'N/A';

        $account->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bank account updated successfully!',
            'data' => $account
        ]);
    }

    public function destroy(PaymentAccount $paymentAccount)
    {
        $paymentAccount->delete();
        return redirect()->route('payment_accounts.index')->with('success', 'Payment Account deleted successfully.');
    }
}
