<?php

namespace TKing\ServerlessCognito\Providers;

use App\Models\User;
use TKing\ServerlessCognito\Cognito\Validator;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
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

        $this->loadMigrationsFrom($this->basePath('/database/migrations'));

        Auth::viaRequest('cognito', function (Request $request) {
            if (Auth::user() && Auth::user()->hasCognito()) {
                return Auth::user();
            }
            try {
                $cookie = $request->cookie("jwt_token", '');
                parse_str($cookie, $output);
                $token = ($output['token_type'] ?? '') . " " . ($output['access_token'] ?? '');
                $token = $request->headers->get("authorization", $token);
                $tokenProps = Validator::validate($token);
                // return User::firstOrCreate(['sub' => $tokenProps['sub']], [
                return User::firstOrCreate(['email' => $tokenProps['email']], [
                    'name' => ($tokenProps['given_name'] ?? '') . " " . ($tokenProps['family_name'] ?? ''),
                    'email' => $tokenProps['email'] ?? '',
                    'sub' => $tokenProps['sub'],
                    'scopes' => [],
                    'password' => 'not needed'
                ]); //->setCognito($tokenProps);
            } catch (TokenExpiredException | InvalidTokenException $e) {
                return null;
            } catch (\Throwable $e) {
                return abort(500, $e->getMessage());
            }
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
