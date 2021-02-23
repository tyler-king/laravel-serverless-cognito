<?php

namespace TKing\ServerlessCognito\Cognito;

use Illuminate\Support\Facades\Cache;
use MiladRahimi\Jwt\Cryptography\Algorithms\Rsa\RS256Verifier;
use MiladRahimi\Jwt\Cryptography\Keys\RsaPublicKey;
use MiladRahimi\Jwt\Parser;
use CoderCat\JWKToPEM\JWKConverter;

class Validator
{
    public function validate(string $token)
    {
        $region = config("cognito.region");
        if ($region == "local" && app()->isLocal()) {
            return ['sub' => 'guest']; //NOW add more
        }
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
