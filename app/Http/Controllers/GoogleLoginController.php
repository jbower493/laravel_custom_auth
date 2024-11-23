<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        $name = $googleUser->getName();
        $email = $googleUser->getEmail();

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                // TODO: remove password login all together, including removing the column from the db
                'password' => Str::random(20)
            ]);
        }

        Auth::login($user);

        $redirectUrl = config('app.env') === 'production' ? 'https://shoppinglist.jamiebowerdev.com' : 'http://localhost:3000';

        return redirect($redirectUrl);
    }
}
