<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
	$app_token = config("cognito.app_token");
	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$redirect_uri = str_replace("/login", "/profile", $actual_link);
	$token = $_COOKIE['jwt_token'] ?? "";
	if ($token == "") {
		if (app()->environment() === 'local') {
			$redirect_uri = config("cognito.debug_redirect");
		}
		$cognito_url = config("cognito.login_url") . "login?response_type=token&client_id=$app_token&redirect_uri=$redirect_uri";
		header("Location: " . $cognito_url);
	} else {
		header("Location: " .  $redirect_uri);
	}
	exit();
});

Route::get('/profile', function () {
	$token = $_COOKIE['token'] ?? "";
	if ($token !== "") {
		setcookie("token", "");
		setcookie("jwt_token", $token, null, null, null, null, true);
	} else {
		$token = $_COOKIE['jwt_token'] ?? "";
		if ($token !== "") {
			parse_str($_COOKIE["jwt_token"], $output);
			if ($output['expires_in'] < date("U")) {
				//NOW expire token
				/*setcookie("jwt_token", "", null, null, null, null, true);
				$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				$redirect_uri = str_replace("/profile", "/login", $actual_link);
				header("Location: " .  $redirect_uri);
				exit();*/
			}
		}
	}
	return view('cognito::profile');
});

Route::get('/logout', function () {
	setcookie('token', "");
	setcookie("jwt_token", "", null, null, null, null, true);
	return view('cognito::login');
});
