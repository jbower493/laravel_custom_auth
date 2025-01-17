<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CustomSession as CustomSessionModel;
use ErrorException;

trait SessionTokenTrait
{
    public function getExistingSession(Request $request)
    {
        $existingSession = $request->attributes->get('custom_session');

        return $existingSession;
    }

    public function createNewSession($userId = null, $sessionType)
    {
        if ($sessionType !== CustomSessionModel::SESSION_TYPE_APP && $sessionType !== CustomSessionModel::SESSION_TYPE_WEB) {
            throw new ErrorException('Invalid session type, must be app or web.');
        }

        $newSession = CustomSessionModel::create([
            'user_id' => $userId,
            'type' => $sessionType
        ]);

        return $newSession;
    }

    public function attachSessionCookieToResponse(CustomSessionModel $session, Response $response)
    {
        if (!$session) {
            return;
        }

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

    public function checkIsFromMobileApp(Request $request)
    {
        $customHeader = $request->headers->get('Shopping-List-Mobile-App');

        return $customHeader ? true : false;
    }
}
