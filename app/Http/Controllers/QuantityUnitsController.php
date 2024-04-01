<?php

namespace App\Http\Controllers;

use App\Models\QuantityUnit;

class QuantityUnitsController extends Controller
{
    public function index()
    {
        $quantityUnits = QuantityUnit::orderBy('name')->get()->toArray();

        return [
            'message' => 'Successfully retreived quantity units.',
            'data' => [
                'quantity_units' => $quantityUnits
            ]
        ];
    }
}
