<?php

namespace App\Repositories;

use App\Models\User;

interface AuthedUserRepositoryInterface
{
    /**
     * Set the name of the session.
     *
     * @return User | null
     */
    public function getUser();

    /**
     * Log the current user out (by deleting their session).
     *
     * @return void
     */
    public function logout();
}
