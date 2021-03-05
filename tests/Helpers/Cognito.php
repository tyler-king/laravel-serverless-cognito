<?php

declare(strict_types=1);

namespace TKing\ServerlessCognito\Tests\Helpers;

use App\Models\User;
use TKing\ServerlessCognito\Cognito\Validator;

trait Cognito
{
    public function actingAsCognitoGuest(array $userProps = [])
    {
        $this->actingAs(self::createUserWithCognito([], $userProps));
    }

    public function actingAsCognito(array $cognitoProps, array $userProps = [])
    {
        $this->actingAs(self::createUserWithCognito($cognitoProps, $userProps));
    }

    private static function createUserWithCognito(array $cognitoProps, array $userProps)
    {
        $user = User::factory()->create($userProps);
        return $user->setCognito(array_replace(
            Validator::GUEST_PROPS,
            $cognitoProps
        ));
    }
}
