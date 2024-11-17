<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

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
                'password' => 'asdfasdfasdfasdf98274%&*#*$'
            ]);
        }

        Auth::login($user);

        return redirect("http://localhost:3000");
    }
}
