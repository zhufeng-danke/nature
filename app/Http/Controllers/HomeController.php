<?php

namespace App\Http\Controllers;

use App\Containers\Record;
use App\Jobs\Data\Suite;
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        for($i=0; $i<10; $i++){
//            $str = 'str-'.$i;
//            dispatch((new Suite(new Record($str)))->onQueue('suite'));
//        }

        return view('home');
    }
}
