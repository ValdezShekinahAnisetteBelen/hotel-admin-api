<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:1000',
        ]);

        $phone = $validated['phone'] ?? 'N/A';

        try {
            Mail::raw("
Name: {$validated['name']}
Email: {$validated['email']}
Phone: {$phone}
Subject: {$validated['subject']}

Message:
{$validated['message']}
", function ($msg) use ($validated) {
                $msg->to('hotel.123makati@gmail.com')
                    ->subject('New Contact Form Submission: ' . $validated['subject']);
            });

            // ✅ If successful
            Log::info('📨 Contact form email sent successfully', ['from' => $validated['email']]);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully.'
            ]);

        } catch (Exception $e) {
            // ❌ Log error details
            Log::error('❌ Failed to send contact email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again later.'
            ], 500);
        }
    }
}
