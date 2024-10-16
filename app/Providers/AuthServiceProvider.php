<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\ShoppingList;
use App\Policies\ShoppingListPolicy;
use Illuminate\Support\Facades\App;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ShoppingList::class => ShoppingListPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function ($user, string $token) {
            $baseUrl = App::environment() === 'production' ? 'https://shoppinglist.jamiebowerdev.com/' : 'http://localhost:3000/';

            return $baseUrl . 'reset-password/' . $token . '?email=' . $user->email;
        });
    }
}
