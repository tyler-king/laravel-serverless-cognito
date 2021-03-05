<?php

namespace TKing\ServerlessCognito;


trait Cognito
{
    private $cognito = [];

    public function setCognito(array $props)
    {
        $this->cognito = $props;

        $update = false;
        $name = ($props['given_name'] ?? '') . " " .  ($props['family_name'] ?? '');
        if ($name !== $this->name) {
            $this->name = $name;
            $update = true;
        }
        $email = $props['email'] ?? '';
        if ($email !== $this->email) {
            $this->email = $email;
        }
        if ($props['sub'] !== $this->sub) {
            $this->sub = $props['sub'];
        }
        $this->password = 'not needed';
        if ($update) {
            $this->save();
        }
        return $this;
    }

    public function hasCognito(): bool
    {
        return !empty($this->cognito['sub']);
    }

    public function getCognito(): array
    {
        return $this->cognito;
    }

    public function isAdmin(): bool
    {
        return false;
    }
}
