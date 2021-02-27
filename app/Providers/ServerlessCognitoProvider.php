<?php

namespace TKing\ServerlessCognito\Providers;

use TKing\ServerlessCognito\Cognito\Validator;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TKing\ServerlessCognito\Cognito;
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
                parse_str($request->session()->get('jwt_token', ''), $output);
                $from_session = ($output['token_type'] ?? '') . " " . ($output['access_token'] ?? '');
                $token = $request->headers->get("authorization", $from_session);
                $tokenProps = Validator::validate($token);
                //rmember changes inphp-jwt file. comment out openssl_free_key
            } catch (TokenExpiredException $e) {
                return redirect('/login'); //NOW add redirect key
            } catch (\Throwable $e) {
                return abort(401, $e->getMessage());
            } catch (\Throwable $e) {
                return abort(500, $e->getMessage()); //NOW add bad failures like php ones
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
