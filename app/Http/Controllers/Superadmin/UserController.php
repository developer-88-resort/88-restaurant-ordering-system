<?php

namespace App\Http\Controllers\Superadmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of all user accounts.
     */
    public function index(): View
    {
        $users = User::orderByRaw("field(role, 'superadmin', 'admin', 'staff')")
            ->orderBy('created_at')
            ->get();

        $onlineUserIds = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
            ->pluck('user_id')
            ->all();

        return view('superadmin.users.index', [
            'users' => $users,
            'onlineUserIds' => $onlineUserIds,
            'activeSuperadminCount' => $this->activeSuperadminCount(),
        ]);
    }

    public function create(): View
    {
        $recentInvitations = User::whereNull('password')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $pendingCount = User::whereNull('password')->count();

        return view('superadmin.users.create', [
            'recentInvitations' => $recentInvitations,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->validated(),
            'password' => null,
            'is_active' => true,
            'invited_by' => auth()->id(),
        ]);

        $user->sendInvitation();

        return redirect()->route('superadmin.users.index')
            ->with('status', __('Invitation sent to :email.', ['email' => $user->email]));
    }

    /**
     * Issue a fresh invitation link for a user who hasn't activated their
     * account yet (invalidates any previous link, whether it had expired
     * or not).
     */
    public function resendInvitation(User $user): RedirectResponse
    {
        if (! $user->isPendingActivation()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', __(':name has already activated their account.', ['name' => $user->name]));
        }

        $user->sendInvitation();

        return redirect()->route('superadmin.users.index')
            ->with('status', __('Invitation resent to :email.', ['email' => $user->email]));
    }

    public function edit(User $user): View|RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('profile.edit')
                ->with('status', __('Use this page to edit your own account.'));
        }

        return view('superadmin.users.edit', [
            'user' => $user,
            'isLastActiveSuperadmin' => $this->isLastActiveSuperadmin($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('profile.edit')
                ->with('status', __('Use this page to edit your own account.'));
        }

        $user->update($request->validated());

        return redirect()->route('superadmin.users.index')
            ->with('status', __('User account updated successfully.'));
    }

    /**
     * Send an active user a password reset link rather than letting a
     * Superadmin type a new password on their behalf — nobody but the
     * account owner ever knows their own password.
     */
    public function sendPasswordReset(User $user): RedirectResponse
    {
        if ($user->isPendingActivation()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', __(':name hasn\'t activated their account yet — resend their invitation instead.', ['name' => $user->name]));
        }

        Password::sendResetLink(['email' => $user->email]);

        return redirect()->route('superadmin.users.index')
            ->with('status', __('Password reset link sent to :email.', ['email' => $user->email]));
    }

    /**
     * Deactivate an account rather than deleting it, so the user record is
     * retained for audit logs and historical references. Deactivated users
     * are blocked from logging in (and force-logged-out if already signed
     * in) by LoginRequest / EnsureAccountIsActive.
     */
    public function deactivate(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', __('You cannot deactivate your own account.'));
        }

        if ($this->isLastActiveSuperadmin($user)) {
            return redirect()->route('superadmin.users.index')
                ->with('error', __('You cannot deactivate the last remaining Superadmin.'));
        }

        $user->update(['is_active' => false]);

        return redirect()->route('superadmin.users.index')
            ->with('status', __(':name has been deactivated.', ['name' => $user->name]));
    }

    public function reactivate(User $user): RedirectResponse
    {
        $user->update(['is_active' => true]);

        return redirect()->route('superadmin.users.index')
            ->with('status', __(':name has been reactivated.', ['name' => $user->name]));
    }

    protected function isLastActiveSuperadmin(User $user): bool
    {
        return $user->role === UserRole::Superadmin
            && $user->is_active
            && $this->activeSuperadminCount() <= 1;
    }

    protected function activeSuperadminCount(): int
    {
        return User::where('role', UserRole::Superadmin)
            ->where('is_active', true)
            ->count();
    }
}
