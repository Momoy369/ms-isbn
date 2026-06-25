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
    'role',
    'ktp_number',
    'ktp_name',
    'phone',
    'address',
    'birth_date',
    'bank_name',
    'bank_account_number',
    'bank_account_holder',
    'bank_branch',
    'is_profile_complete'
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
            'birth_date' => 'date',
            'is_profile_complete' => 'boolean',
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

    public function isFinance()
    {
        return $this->role === 'finance';
    }

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
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

    public function externalSalesInputs()
    {
        return $this->hasMany(ExternalSalesRecord::class, 'input_by_user_id');
    }

    public function authorOrders()
    {
        return $this->hasMany(AuthorBookOrder::class);
    }

    public function royaltyPayoutRequests()
    {
        return $this->hasMany(AuthorRoyaltyPayoutRequest::class, 'author_user_id');
    }

    public function royaltyLedgers()
    {
        return $this->hasMany(AuthorRoyaltyLedger::class, 'author_user_id');
    }

    public function isAuthorProfileComplete(): bool
    {
        return !empty($this->ktp_number)
            && !empty($this->ktp_name)
            && !empty($this->phone)
            && !empty($this->address)
            && !empty($this->birth_date);
    }
}
