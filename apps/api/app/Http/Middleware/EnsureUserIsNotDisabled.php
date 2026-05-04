<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotDisabled
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $user = $request->user()?->fresh();

        if ($user?->disabled_at !== null) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            Auth::forgetGuards();

            return ApiResponse::error(
                'Account is disabled',
                [
                    'account' => ['This account has been disabled.'],
                ],
                403
            );
        }

        return $next($request);
    }
}
