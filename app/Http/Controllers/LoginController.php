<?php

namespace TKing\ServerlessCognito\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{

    public function login(Request $request)
    {

        if ($request->user('cognito')) {
            return redirect('/user');
        }

        $app_token = config("cognito.app_token");
        $cognito_url = config("cognito.login_url");
        if (!isset($app_token) || !isset($cognito_url)) {
            return abort(500, "Missing Cognito configuration");
        }

        $redirect_uri = route('callback');
        $cognito_url = $cognito_url . "login?response_type=token&client_id=$app_token&redirect_uri=$redirect_uri";
        $cookie = Cookie::forget('jwt_token');
        return redirect($cognito_url)->withoutCookie($cookie);
    }

    public function hash(Request $request)
    {
        return view('cognito::hash', ['callback' => "/" . $request->path()]);
    }

    public function readHash(Request $request)
    {
        $token = $request->header("x-auth-hash", '');
        //NOW expires time, not sure if forever will work
        $cookie = cookie()->forever('jwt_token', $token);
        return redirect('login')->withCookie($cookie);
    }

    public function user(Request $request)
    {
        $user = $request->user()->user;

        return response($user, 200);
    }

    public function logout(Request $request)
    {
        $cookie = Cookie::forget('jwt_token');
        return redirect('/login')->withoutCookie($cookie);
    }
}
