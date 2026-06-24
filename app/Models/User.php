<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\BookAssignment;

#[Fillable([
    'name',
    'email',
    'password',
    'role'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isEditor()
    {
        return $this->role === 'editor';
    }

    public function isLayouter()
    {
        return $this->role === 'layouter';
    }

    public function isISBN()
    {
        return $this->role === 'isbn';
    }

    public function isOwner()
    {
        return $this->role === 'owner';
    }

    public function assignments()
    {
        return $this->hasMany(
            BookAssignment::class
        );
    }

    public function isDesigner()
    {
        return $this->role === 'designer';
    }

    public function isAuthor()
    {
        return $this->role === 'author';
    }

    public function books()
    {
        return $this->hasMany(
            Book::class,
            'user_id'
        );
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }
    public function notifications()
    {
        return $this->hasMany(
            Notification::class
        );
    }
}
