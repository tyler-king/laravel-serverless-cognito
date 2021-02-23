<?php

namespace TKing\ServerlessCognito;

class Cognito
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
}
