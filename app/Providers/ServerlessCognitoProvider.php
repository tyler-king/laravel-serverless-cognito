<?php

namespace TKing\ServerlessCognito\Providers;

use TKing\ServerlessCognito\Cognito\Validator;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use TKing\ServerlessCognito\Cognito;

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
            $this->basePath('config/cognito.php') => config_path('cognito.php'),
        ]);

        $this->loadRoutesFrom($this->basePath('/routes/web.php'));

        $this->loadViewsFrom($this->basePath('/resources/views'), 'cognito');
        //NOW if token expired then redirect else
        Auth::viaRequest('cognito', function ($request) {
            try {
                parse_str($_COOKIE["jwt_token"] ?? '', $output);
                $token = $request->headers->get("authorization") ?? (($output['token_type'] ?? '') . " " . ($output['access_token'] ?? ''));
                $tokenProps = Validator::validate($token);
            } catch (\Throwable $e) {
                return abort(401);
            }
            return new Cognito($tokenProps);
        });

        $this->publishes([
            $this->basePath('resources/views') => resource_path('views/vendor/cognito'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            $this->basePath('config/cognito.php'),
            'cognito'
        );
    }

    private function basePath(string $path)
    {
        return __DIR__ . '/../../' . $path;
    }
}
