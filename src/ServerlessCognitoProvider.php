<?php

namespace TKing;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use MiladRahimi\Jwt\Cryptography\Algorithms\Rsa\RS256Verifier;
use MiladRahimi\Jwt\Cryptography\Keys\RsaPublicKey;
use MiladRahimi\Jwt\Parser;
use CoderCat\JWKToPEM\JWKConverter;

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
                $tokenProps = $this->validate($token);
                return new Cognito($tokenProps);
            } catch (\Throwable $e) {
                return abort(401);
            }
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

    private function validate(string $token)
    {
        $region = config("cognito.region");
        $userPoolId = config("cognito.user_pool_id");
        $app_token = [config("cognito.app_token")];

        $token = explode(" ", $token);

        if ($token[0] == "Bearer") {
            $token = $token[1];
        } else {
            throw new \Exception("No Token provided");
        }

        if (strlen($token) == 0) {
            return [];
        }
        $kid = json_decode(base64_decode(explode(".", $token)[0]), true)['kid'];
        if (!isset($kid)) {
            throw new \Exception("Not cognito token");
        }
        $iss = "https://cognito-idp.$region.amazonaws.com/$userPoolId";
        $jwks = Cache::remember('cognito.jwks', now()->addMinutes(10), function () use ($iss) {
            $location = "$iss/.well-known/jwks.json";
            return json_decode(file_get_contents($location), true);
        });

        $jwk = array_values(array_filter($jwks['keys'], function ($jwk) use ($kid) {
            return $jwk['kid'] == $kid;
        }))[0];

        if (!isset($jwk)) {
            throw new \Exception("Invalid token");
        }
        $jwkConverter = new JWKConverter();
        $PEM = $jwkConverter->toPEM($jwk);

        $publicKey = new RsaPublicKey($PEM);
        $verifier = new RS256Verifier($publicKey);
        // Parse the token

        $parser = new Parser($verifier);
        $claims = $parser->parse($token);
        if ($claims['exp'] <= (new \DateTime('now', new \DateTimeZone("UTC")))->format("U")) {
            throw new \Exception("Token has expired");
        }
        if (!in_array($claims['token_use'], ['id', 'access'])) {
            throw new \Exception("Invalid token_use");
        }
        if (isset($claims['aud'])) {
            if (!in_array($claims['aud'], $app_token)) {
                throw new \Exception("Invalid client");
            }
        }
        if (isset($claims['iss'])) {
            if ($claims['iss'] !== $iss) {
                throw new \Exception("Invalid token");
            }
        }
        return $claims;
    }
}
