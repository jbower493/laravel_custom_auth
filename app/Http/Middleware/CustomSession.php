<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\SessionTokenTrait;
use App\Models\CustomSession as CustomSessionModel;
use Carbon\Carbon;

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
        $session = $this->deleteExpiredSession($session);

        if ($session) {
            $session = $this->extendActiveSession($session);
            $request->attributes->set('custom_session', $session);
        }

        $response = $next($request);

        return $response;
    }

    private function deleteExpiredSession(CustomSessionModel | null $session): CustomSessionModel | null
    {
        if (!$session) {
            return null;
        }

        $expiresAt = Carbon::parse($session->expires_at);

        if (Carbon::now()->isAfter($expiresAt)) {
            $session->delete();
            return null;
        }

        return $session;
    }

    private function extendActiveSession(CustomSessionModel $session): CustomSessionModel
    {
        $session->expires_at = Carbon::now()->add(CustomSessionModel::SESSION_LIFETIME_HOURS, 'hour');
        $session->save();
        return $session;
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
