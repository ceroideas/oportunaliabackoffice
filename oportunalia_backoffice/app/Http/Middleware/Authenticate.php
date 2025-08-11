<?php

namespace App\Http\Middleware;

use App\Models\Responses\ErrorHandlerResponse;
use App\Models\User;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Tymon\JWTAuth\Contracts\Providers\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return array|string|null
     */
    public function handle($request, Closure $next)
    {

        try {


            if (!empty(trim($request->header('authorization')))) {

                $is_exists = User::where('id', auth()->user()->id)->exists();
                if ($is_exists) {
                    return $next($request);
                }
            }
        } catch (\Exception $e){}
        return response()->json([
            'messages' => ['Unauthorized'],
            'code' => 401
        ], 401);

    }
}
