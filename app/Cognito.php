<?php

namespace TKing\ServerlessCognito;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use JsonSerializable;

class Cognito implements Authenticatable, JsonSerializable
{

    private $props = [];

    public function __construct(array $props)
    {
        $this->props = $props;
        $this->props['user'] = User::firstOrCreate(['sub' => $props['sub']], [
            'name' => $this->given_name . " " . $this->family_name,
            'email' => $this->email ?? '',
            'sub' => $this->sub,
            'scopes' => [],
            'password' => 'not needed'
        ]);
    }

    public function __get($property)
    {
        return $this->props[$property];
    }

    public function getAuthIdentifier()
    {
        return $this->sub;
    }

    public function getAuthIdentifierName()
    {
        return 'sub';
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return '';
    }

    public function getRememberTokenName()
    {
        return '';
    }

    public function setRememberToken($value)
    {
        //do nothing   
    }

    public function jsonSerialize()
    {
        return $this->props;
    }

    public function isAdmin(): bool
    {
        return false;
    }

    public static function guestAccount()
    {
        return new self(self::GUEST_PROPS);
    }

    public const GUEST_PROPS = [
        'sub' => 'guest',
        'given_name' => 'guest',
        'family_name' => 'guest',
        'email' => '',
    ];
}
