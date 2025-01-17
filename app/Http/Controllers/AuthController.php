<?php

namespace App\Http\Controllers;

use App\Traits\SessionTokenTrait;
use App\Models\CustomSession as CustomSessionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Repositories\AuthedUserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use SessionTokenTrait;

    protected $authedUserRepo;

    public function __construct(AuthedUserRepositoryInterface $authedUserRepo)
    {
        $this->authedUserRepo = $authedUserRepo;
    }

    public function getCsrfToken()
    {
        return '';
    }

    public function getUser()
    {
        $loggedInUser = $this->authedUserRepo->getUser();

        return [
            'message' => 'Successfully retreived user.',
            'data' => [
                'user' => $loggedInUser,
                "additional_user" => null
            ]
        ];
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = User::where('email', $credentials['email'])->first();
            $sessionType = $this->checkIsFromMobileApp($request) ? CustomSessionModel::SESSION_TYPE_APP : CustomSessionModel::SESSION_TYPE_WEB;
            $newSession = $this->createNewSession($user->id, $sessionType);

            if ($sessionType === CustomSessionModel::SESSION_TYPE_APP) {
                return [
                    'message' => 'Login successful.',
                    'token' => $newSession->id
                ];
            }

            $response = response([
                'message' => 'Login successful.'
            ]);

            $this->attachSessionCookieToResponse($newSession, $response);

            return $response;
        }

        return response([
            'errors' => ['Incorrect credentials.']
        ], 401);
    }

    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'unique:users', 'email'],
            'password' => ['required', 'string'],
            "confirm_password" => ['required', 'string', 'same:password']
        ]);

        $newUser = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password'])
        ]);

        // Log them straight in
        if (Auth::attempt([
            'email' => $newUser->email,
            'password' => $fields['password'],
        ])) {
            Session::remove('additional_user_id');
            $request->session()->regenerate();

            return [
                'message' => 'Registration successful.'
            ];
        }

        return response([
            'errors' => ['Something went wrong.']
        ], 500);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::remove('additional_user_data');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return [
            'message' => 'Logout successful.'
        ];
    }

    public function forgotPassword(Request $request)
    {

        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // If successful OR user did not exist, send this generic message. We don't want to let the person know if the email exists in the system
        if ($status === Password::INVALID_USER || $status === Password::RESET_LINK_SENT) {
            return ['message' => 'If an account exists for this email address, we\'ve sent a password reset email. Click the link in the email to reset your password.'];
        }

        if ($status === Password::RESET_THROTTLED) {
            return response([
                'errors' => ['Too many requests in a short time. Please wait a bit and try again.']
            ], 429);
        }

        return response([
            'errors' => ['Something went wrong.']
        ], 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? ['message' => 'Password successfully reset.']
            : response([
                'errors' => ['Something went wrong.']
            ], 500);
    }
}
