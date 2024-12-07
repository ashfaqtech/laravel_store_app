<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedAdmin', 'replies'])
            ->latest();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by assigned admin
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Search by ticket number or subject
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'assignedAdmin', 'replies.author']);
        
        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $reply = new TicketReply($validated);
        $reply->author()->associate(Auth::guard('admin')->user());
        
        $ticket->replies()->save($reply);

        // Update ticket status if needed
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return redirect()->back()
            ->with('success', 'Reply added successfully.');
    }

    public function assign(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:admins,id',
        ]);

        $ticket->update([
            'assigned_to' => $validated['admin_id'],
            'status' => 'in_progress',
        ]);

        return redirect()->back()
            ->with('success', 'Ticket assigned successfully.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $ticket->update($validated);

        return redirect()->back()
            ->with('success', 'Ticket status updated successfully.');
    }

    public function updatePriority(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket->update($validated);

        return redirect()->back()
            ->with('success', 'Ticket priority updated successfully.');
    }
}
