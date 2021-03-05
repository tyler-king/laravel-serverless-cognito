<?php

declare(strict_types=1);

namespace TKing\ServerlessCognito\Testing\Helpers;

use App\Models\User;
use TKing\ServerlessCognito\Cognito\Validator;

trait Cognito
{
    public function actingAsCognitoGuest(array $userProps = []): User
    {
        $user = self::createUserWithCognito([], $userProps);
        $this->actingAs($user);
        return $user;
    }

    public function actingAsCognito(array $cognitoProps, array $userProps = []): User
    {
        $user = self::createUserWithCognito($cognitoProps, $userProps);
        $this->actingAs($user);
        return $user;
    }

    private static function createUserWithCognito(array $cognitoProps, array $userProps): User
    {
        $user = User::factory()->create($userProps);
        return $user->setCognito(array_replace(
            Validator::GUEST_PROPS,
            $cognitoProps
        ));
    }
}
