<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentAccount;
use App\Models\Department;
use App\Models\ProjectPayment;


class PaymentDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Admin', 'Project Manager'])) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
    
        $bankAccounts = PaymentAccount::all();
        $departments = Department::all();
    
        // Use selected month, or default to current month
        $month = $request->input('project_month', now()->format('Y-m'));
    
        // Prepare total payment amounts by account
        $paymentQuery = ProjectPayment::select('account_id')
            ->selectRaw('SUM(payment_amount) as total_payment');
    
        $paymentQuery->where('created_at', 'like', "$month%");
    
        $payments = $paymentQuery->groupBy('account_id')
            ->pluck('total_payment', 'account_id');
    
        // Department-wise payments per account
        $departmentPayments = [];
    
        foreach ($bankAccounts as $account) {
            $deptQuery = ProjectPayment::with('project.department')
                ->where('account_id', $account->id)
                ->where('created_at', 'like', "$month%");
    
            $paymentsByDept = $deptQuery->get()
                ->groupBy(fn($payment) => optional($payment->project->department)->name ?? 'Unknown')
                ->map(fn($group) => $group->sum('payment_amount'));
    
            $departmentPayments[$account->id] = $paymentsByDept;
        }
    
        return view('payment-details.index', compact(
            'bankAccounts',
            'departments',
            'payments',
            'departmentPayments'
        ));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
