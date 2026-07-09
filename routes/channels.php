<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('kitchen', function ($user) {
    return in_array($user->role, [UserRole::Superadmin, UserRole::Admin, UserRole::Staff], true);
});

Broadcast::channel('audit-logs', function ($user) {
    return $user->role === UserRole::Superadmin;
});

Broadcast::channel('spaces', function ($user) {
    return in_array($user->role, [UserRole::Superadmin, UserRole::Admin, UserRole::Staff], true);
});
