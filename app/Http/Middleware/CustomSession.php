<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CustomSession as CustomSessionModel;

class CustomSession
{
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

        $session = null;

        if ($token) {
            $session = $this->retrieveSessionByToken($token);
        } else {
            $session = $this->getNewSession();
        }

        $request->attributes->set('custom_session', $session);

        $response = $next($request);

        $this->attachSessionCookieToResponse($session, $response);

        return $response;
    }

    private function attachSessionCookieToResponse(CustomSessionModel $session, Response $response)
    {
        $sessionConfig = config('session');

        $response->headers->setCookie(new Cookie(
            // $session->getName(),
            'custom_session',
            // $$newSession->getId(),
            $session->id,
            // $this->getCookieExpirationDate(),
            0,
            $sessionConfig['path'],
            $sessionConfig['domain'],
            $sessionConfig['secure'] ?? false,
            $sessionConfig['http_only'] ?? true,
            false,
            $sessionConfig['same_site'] ?? null
        ));
    }

    private function getNewSession()
    {
        $newSession = CustomSessionModel::create();

        return $newSession;
    }

    private function retrieveToken(Request $request)
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken) {
            return $bearerToken;
        }

        return $request->cookie('custom_session');
    }

    private function retrieveSessionByToken($sessionId)
    {
        return CustomSessionModel::find($sessionId);
    }
}
