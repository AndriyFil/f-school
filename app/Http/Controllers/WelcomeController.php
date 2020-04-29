<?php

namespace App\Http\Controllers;

use App\City;
use App\Region;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function getRegions() {
        $regions = Region::all();
        return response()->json($regions);
    }

    public function getCities($id) {
        $cities = City::all()->where('cit_region_id', '=', $id);
        return response()->json($cities);
    }
}
