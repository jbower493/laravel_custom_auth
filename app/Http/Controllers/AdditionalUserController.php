<?php

namespace App\Http\Controllers;

use App\Models\AdditionalUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdditionalUserController extends Controller
{
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
            'additional_user_id' => ['required', 'exists:users,id']
        ]);

        $additionalUser = AdditionalUser::where('user_id', $loggedInUserId)
            ->where('additional_user_id', $validatedRequest['additional_user_id'])
            ->get()
            ->first();

        $additionalUser->delete();

        return [
            'message' => 'Successfully removed additional user.'
        ];
    }
}
