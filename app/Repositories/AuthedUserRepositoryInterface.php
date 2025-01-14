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
}
