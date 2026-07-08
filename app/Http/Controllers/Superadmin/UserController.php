<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
        ]);
    }

    public function create(): View
    {
        return view('superadmin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->validated(),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('status', 'User account created successfully.');
    }

    public function edit(User $user): View|RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('profile.edit')
                ->with('status', 'Use this page to edit your own account.');
        }

        return view('superadmin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('profile.edit')
                ->with('status', 'Use this page to edit your own account.');
        }

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('superadmin.users.index')
            ->with('status', 'User account updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('status', 'User account deleted successfully.');
    }
}
