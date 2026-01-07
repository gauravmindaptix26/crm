<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPayment;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProjectPaymentController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->query('project_id');

        // Validate that project_id is provided
        if (!$projectId) {
            abort(400, 'Project ID is required.');
        }

        // Get the project or fail if not found
        $project = Project::findOrFail($projectId);

        // Get paginated payments related to this project
        $payments = ProjectPayment::with('account') // eager load related account if needed
            ->where('project_id', $projectId)
            ->latest()
            ->paginate(10); // paginate instead of get()

        // Get all accounts
        $accounts = PaymentAccount::all();
       // dd($accounts);

        return view('project_payments.index', compact('project', 'payments', 'accounts'));
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'project_id' => 'nullable|exists:projects,id',
                'account_id' => 'nullable|exists:payment_accounts,id',
                'payment_amount' => 'required|numeric',
                'commission_amount' => 'nullable|numeric',
                'payment_month' => 'required|date',
                'payment_details' => 'nullable|string',
                'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        }

        $data = $request->only([
            'project_id',
            'account_id',
            'payment_amount',
            'commission_amount',
            'payment_month',
            'payment_details',
        ]);

        if ($request->hasFile('screenshot')) {
            $data['screenshot'] = $request->file('screenshot')->store('payments', 'public');
        }

        $data['created_by'] = Auth::id();

        $payment = ProjectPayment::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Payment added successfully!',
            'data' => $payment,
        ], 201);
    }
    public function destroy(ProjectPayment $projectPayment)
    {
        if ($projectPayment->screenshot) {
            Storage::disk('public')->delete($projectPayment->screenshot);
        }

        $projectPayment->delete();

        return redirect()->back()->with('success', 'Payment deleted successfully.');
    }
}
