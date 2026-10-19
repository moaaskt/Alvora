<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }

    /**
     * Get the company that owns the user (D-02: 1 user belongs strictly to 1 company).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if user is an Alvora platform admin (D-03).
     */
    public function isAlvoraAdmin(): bool
    {
        return $this->role === UserRole::ADMIN_ALVORA;
    }

    /**
     * Check if user is a Company Admin (RH).
     */
    public function isCompanyAdmin(): bool
    {
        return $this->role === UserRole::COMPANY_ADMIN;
    }

    /**
     * Check if user is an Instructor.
     */
    public function isInstructor(): bool
    {
        return $this->role === UserRole::INSTRUCTOR;
    }

    /**
     * Check if user is a Student / Employee.
     */
    public function isStudent(): bool
    {
        return $this->role === UserRole::STUDENT;
    }

    /**
     * Check if user has specific role or roles.
     *
     * @param UserRole|string|array<UserRole|string> $roles
     */
    public function hasRole(UserRole|string|array $roles): bool
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }

        $roleValue = $roles instanceof UserRole ? $roles->value : $roles;
        $userRoleValue = $this->role instanceof UserRole ? $this->role->value : $this->role;

        return $userRoleValue === $roleValue;
    }
}
