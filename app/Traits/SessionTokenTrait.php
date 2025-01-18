<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CustomSession as CustomSessionModel;
use Carbon\Carbon;

trait SessionTokenTrait
{
    public function getExistingSession(Request $request)
    {
        $existingSession = $request->attributes->get('custom_session');

        return $existingSession;
    }

    public function createNewSession($userId = null)
    {
        $sessionType = $this->checkIsFromMobileApp(request()) ? CustomSessionModel::SESSION_TYPE_APP : CustomSessionModel::SESSION_TYPE_WEB;

        $newSession = CustomSessionModel::create([
            'user_id' => $userId,
            'type' => $sessionType,
            'expires_at' => Carbon::now()->add(CustomSessionModel::SESSION_LIFETIME_HOURS, 'hour')
        ]);

        return $newSession;
    }

    public function createCookie($value)
    {
        $sessionConfig = config('session');

        return new Cookie(
            // $session->getName(),
            'custom_session',
            // $$newSession->getId(),
            $value,
            // $this->getCookieExpirationDate(),
            0,
            $sessionConfig['path'],
            $sessionConfig['domain'],
            $sessionConfig['secure'] ?? false,
            $sessionConfig['http_only'] ?? true,
            false,
            $sessionConfig['same_site'] ?? null
        );
    }

    public function getSessionCookie(CustomSessionModel $session)
    {
        if (!$session) {
            return null;
        }

        return $this->createCookie($session->id);
    }

    public function attachSessionCookieToResponse(CustomSessionModel $session, Response $response)
    {
        if (!$session) {
            return;
        }

        $cookie = $this->getSessionCookie($session);

        $response->headers->setCookie($cookie);
    }

    public function checkIsFromMobileApp(Request $request)
    {
        $customHeader = $request->headers->get('Shopping-List-Mobile-App');

        return $customHeader ? true : false;
    }

    public function deleteCurrentSession()
    {
        $currentSession = request()->attributes->get('custom_session');

        if (!$currentSession) {
            return;
        }

        $currentSession->delete();
    }
}
