<?php

namespace App\Repositories;

use App\Repositories\AuthedUserRepositoryInterface;
use App\Models\User;

class AuthedUserRepository implements AuthedUserRepositoryInterface
{
    public function getUser()
    {
        $session = request()->attributes->get('custom_session');

        if (!$session) {
            return null;
        }

        return User::find($session->user_id) ?? null;
    }
}
