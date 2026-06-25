<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuthorInvoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'book_id',
        'user_id',
        'type',
        'is_package_billing',
        'installment_number',
        'description',
        'amount',
        'status',
        'revision_stage',
        'revision_count',
        'due_date',
        'paid_at',
        'payment_proof',
        'payment_method',
        'payment_gateway',
        'gateway_reference',
        'gateway_checkout_url',
        'gateway_expires_at',
        'payment_reference',
        'verified_by_user_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'gateway_expires_at' => 'datetime',
        'revision_count' => 'integer',
        'is_package_billing' => 'boolean',
        'installment_number' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $invoice): void {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateNumber($invoice->type);
            }
        });
    }

    public static function generateNumber(string $type): string
    {
        $prefix = match ($type) {
            'revision' => 'INV-REV',
            'additional' => 'INV-ADD',
            default => 'INV-PKG',
        };

        $year = now()->format('Y');
        $month = now()->format('m');

        $lastNumber = self::where('invoice_number', 'like', "{$prefix}-{$year}{$month}%")
            ->lockForUpdate()
            ->count();

        $sequence = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}{$month}-{$sequence}";
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getTypeLabel(): string
    {
        if ($this->is_package_billing && $this->installment_number === 1) {
            return 'Paket Penerbitan (DP 50%)';
        }

        if ($this->is_package_billing && $this->installment_number === 2) {
            return 'Paket Penerbitan (Pelunasan 50%)';
        }

        return match ($this->type) {
            'package' => 'Paket Penerbitan',
            'revision' => 'Revisi Berbayar',
            'additional' => 'Layanan Tambahan',
            default => ucfirst($this->type),
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'paid' => 'success',
            'cancelled' => 'secondary',
            default => 'light',
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForAuthor($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePackageBilling($query)
    {
        return $query->where('is_package_billing', true);
    }

    /**
     * Auto-create a package invoice when a book is linked to a publishing package.
     */
    public static function createPackageInvoice(Book $book): ?self
    {
        $package = $book->publishingPackage;

        if (!$package || $package->price <= 0) {
            return null;
        }

        $authorUserId = $book->author_user_id;

        if (!$authorUserId) {
            return null;
        }

        // Prevent duplicate first installment package invoices
        $existing = self::where('book_id', $book->id)
            ->where('is_package_billing', true)
            ->where('installment_number', 1)
            ->first();

        if ($existing) {
            return $existing;
        }

        $dpAmount = round(((float) $package->price) * 0.5, 2);

        return self::create([
            'book_id' => $book->id,
            'user_id' => $authorUserId,
            'type' => 'package',
            'is_package_billing' => true,
            'installment_number' => 1,
            'description' => 'DP 50% Paket Penerbitan: ' . $package->name,
            'amount' => $dpAmount,
            'status' => 'pending',
            'notes' => 'Invoice DP 50% diterbitkan saat paket penerbitan dipilih.',
        ]);
    }

    /**
     * Auto-create final installment invoice once production is complete.
     */
    public static function createFinalPackageInvoice(Book $book): ?self
    {
        $package = $book->publishingPackage;

        if (!$package || $package->price <= 0 || !$book->author_user_id) {
            return null;
        }

        $existing = self::where('book_id', $book->id)
            ->where('is_package_billing', true)
            ->where('installment_number', 2)
            ->first();

        if ($existing) {
            return $existing;
        }

        $finalAmount = ((float) $package->price) - round(((float) $package->price) * 0.5, 2);

        if ($finalAmount <= 0) {
            return null;
        }

        return self::create([
            'book_id' => $book->id,
            'user_id' => $book->author_user_id,
            'type' => 'package',
            'is_package_billing' => true,
            'installment_number' => 2,
            'description' => 'Pelunasan 50% Paket Penerbitan: ' . $package->name,
            'amount' => $finalAmount,
            'status' => 'pending',
            'notes' => 'Invoice pelunasan diterbitkan saat produksi dinyatakan selesai.',
        ]);
    }

    /**
     * Auto-create a revision invoice if this is not the first revision on this stage.
     * First revision per stage is free; subsequent revisions use the admin-defined nominal fee.
     */
    public static function createRevisionInvoiceIfNeeded(
        Book $book,
        string $stage
    ): ?self {
        $authorUserId = $book->author_user_id;

        if (!$authorUserId) {
            return null;
        }

        // Count previous revisions (status = 'revision') on this stage
        $revisionCount = \App\Models\BookReview::where('book_id', $book->id)
            ->where('stage', $stage)
            ->where('status', 'revision')
            ->count();

        // First revision is free. Because the review record is already created before this method runs,
        // a first revision produces count=1 and must not create an invoice.
        if ($revisionCount <= 1) {
            return null;
        }

        $fee = round((float) ($book->revision_fee_amount ?? 0), 2);

        if ($fee <= 0) {
            return null;
        }

        $stageLabel = match ($stage) {
            'editing' => 'Editing',
            'layout' => 'Layout',
            'cover' => 'Desain Sampul',
            default => ucfirst($stage),
        };

        return self::create([
            'book_id' => $book->id,
            'user_id' => $authorUserId,
            'type' => 'revision',
            'revision_stage' => $stage,
            'revision_count' => $revisionCount,
            'description' => sprintf(
                'Biaya Revisi Ke-%d – Tahap %s (%s)',
                $revisionCount,
                $stageLabel,
                $book->judul
            ),
            'amount' => $fee,
            'status' => 'pending',
            'notes' => 'Revisi pertama tiap tahap gratis. Revisi berikutnya menggunakan nominal biaya revisi yang diinput admin.',
        ]);
    }
}
