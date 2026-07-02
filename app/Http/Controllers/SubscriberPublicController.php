<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberPublicController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
        ]);

        $email = $request->input('email');

        // Check if subscriber exists
        $subscriber = Subscriber::where('email', $email)->first();

        if ($subscriber) {
            if ($subscriber->status === 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already subscribed to our list!'
                ]);
            }

            // Reactivate subscriber
            $subscriber->update([
                'status' => 'active',
                'unsubscribed_at' => null,
                'name' => $request->input('name') ?? $subscriber->name,
                'source' => $request->input('source', 'reactivate'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Welcome back! Your subscription has been reactivated successfully.'
            ]);
        }

        // Create new subscriber
        Subscriber::create([
            'email' => $email,
            'name' => $request->input('name'),
            'source' => $request->input('source', 'footer_newsletter'),
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Awesome! You have successfully subscribed for mail & sales alerts.'
        ]);
    }

    public function unsubscribe($token)
    {
        if ($token === 'user') {
            // General unsubscribe page for users
            return view('subscribers.unsubscribed_message', [
                'message' => 'To update your profile email alerts, please go to your account settings.'
            ]);
        }

        $subscriber = Subscriber::where('token', $token)->first();

        if (!$subscriber) {
            return view('subscribers.unsubscribed_message', [
                'message' => 'Invalid or expired unsubscribe token. If you are still receiving mails, contact support.'
            ]);
        }

        if ($subscriber->status === 'unsubscribed') {
            return view('subscribers.unsubscribed_message', [
                'message' => 'You have already unsubscribed from our lists.'
            ]);
        }

        $subscriber->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return view('subscribers.unsubscribed_message', [
            'message' => 'You have successfully unsubscribed from all marketing, newsletters, and sales alerts. We will miss you!'
        ]);
    }
}
