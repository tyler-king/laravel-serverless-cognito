<?php

namespace TKing\ServerlessCognito\Providers;

use TKing\ServerlessCognito\Cognito\Validator;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use TKing\ServerlessCognito\Cognito;
use TKing\ServerlessCognito\Cognito\InvalidTokenException;
use TKing\ServerlessCognito\Cognito\TokenExpiredException;

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

        Auth::viaRequest('cognito', function (Request $request) {
            if (Auth::user()) {
                return Auth::user();
            }
            try {
                $cookie = $request->cookie("jwt_token", '');
                parse_str($cookie, $output);
                $token = ($output['token_type'] ?? '') . " " . ($output['access_token'] ?? '');
                $token = $request->headers->get("authorization", $token);
                $tokenProps = Validator::validate($token);
            } catch (TokenExpiredException | InvalidTokenException $e) {
                return null;
            } catch (\Throwable $e) {
                return abort(500, $e->getMessage());
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

        $auth = require($this->basePath('config/auth.php'));

        $config = $this->app->make('config');

        $config->set('auth.guards.cognito',  $auth['guards']['cognito']);
        $config->set('auth.providers.cognito',  $auth['providers']['cognito']);

        /** @var Router $router  */
        $router = $this->app['router'];

        $router->middlewareGroup('api.cognito', [
            \App\Http\Middleware\EncryptCookies::class,
            'throttle:60,1',
            'auth.cognito:cognito',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $router->aliasMiddleware('auth.cognito', \TKing\ServerlessCognito\Http\Middleware\Authenticate::class);
    }



    private function basePath(string $path)
    {
        return __DIR__ . '/../../' . $path;
    }
}
