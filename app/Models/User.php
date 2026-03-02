<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_QA = 'qa';
    public const ROLE_CS = 'cs';
    public const ROLE_SALES = 'sales';
    public const ROLE_MARKETING = 'marketing';
    public const ROLE_PPIC = 'ppic';
    public const ROLE_VIEWER = 'viewer';

    public const ROLE_OPTIONS = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_QA,
        self::ROLE_CS,
        self::ROLE_SALES,
        self::ROLE_MARKETING,
        self::ROLE_PPIC,
        self::ROLE_VIEWER,
    ];

    public const DEPT_GENERAL = 'general';
    public const DEPT_QA = 'qa';
    public const DEPT_PPIC = 'ppic';
    public const DEPT_MARKETING = 'marketing';
    public const DEPT_SALES = 'sales';
    public const DEPT_CS = 'customer_service';
    public const DEPT_MANAGEMENT = 'management';

    public const DEPARTMENT_OPTIONS = [
        self::DEPT_QA,
        self::DEPT_PPIC,
        self::DEPT_MARKETING,
        self::DEPT_SALES,
        self::DEPT_CS,
        self::DEPT_MANAGEMENT,
        self::DEPT_GENERAL,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'department',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
