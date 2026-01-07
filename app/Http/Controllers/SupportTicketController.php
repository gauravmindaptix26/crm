<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketAssignedMail;
use App\Mail\TicketReplyMail;



class SupportTicketController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Fetch all users for the "Assign To" dropdown
        $users = User::all();

        // Fetch tickets based on role with pagination
        if ($user->hasRole('Admin') || $user->hasRole('HR')) {
            // Admins and HR see all tickets
            $tickets = SupportTicket::latest()->paginate(10);
        } else {
            // Assigned to the user OR created by the user
            $tickets = SupportTicket::where(function ($query) use ($user) {
                $query->where('assigned_to', $user->id)
                      ->orWhere('user_id', $user->id);
            })->latest()->paginate(10);
        }

        return view('support_tickets.index', compact('tickets', 'users'));
    }

    // Other methods (store, show, etc.) remain unchanged

    


public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'priority' => 'required|in:Low,Medium,High',
        'assigned_to' => 'nullable|exists:users,id',
    ]);

    $ticket = SupportTicket::create([
        'user_id' => Auth::id(),
        'title' => $request->title,
        'description' => $request->description,
        'priority' => $request->priority,
        'status' => 'Open',
        'assigned_to' => $request->assigned_to,
    ]);
       // ✅ Send email if assigned_to is set
    if ($ticket->assigned_to) {
        $assignedUser = \App\Models\User::find($ticket->assigned_to);
        if ($assignedUser && $assignedUser->email) {
            Mail::to($assignedUser->email)->send(new TicketAssignedMail($ticket));
        }
    }

    return redirect()->route('support-tickets.index')->with('success', 'Ticket created successfully.');
}


public function show($id)
{
    $ticket = SupportTicket::with(['assignedTo', 'replies.user'])->findOrFail($id);
    $users = User::all();

    return view('support_tickets.show', compact('ticket','users'));
}

// SupportTicketController.php

public function reply(Request $request, $id)
{
    $ticket = SupportTicket::findOrFail($id);
    $newStatus = $request->status;
    $oldStatus = $ticket->status;

    // Only require message if changing from open → closed
    if ($oldStatus === 'open' && $newStatus === 'closed' && empty($request->reply_ticket_description)) {
        return redirect()->back()->withErrors([
            'reply_ticket_description' => 'Please enter a message when closing the ticket.',
        ])->withInput();
    }

    // Update status
    $ticket->status = $newStatus;
    $ticket->save();

    // Only save reply if a message was entered
    if (!empty($request->reply_ticket_description)) {
        $reply=$ticket->replies()->create([
            'user_id' => auth()->id(),
            'message' => $request->reply_ticket_description,
        ]);
    }
// ✅ Send email to the user who created the ticket
$ticketCreator = $ticket->user;
if ($ticketCreator && $ticketCreator->email) {
    Mail::to($ticketCreator->email)->send(new TicketReplyMail($ticket, $reply));
}

    return redirect()->back()->with('success', 'Ticket updated successfully.');
}

}

