<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function getCsrfToken(Request $request)
    {
        return '';
    }

    public function getUser(Request $request)
    {
        $user = Auth::user();

        if (!$user) return response([
            'errors' => ['No user is currently logged in.']
        ], 401);

        return [
            'message' => 'Successfully retreived user.',
            'data' => [
                'user' => $user
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
            $request->session()->regenerate();
 
            return [
                'message' => 'Login successful.'
            ];
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
            'password' => ['required', 'string']
        ]);

        User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password'])
        ]);

        return [
            'message' => 'Registration successful.'
        ];
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return [
            'message' => 'Logout successful.'
        ];
    }
}
