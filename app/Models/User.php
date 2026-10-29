<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Boot model events.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (User $user) {
            if ($user->certificates()->exists()) {
                throw new \DomainException("Não é possível excluir o usuário '{$user->name}' pois ele possui certificados emitidos.");
            }
        });
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

    /**
     * User course enrollments.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Courses taught by instructor.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * User lessons progress.
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * User lessons notes.
     */
    public function lessonNotes(): HasMany
    {
        return $this->hasMany(LessonNote::class);
    }

    /**
     * Certificates earned by user.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Risk scores history for user.
     */
    public function riskScores(): HasMany
    {
        return $this->hasMany(RiskScore::class);
    }

    /**
     * Latest calculated risk score for user (relation).
     */
    public function latestRiskScore(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RiskScore::class)->latestOfMany('calculated_at');
    }

    /**
     * AI interventions sent to user.
     */
    public function interventions(): HasMany
    {
        return $this->hasMany(AiIntervention::class);
    }
}
