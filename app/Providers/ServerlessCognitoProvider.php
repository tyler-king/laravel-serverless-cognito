<?php

namespace App\Providers;

use App\Cognito\Validator;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class ServerlessCognitoProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */

    public function boot()
    {

        $this->publishes([
            __DIR__ . '/config/cognito.php' => config_path('cognito.php'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'cognito');
        //NOW if token expired then redirect else
        Auth::viaRequest('cognito', function ($request) {
            try {
                parse_str($_COOKIE["jwt_token"], $output);
                $token = $request->headers->get("authorization") ?? ($output['token_type'] . " " . $output['access_token']);
                $tokenProps = Validator::validate($token);
            } catch (\Throwable $e) {
                if (!app()->isLocal()) {
                    return abort(401);
                }
                $tokenProps = ['sub' => 'guest'];
            }
            return new Cognito($tokenProps);
        });

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/cognito'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/cognito.php',
            'cognito'
        );
    }
}
