<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends authenticatable implements filamentuser
{
    /** @use hasfactory<userfactory> */
    use hasfactory, notifiable;

    /**
     * the attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * the attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * get the user's initials
     */
    public function initials(): string
    {
        return str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function canAccessPanel(panel $panel): bool
    {
        return $this->isadmin();

    }

    protected function isadmin(): bool
    {
        return $this->email === 'info@raulsebastian.es' || $this->email === 'dtertre@surf3.es' || $this->email === 'ayuda@fundacionelenatertre.es';

    }

    /**
     * get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
