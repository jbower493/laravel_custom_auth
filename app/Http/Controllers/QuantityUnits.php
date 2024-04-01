<?php

namespace App\Http\Controllers;

use App\Models\QuantityUnit;
use Illuminate\Support\Facades\Auth;

class QuantityUnitsController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $quantityUnits = QuantityUnit::where('user_id', $loggedInUserId)->orderBy('name')->get()->toArray();

        return [
            'message' => 'Successfully retreived quantity units.',
            'data' => [
                'items' => $quantityUnits
            ]
        ];
    }
}
