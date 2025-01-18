<?php

namespace App\Repositories;

use App\Repositories\AuthedUserRepositoryInterface;
use App\Traits\SessionTokenTrait;

class AuthedUserRepository implements AuthedUserRepositoryInterface
{
    use SessionTokenTrait;

    public function getUser()
    {
        $user = request()->attributes->get('logged_in_user');

        if (!$user) {
            return null;
        }

        return $user;
    }

    public function logout()
    {
        $this->deleteCurrentSession();
    }
}
