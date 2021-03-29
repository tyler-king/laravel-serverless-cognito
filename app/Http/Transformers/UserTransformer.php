<?php

declare(strict_types=1);

namespace TKing\ServerlessCognito\Http\Transformers;

use App\Models\User;

class UserTransformer
{

    public function transform(User $user)
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'registration_date' => $user->created_at->format('Y-m-d h:i:s'),
            'cognito' => $user->getCognito(),
            'scopes' => $user->scopes ?? []
        ];
    }
}
