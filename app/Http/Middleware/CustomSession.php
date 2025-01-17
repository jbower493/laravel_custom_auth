<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\SessionTokenTrait;
use App\Models\CustomSession as CustomSessionModel;

class CustomSession
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
        $token = $this->retrieveToken($request);

        $session = $this->retrieveSessionByToken($token);

        if ($session) {
            $request->attributes->set('custom_session', $session);
        }

        $response = $next($request);

        return $response;
    }

    private function retrieveToken(Request $request)
    {
        if ($this->checkIsFromMobileApp($request)) {
            return $request->bearerToken();
        }

        return $request->cookie('custom_session');
    }

    private function retrieveSessionByToken($sessionId)
    {
        if (!$sessionId) {
            return null;
        }

        return CustomSessionModel::find($sessionId);
    }
}
