<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        return view('app');
    }

    public function call(Request $request)
    {
        event(new \App\Events\UserPeerEvent($request->user_id, $request->all()));
    }

    public function answer(Request $request)
    {
        event(new \App\Events\UserPeerEvent($request->user_id, $request->all()));
    }
}
