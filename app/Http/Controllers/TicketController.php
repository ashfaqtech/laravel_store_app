<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tickets = Auth::user()->tickets()
            ->with(['assignedAdmin', 'replies'])
            ->latest()
            ->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket = Auth::user()->tickets()->create($validated);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully.');
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['replies.author', 'assignedAdmin']);
        
        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorize('reply', $ticket);

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $reply = new TicketReply($validated);
        $reply->author()->associate(Auth::user());
        
        $ticket->replies()->save($reply);

        return redirect()->back()
            ->with('success', 'Reply added successfully.');
    }

    public function close(SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update(['status' => 'closed']);

        return redirect()->back()
            ->with('success', 'Ticket closed successfully.');
    }

    public function reopen(SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update(['status' => 'open']);

        return redirect()->back()
            ->with('success', 'Ticket reopened successfully.');
    }
}
