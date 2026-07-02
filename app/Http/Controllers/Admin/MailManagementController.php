<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\MailCampaign;
use App\Models\User;
use App\Mail\DynamicCampaignMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailManagementController extends Controller
{
    public function index(Request $request)
    {
        // Subscribers Query
        $subQuery = Subscriber::query();
        if ($request->filled('search')) {
            $search = $request->input('search');
            $subQuery->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $subQuery->where('status', $request->input('status'));
        }
        if ($request->filled('source')) {
            $subQuery->where('source', $request->input('source'));
        }
        if ($request->filled('timeline')) {
            $timeline = $request->input('timeline');
            if ($timeline === '7_days') {
                $subQuery->where('created_at', '>=', now()->subDays(7));
            } elseif ($timeline === '15_days') {
                $subQuery->where('created_at', '>=', now()->subDays(15));
            } elseif ($timeline === '30_days') {
                $subQuery->where('created_at', '>=', now()->subDays(30));
            } elseif ($timeline === '90_days') {
                $subQuery->where('created_at', '>=', now()->subDays(90));
            } elseif ($timeline === 'custom') {
                if ($request->filled('start_date')) {
                    $subQuery->whereDate('created_at', '>=', $request->input('start_date'));
                }
                if ($request->filled('end_date')) {
                    $subQuery->whereDate('created_at', '<=', $request->input('end_date'));
                }
            }
        }
        Subscriber::onlyTrashed()->where('deleted_at', '<', now()->subDays(30))->forceDelete();
        $subscribers = $subQuery->orderBy('created_at', 'desc')->paginate(15, ['*'], 'subscribers_page');
 
        // Campaigns Query
        $campaigns = MailCampaign::orderBy('created_at', 'desc')->paginate(15, ['*'], 'campaigns_page');
 
        // Sources list
        $sources = Subscriber::distinct()->pluck('source');
 
        $settings = \App\Models\SiteSetting::all();
        $trashedSubscribers = Subscriber::onlyTrashed()->get();
 
        return view('admin.mails.index', compact('subscribers', 'campaigns', 'sources', 'settings', 'trashedSubscribers'));
    }

    public function storeSubscriber(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers,email',
            'name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
        ]);

        Subscriber::create([
            'email' => $request->input('email'),
            'name' => $request->input('name'),
            'source' => $request->input('source', 'manual'),
            'status' => 'active',
        ]);

        return redirect()->route('admin.mails.index')->with('success', 'Subscriber added successfully.');
    }

    public function destroySubscriber($id)
    {
        $subscriber = Subscriber::findOrFail($id);
        $subscriber->delete();

        return redirect()->route('admin.mails.index')->with('success', 'Subscriber deleted.');
    }

    public function bulkActionSubscribers(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No subscribers selected.');
        }

        switch ($action) {
            case 'activate':
                Subscriber::whereIn('id', $ids)->update(['status' => 'active', 'unsubscribed_at' => null]);
                $msg = 'Selected subscribers activated.';
                break;
            case 'unsubscribe':
                Subscriber::whereIn('id', $ids)->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
                $msg = 'Selected subscribers marked as unsubscribed.';
                break;
            case 'delete':
                Subscriber::whereIn('id', $ids)->delete();
                $msg = 'Selected subscribers deleted.';
                break;
            default:
                return redirect()->back()->with('error', 'Invalid action.');
        }

        return redirect()->back()->with('success', $msg);
    }

    public function sendMail(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'target_group' => 'required|string',
            'type' => 'required|string',
            'scheduled_at' => 'nullable|date',
        ]);

        $target = $request->input('target_group');
        $type = $request->input('type');
        $subject = $request->input('subject');
        $body = $request->input('body');

        // Check if scheduled for the future
        if ($request->filled('scheduled_at') && \Carbon\Carbon::parse($request->input('scheduled_at'))->isFuture()) {
            MailCampaign::create([
                'subject' => $subject,
                'body' => $body,
                'target_group' => $target,
                'type' => $type,
                'status' => 'scheduled',
                'recipient_count' => 0,
                'scheduled_at' => \Carbon\Carbon::parse($request->input('scheduled_at')),
            ]);

            return redirect()->route('admin.mails.index', ['tab' => 'history'])
                ->with('success', "Campaign scheduled successfully for " . $request->input('scheduled_at') . ".");
        }

        // Send instantly
        $campaign = MailCampaign::create([
            'subject' => $subject,
            'body' => $body,
            'target_group' => $target,
            'type' => $type,
            'status' => 'sending',
            'recipient_count' => 0,
            'sent_at' => now(),
        ]);

        self::executeCampaign($campaign);

        if ($campaign->status === 'failed') {
            return redirect()->route('admin.mails.index', ['tab' => 'history'])
                ->with('error', "Campaign sending failed completely. Please verify your SMTP settings.");
        } elseif ($campaign->status === 'partial') {
            return redirect()->route('admin.mails.index', ['tab' => 'history'])
                ->with('info', "Campaign partially sent. Check SMTP logs.");
        }

        return redirect()->route('admin.mails.index', ['tab' => 'history'])
            ->with('success', "Campaign sent successfully to {$campaign->recipient_count} recipients.");
    }

    public static function executeCampaign(MailCampaign $campaign)
    {
        $target = $campaign->target_group;
        $subject = $campaign->subject;
        $body = $campaign->body;

        $recipients = collect();

        switch ($target) {
            case 'all_subscribers':
                $recipients = Subscriber::active()->get(['email', 'name', 'token']);
                break;
            case 'all_users':
                $recipients = User::get(['email', 'name'])->map(function($u) {
                    $u->token = 'user';
                    return $u;
                });
                break;
            case 'sellers':
                $recipients = User::whereIn('role', ['seller', 'hybrid'])->get(['email', 'name'])->map(function($u) {
                    $u->token = 'user';
                    return $u;
                });
                break;
            case 'buyers':
                $recipients = User::whereIn('role', ['buyer', 'user', 'hybrid'])->get(['email', 'name'])->map(function($u) {
                    $u->token = 'user';
                    return $u;
                });
                break;
        }

        if ($recipients->isEmpty()) {
            $campaign->update([
                'status' => 'failed',
                'sent_at' => now(),
            ]);
            return;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $recipient) {
            $unsubscribeUrl = route('subscribers.unsubscribe', ['token' => $recipient->token ?? 'user']);

            try {
                Mail::to($recipient->email)->send(new DynamicCampaignMail($subject, $body, $unsubscribeUrl));
                $sentCount++;
            } catch (\Exception $e) {
                Log::error("Failed to send campaign mail to {$recipient->email}: " . $e->getMessage());
                $failedCount++;
            }
        }

        $campaign->update([
            'status' => $failedCount === 0 ? 'sent' : ($sentCount > 0 ? 'partial' : 'failed'),
            'recipient_count' => $sentCount,
            'sent_at' => now(),
        ]);
    }

    public function searchUsers(Request $request)
    {
        $search = $request->input('search');
        $subscribedEmails = Subscriber::pluck('email')->toArray();

        $usersQuery = User::whereNotIn('email', $subscribedEmails);

        if (!empty($search)) {
            $usersQuery->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->limit(20)->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'email'
        ]);

        $emails = $request->input('emails');
        $added = 0;

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            $exists = Subscriber::where('email', $email)->exists();
            if (!$exists) {
                Subscriber::create([
                    'email' => $email,
                    'name' => $user ? $user->name : null,
                    'source' => 'admin_user_import',
                    'status' => 'active',
                ]);
                $added++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully added {$added} subscribers.",
        ]);
    }
}

