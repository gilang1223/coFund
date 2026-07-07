<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VerificationController extends ApiController
{
    /**
     * Send email verification notification to the authenticated user.
     */
    public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->sendResponse('Email sudah terverifikasi.', [
                'verified' => true,
            ]);
        }

        $user->sendEmailVerificationNotification();

        return $this->sendResponse('Email verifikasi telah dikirim. Silakan cek inbox Anda.');
    }

    /**
     * Verify email using the signed URL from the email.
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect(config('app.frontend_url', config('app.url')) . '/login?verified=invalid');
        }

        if (!URL::hasValidSignature($request)) {
            return redirect(config('app.frontend_url', config('app.url')) . '/login?verified=invalid');
        }

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect(config('app.frontend_url', config('app.url')) . '/login?verified=invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(config('app.frontend_url', config('app.url')) . '/login?verified=already');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Redirect to login page with success message
        return redirect(config('app.frontend_url', config('app.url')) . '/login?verified=success');
    }

    /**
     * Check if the authenticated user's email is verified.
     */
    public function status(Request $request)
    {
        $user = $request->user();

        return $this->sendResponse('Verification status retrieved.', [
            'verified' => $user->hasVerifiedEmail(),
        ]);
    }
}
