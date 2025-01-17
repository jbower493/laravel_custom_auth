<?php

namespace App\Repositories;

use App\Repositories\AuthedUserRepositoryInterface;
use App\Models\User;

class AuthedUserRepository implements AuthedUserRepositoryInterface
{
    public function getUser()
    {
        $user = request()->attributes->get('logged_in_user');

        if (!$user) {
            return null;
        }

        return $user;
    }
}
