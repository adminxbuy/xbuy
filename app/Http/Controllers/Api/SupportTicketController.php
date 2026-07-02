<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SupportTicketController extends Controller
{
    /**
     * Get user's support tickets.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->with(['order'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Tickets retrieved successfully',
            'data' => $tickets
        ]);
    }

    /**
     * Create a new support ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'order_id' => 'nullable|exists:orders,id',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = DB::transaction(function () use ($request) {
            $ticket = SupportTicket::create([
                'user_id' => $request->user()->id,
                'order_id' => $request->input('order_id'),
                'subject' => $request->input('subject'),
                'message' => $request->input('message'),
                'status' => 'open',
                'priority' => $request->input('priority', 'medium'),
            ]);

            // Save first message
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $request->user()->id,
                'message' => $request->input('message'),
                'attachments' => []
            ]);

            return $ticket;
        });

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => $ticket->load('messages')
        ], 201);
    }

    /**
     * Show ticket details and messages.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)
            ->with(['messages.sender', 'order', 'assignedTo'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Ticket details retrieved successfully',
            'data' => $ticket
        ]);
    }

    /**
     * Reply to a ticket.
     */
    public function reply(int $id, Request $request): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'required|string', // URLs
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $msg = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $request->user()->id,
            'message' => $request->input('message'),
            'attachments' => $request->input('attachments', [])
        ]);

        // Re-open ticket if it was resolved
        if ($ticket->status === 'resolved' || $ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully',
            'data' => $msg
        ], 201);
    }

    /**
     * Admin: Assign ticket.
     */
    public function assign(int $id, Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = SupportTicket::findOrFail($id);
        $ticket->update([
            'assigned_to' => $request->input('assigned_to'),
            'status' => 'in_progress'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket assigned successfully',
            'data' => $ticket->load('assignedTo')
        ]);
    }

    /**
     * Admin: Close ticket.
     */
    public function close(int $id, Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['status' => 'closed']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket closed successfully',
            'data' => $ticket
        ]);
    }
}
