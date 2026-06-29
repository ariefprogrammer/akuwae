<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth'   => 'required|string',
        ]);

        auth()->user()->updatePushSubscription(
            $request->endpoint,
            $request->input('keys.p256dh'),
            $request->input('keys.auth')
        );

        return response()->json(['message' => 'Subscribed']);
    }

    public function destroy(Request $request)
    {
        $request->validate(['endpoint' => 'required|string']);

        auth()->user()->deletePushSubscription($request->endpoint);

        return response()->json(['message' => 'Unsubscribed']);
    }
}