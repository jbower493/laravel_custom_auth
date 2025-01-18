<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CustomSession as CustomSessionModel;

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

    public function deleteCurrentSession()
    {
        $currentSession = request()->attributes->get('custom_session');

        if (!$currentSession) {
            return;
        }

        $currentSession->delete();
    }
}
