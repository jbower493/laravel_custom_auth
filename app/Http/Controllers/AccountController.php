<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdditionalUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AccountController extends Controller
{
    /**
     * List all the users that have additional user access to this account
     */
    public function index()
    {
        $loggedInUserId = Auth::id();

        // TODO: speed up this query by doing it all in SQL instead of looping over and then querying in each iteration

        $additionalUsers = AdditionalUser::where('user_id', $loggedInUserId)->get();

        $mappedAdditionalUsers = $additionalUsers->map(function ($item) {
            $user = User::find($item->additional_user_id);

            return [
                'email' => $user->email
            ];
        });

        return [
            'message' => 'Successfully retreived additional users.',
            'data' => [
                'additional_users' => $mappedAdditionalUsers
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUser = Auth::user();

        $validatedRequest = $request->validate([
            'additional_user_email' => ['required', 'exists:users,email']
        ]);

        if ($validatedRequest['additional_user_email'] === $loggedInUser->email) {
            return response([
                'errors' => ['Cannot add yourself as an additional user.']
            ], 400);
        }

        $newAdditionalUser = User::where('email', $validatedRequest['additional_user_email'])->get()->first();

        AdditionalUser::create([
            'user_id' => $loggedInUser->id,
            'additional_user_id' => $newAdditionalUser->id
        ]);

        return [
            'message' => 'Successfully added additional user.'
        ];
    }

    public function remove(Request $request)
    {
        $loggedInUserId = Auth::id();

        $validatedRequest = $request->validate([
            'additional_user_email' => ['required', 'exists:users,email']
        ]);

        $additionalUserData = User::where('email', $validatedRequest['additional_user_email'])->get()->first();

        $additionalUser = AdditionalUser::where('user_id', $loggedInUserId)
            ->where('additional_user_id', $additionalUserData->id)
            ->get()
            ->first();

        if (!$additionalUser) {
            return response([
                'errors' => ['Email address is not an additional user of your account.']
            ], 400);
        }

        $additionalUser->delete();

        return [
            'message' => 'Successfully removed additional user.'
        ];
    }

    /**
     * List all the accounts that this account has additional user access to
     */
    public function accountAccess()
    {
        $loggedInUserId = Auth::id();

        // TODO: speed up this query by doing it all in SQL instead of looping over and then querying in each iteration

        $additionalUsersEntries = AdditionalUser::where('additional_user_id', $loggedInUserId)->get();

        $accountsThisAccountHasAccessTo = $additionalUsersEntries->map(function ($item) {
            $user = User::find($item->user_id);

            return [
                'email' => $user->email
            ];
        });

        return [
            'message' => 'Successfully retreived accounts this account has access to.',
            'data' => [
                'account_access' => $accountsThisAccountHasAccessTo
            ]
        ];
    }

    public function loginAsAnotherUser(Request $request)
    {
        $loggedInUserId = Auth::id();

        $validatedRequest = $request->validate([
            'user_email_to_login_as' => ['required', 'exists:users,email']
        ]);

        $userToLoginAs = User::where('email', $validatedRequest['user_email_to_login_as'])->get()->first();

        $additionalUserEntry = AdditionalUser::where('user_id', $userToLoginAs->id)
            ->where('additional_user_id', $loggedInUserId)
            ->get()
            ->first();

        if (!$additionalUserEntry) {
            return response([
                'errors' => ['You do not have access to this account.']
            ], 400);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::loginUsingId($userToLoginAs->id);

        Session::put('additional_user_id', $loggedInUserId);

        return [
            'message' => 'Successfully logged into account as additional user.'
        ];
    }

    public function changeEmail(Request $request)
    {
        $loggedInUser = Auth::user();

        // TODO: This presents a security risk, telling the user if an email address has already been taken. Eventually change it to send them an email and make them confirm
        $validatedRequest = $request->validate([
            'new_email' => ['required', 'string', 'email', 'unique:users,email'],
        ]);

        $loggedInUser->email = $validatedRequest['new_email'];
        $loggedInUser->save();

        return [
            'message' => 'Successfully changed account email address.'
        ];
    }

    public function changePassword(Request $request)
    {
        $loggedInUser = Auth::user();

        $validatedRequest = $request->validate([
            "new_password" => ['required', 'string'],
            "confirm_new_password" => ['required', 'string', 'same:new_password']
        ]);

        $loggedInUser->password = Hash::make($validatedRequest['new_password']);

        $loggedInUser->save();

        return [
            'message' => 'Successfully changed account password.'
        ];
    }
}
