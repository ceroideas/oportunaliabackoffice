<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AdminContestMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (JWTAuth::parseToken()->authenticate()) {
                $userData = auth()->user();
                if ($userData->role_id == Role::ID_ADMIN_CONTEST &&
                    $userData->confirmed == 1) {
                    return $next($request);
                }
            }
        } catch (Exception $e) {
        }
        return response()->json([
            'messages' => ['Unauthorized'],
            'code' => 401
        ], 401);
    }
}
