<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::find($id);

        if (! $request->hasValidSignature() || ! $user || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return ApiResponse::error(
                'Invalid verification link',
                [
                    'verification' => ['The email verification link is invalid or has expired.'],
                ],
                403
            );
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(
                null,
                'Email is already verified'
            );
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return ApiResponse::success(
            null,
            'Email verified successfully'
        );
    }

    public function resend(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(
                null,
                'Email is already verified'
            );
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::success(
            null,
            'Verification email sent'
        );
    }
}
