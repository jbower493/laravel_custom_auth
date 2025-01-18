<?php

namespace App\Http\Controllers;

use App\Models\CustomSession as CustomSessionModel;
use App\Models\User;
use App\Traits\SessionTokenTrait;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleLoginController extends Controller
{
    use SessionTokenTrait;

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

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

        $newSession = $this->createNewSession($user->id);

        $redirectUrl = config('app.env') === 'production' ? 'https://shoppinglist.jamiebowerdev.com' : 'http://localhost:3000';

        if ($newSession->type === CustomSessionModel::SESSION_TYPE_APP) {
            // TODO: still need to properly implement Google login on the app
            return redirect($redirectUrl . '?token=' . $newSession->id);
        }

        return redirect($redirectUrl)->withCookie($this->getSessionCookie($newSession));
    }
}
