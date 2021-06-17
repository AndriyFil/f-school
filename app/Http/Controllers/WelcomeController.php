<?php

namespace App\Http\Controllers;

use App\City;
use App\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    public function index()
    {
        $subjects = ['id' => -1, 'name' => 'Немає даних'];
        $classes = ['id' => -1, 'name' => 'Немає даних'];
//        dd(Auth::user()->id);
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
