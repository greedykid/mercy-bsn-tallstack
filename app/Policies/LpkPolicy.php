<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lpk;
use App\Models\User;

class LpkPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lpk $lpk): bool
    {
        return $user->isAdmin() || $lpk->pic_user_id === $user->id || $lpk->pic_user_id === null;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Lpk $lpk): bool
    {
        return $user->isAdmin() || $lpk->pic_user_id === $user->id || $lpk->pic_user_id === null;
    }

    public function delete(User $user, Lpk $lpk): bool
    {
        return $user->isAdmin() || $lpk->pic_user_id === $user->id;
    }

    public function import(User $user): bool
    {
        return true;
    }
}
