<?php

namespace TKing\ServerlessCognito\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        try {
            if ($request->user('cognito')) {
                return redirect('/user');
            }
        } catch (\Exception $e) {
            //s
        }

        $app_token = config("cognito.app_token");
        $cognito_url = config("cognito.login_url");
        if (!isset($app_token) || !isset($cognito_url)) {
            return abort(500, "Missing Cognito configuration");
        }

        $redirect_uri = route('callback');
        $cognito_url = $cognito_url . "login?response_type=token&client_id=$app_token&redirect_uri=$redirect_uri";
        return redirect($cognito_url);
    }

    public function hash(Request $request)
    {
        return view('cognito::hash', ['callback' => "/" . $request->path()]);
    }

    public function readHash(Request $request)
    {
        $token = $request->header("x-auth-hash", '');
        if ($token !== "") {
            $request->session()->put('jwt_token', $token);
        }
        return response('Logged In', 200, [
            'Location' => "/login"
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user('cognito');

        return response($user, 200);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('jwt_token');
        return redirect('/login');
    }
}
