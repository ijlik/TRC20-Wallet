<?php

namespace App\Http\Controllers;

use App\Coin;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $coins = Coin::all();
        return view('home', compact('coins'));
    }

    public function coin(){
        $coins = Coin::all();
        return view('coin', compact('coins'));
    }
}
