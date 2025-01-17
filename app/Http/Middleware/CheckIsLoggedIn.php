<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\SessionTokenTrait;
use App\Models\User;

class CheckIsLoggedIn
{
    use SessionTokenTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $session = $request->attributes->get('custom_session');
        $loggedInUser = $session ? User::find($session->user_id) : null;

        if (!$loggedInUser) {
            return response([
                'errors' => ['No user is currently logged in.']
            ], 401);
        }

        $request->attributes->set('logged_in_user', $loggedInUser);

        return $next($request);
    }
}
