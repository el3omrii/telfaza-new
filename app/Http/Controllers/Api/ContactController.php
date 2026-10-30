<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function sendmail(Request $request): JsonResponse
    {
        $validationSecret = config('contact.validation_secret');

        if (! is_string($validationSecret) || $validationSecret === '') {
            return response()->json([
                'error' => 'The contact form is temporarily unavailable.',
            ], 503);
        }

        $providedSecret = $request->header('X-Validation-Secret');

        if (! is_string($providedSecret) || ! hash_equals($validationSecret, $providedSecret)) {
            return response()->json([
                'error' => 'Unauthorized.',
            ], 401);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'subject' => ['required', 'string', 'max:150', 'not_regex:/[\r\n]/'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $recipient = config('contact.mail_to');

        if (! is_string($recipient) || $recipient === '') {
            return response()->json([
                'error' => 'The contact form is temporarily unavailable.',
            ], 503);
        }

        Mail::to($recipient)->send(
            (new ContactMessage(
                contactEmail: $validated['email'],
                contactSubject: $validated['subject'],
                messageBody: $validated['body'],
            ))->replyTo($validated['email'])
        );

        return response()->json(['ok' => true]);
    }
}
