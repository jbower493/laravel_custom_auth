<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdditionalUser;
use App\Models\CustomSession as CustomSessionModel;
use App\Models\ImportedRecipe;
use App\Models\RecipeShareRequest;
use App\Models\User;
use App\Repositories\AuthedUserRepositoryInterface;
use App\Traits\SessionTokenTrait;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    use SessionTokenTrait;

    protected $authedUserRepo;

    public function __construct(AuthedUserRepositoryInterface $authedUserRepo)
    {
        $this->authedUserRepo = $authedUserRepo;
    }

    /**
     * List all the users that have additional user access to this account
     */
    public function index()
    {
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

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
        $loggedInUser = $this->authedUserRepo->getUser();

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
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

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
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

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
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

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

        // Logout current user from their own account
        $this->authedUserRepo->logout();

        // Log them into the other person's account
        $newSession = $this->createNewSession($userToLoginAs->id);

        // session['additional_user_id'] is the id of the original user that was logged in, and is now managing services for another user.
        $newSession->additional_user_id = $loggedInUserId;
        $newSession->save();

        if ($newSession->type === CustomSessionModel::SESSION_TYPE_APP) {
            return [
                'message' => 'Successfully logged into account as additional user.',
                'token' => $newSession->id
            ];
        }

        return [
            'message' => 'Successfully logged into account as additional user.'
        ];
    }

    public function changeEmail(Request $request)
    {
        $loggedInUser = $this->authedUserRepo->getUser();

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
        $loggedInUser = $this->authedUserRepo->getUser();

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

    public function deleteAccount(User $user)
    {
        $this->authedUserRepo->logout();

        $user->delete();

        return [
            'message' => 'Account successfully deleted.'
        ];
    }

    public function notifications()
    {
        $loggedInUser = $this->authedUserRepo->getUser();

        // Share requests
        $recipeShareRequestsCollection = RecipeShareRequest::where('recipient_email', $loggedInUser->email)->get();

        $shareRequests = $recipeShareRequestsCollection->map(function ($recipeShareRequest) {
            return [
                "type" => "share_request",
                "meta" => [
                    'share_request_id' => $recipeShareRequest->id,
                    'owner_name' => $recipeShareRequest->owner->name,
                    'recipe_name' => $recipeShareRequest->recipe->name
                ]
            ];
        })->toArray();

        // Imported recipes
        $importedRecipesCollection = ImportedRecipe::where('user_id', $loggedInUser->id)->get();
        $importedRecipes = $importedRecipesCollection->map(function ($importedRecipe) {
            return [
                "type" => "imported_recipe",
                "meta" => [
                    'imported_recipe_id' => $importedRecipe->id,
                    'recipe' => json_decode($importedRecipe->data)
                ]
            ];
        })->toArray();

        $notifications = array_merge($shareRequests, $importedRecipes);

        return [
            'message' => 'Successfully fetched notifications.',
            'data' => [
                "notifications" => $notifications
            ]
        ];
    }
}
