<?php

namespace TKing\ServerlessCognito;

use Illuminate\Contracts\Auth\Authenticatable;
use JsonSerializable;

class Cognito implements Authenticatable, JsonSerializable
{

    private $props = [];

    public function __construct(array $props)
    {
        $this->props = $props;
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
}
