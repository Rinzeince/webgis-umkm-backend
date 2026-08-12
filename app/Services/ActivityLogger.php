<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log a user activity to database.
     *
     * @param string $action LOGIN, LOGOUT, CREATE, UPDATE, DELETE, ANALISIS
     * @param string $description Detailed description of the action
     * @param string|null $subjectType Subject type e.g. UMKM, Artikel, User, Analisis
     * @param User|null $user User object if explicitly passed
     */
    public static function log(
        string $action,
        string $description,
        ?string $subjectType = null,
        ?User $user = null
    ): ActivityLog {
        /** @var User|null $currentUser */
        $currentUser = $user ?? Auth::user();

        return ActivityLog::create([
            'user_id' => $currentUser?->id,
            'user_name' => $currentUser?->name ?? 'Sistem / Pengunjung',
            'user_role' => $currentUser?->role ?? 'guest',
            'action' => strtoupper($action),
            'subject_type' => $subjectType,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
