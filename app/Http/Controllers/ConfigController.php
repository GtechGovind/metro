<?php

namespace App\Http\Controllers;

use App\Models\Fare;
use App\Models\Station;

class ConfigController extends Controller
{
    public function getFares()
    {
        return Fare::all();
    }

    public function getStation()
    {
        return Station::all();
    }
}
