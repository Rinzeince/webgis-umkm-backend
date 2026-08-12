<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogger;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        /** @var User|null $currentUser */
        $currentUser = auth()->user();

        ActivityLogger::log(
            action: 'CREATE',
            description: "Membuat akun pengguna baru: [{$user->name}] ({$user->role}).",
            subjectType: 'User',
            user: $currentUser
        );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        /** @var User|null $currentUser */
        $currentUser = auth()->user();

        ActivityLogger::log(
            action: 'UPDATE',
            description: "Memperbarui data akun pengguna: [{$user->name}] ({$user->role}).",
            subjectType: 'User',
            user: $currentUser
        );
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        /** @var User|null $currentUser */
        $currentUser = auth()->user();

        ActivityLogger::log(
            action: 'DELETE',
            description: "Menghapus akun pengguna: [{$user->name}] ({$user->role}).",
            subjectType: 'User',
            user: $currentUser
        );
    }
}
