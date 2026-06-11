<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'role',
        'nip_staff',
        'no_sertifikat_notaris',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function akta(): HasMany
    {
        return $this->hasMany(Akta::class, 'id_user');
    }

    public function isNotaris(): bool
    {
        return $this->role === 'Notaris';
    }

    public function isAdminStaff(): bool
    {
        return $this->role === 'AdminStaff';
    }
}
