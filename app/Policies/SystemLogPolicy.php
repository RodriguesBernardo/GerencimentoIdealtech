<?php

namespace App\Policies;

use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, SystemLog $log): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return false; // Logs são criados apenas pelo sistema
    }

    public function update(User $user, SystemLog $log): bool
    {
        return false; // Logs não podem ser editados
    }

    public function delete(User $user, SystemLog $log): bool
    {
        return $user->is_admin;
    }

    public function restore(User $user, SystemLog $log): bool
    {
        return $user->is_admin;
    }

    public function forceDelete(User $user, SystemLog $log): bool
    {
        return $user->is_admin;
    }

    public function export(User $user): bool
    {
        return $user->is_admin;
    }
}