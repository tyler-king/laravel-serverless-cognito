<?php

namespace TKing\ServerlessCognito\Cognito;

use Illuminate\Support\Facades\Cache;
use MiladRahimi\Jwt\Cryptography\Algorithms\Rsa\RS256Verifier;
use MiladRahimi\Jwt\Cryptography\Keys\RsaPublicKey;
use MiladRahimi\Jwt\Parser;
use CoderCat\JWKToPEM\JWKConverter;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use TKing\ServerlessCognito\Cognito;
use TKing\ServerlessCognito\Cognito\TokenExpiredException;

class Validator
{
    public const GUEST_PROPS = [
        'sub' => 'guest',
        'given_name' => 'guest',
        'family_name' => 'guest',
        'email' => '',
    ];

    public static function validate(string $fullToken)
    {
        $region = config("cognito.cognito.region");
        if ($region == "local" && app()->isLocal()) {
            return self::GUEST_PROPS;
        }
        $userPoolId = config("cognito.cognito.user_pool_id");
        if (empty($userPoolId)) {
            throw new \Exception("Invalid configuration");
        }

        $token = explode(" ", $fullToken);

        if ($token[0] == "Bearer") {
            $token = $token[1];
        } else {
            throw new InvalidTokenException("No Token provided");
        }

        if (strlen($token) == 0) {
            return [];
        }
        $kid = json_decode(base64_decode(explode(".", $token)[0]), true)['kid'];
        if (!isset($kid)) {
            throw new InvalidTokenException("Not cognito token");
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
            throw new InvalidTokenException("Invalid token");
        }
        $jwkConverter = new JWKConverter();
        $PEM = $jwkConverter->toPEM($jwk);

        $publicKey = new RsaPublicKey($PEM);
        $verifier = new RS256Verifier($publicKey);
        // Parse the token

        $parser = new Parser($verifier);
        try {
            $claims = $parser->parse($token);
        } catch (\Exception $e) {
            throw new InvalidTokenException("Invalid token", 0, $e);
        }
        if (!in_array($claims['token_use'], ['id', 'access'])) {
            throw new InvalidTokenException("Invalid token_use");
        }
        if (isset($claims['aud'])) {
            $app_token = config("cognito.cognito.app_token");
            if (empty($app_token)) {
                throw new \Exception("Invalid configuration");
            }
            if (!in_array($claims['aud'], [$app_token])) {
                throw new InvalidTokenException("Invalid client");
            }
        }
        if (isset($claims['iss'])) {
            if ($claims['iss'] !== $iss) {
                throw new InvalidTokenException("Invalid token");
            }
        }
        $sub = $claims['sub'];
        if (isset($claims['given_name']) && isset($claims['family_name']) && isset($claims['email'])) { 
            return $claims;
        }

        $info = cache($sub);
        if (!$info) {
            $info = self::getUserInfo($fullToken);
            cache([$sub => $info], now()->addMinutes(10));
        }

        return array_replace($info, $claims);
    }

    private static function getUserInfo(string $fullToken)
    {

        try {
            $client = new Client();
            $url = config("cognito.cognito.login_url") . "oauth2/userInfo";
            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => $fullToken
                ]
            ])->getBody()->getContents();
            $response = json_decode($response, true);
        }   catch (Exception $exception) {
            return [];
        }

        return $response;
    }
}
