<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\LogsAuditActivity;
use App\Enums\UserInvitationStatus;
use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\UserInvitationNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, LogsAuditActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'avatar_path',
        'locale',
        'invitation_token',
        'invitation_expires_at',
        'invited_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'invitation_expires_at' => 'datetime',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * A user who hasn't set their own password yet is still mid-invitation —
     * they exist in the system (so a Superadmin can see/manage/resend their
     * invite) but cannot sign in until they complete activation.
     */
    public function invitationStatus(): UserInvitationStatus
    {
        if ($this->password !== null) {
            return UserInvitationStatus::Active;
        }

        if ($this->invitation_expires_at && $this->invitation_expires_at->isPast()) {
            return UserInvitationStatus::Expired;
        }

        return UserInvitationStatus::Pending;
    }

    public function isPendingActivation(): bool
    {
        return $this->password === null;
    }

    /**
     * Issue a fresh invitation token (invalidating any previous one) and
     * email it to the user. Used both when a Superadmin first invites
     * someone and when resending an expired/unused invitation.
     */
    public function sendInvitation(): void
    {
        $token = Str::random(64);

        $this->forceFill([
            'invitation_token' => Hash::make($token),
            'invitation_expires_at' => now()->addDays(7),
        ])->save();

        $this->notify(new UserInvitationNotification($token));
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name));

        $initials = collect($words)->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('');

        return mb_strtoupper($initials) ?: '?';
    }
}
