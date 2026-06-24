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
        'description',
        'amount',
        'status',
        'revision_stage',
        'revision_count',
        'due_date',
        'paid_at',
        'payment_proof',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'revision_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $invoice): void {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateNumber($invoice->type);
            }

            if (empty($invoice->due_date)) {
                $invoice->due_date = now()->addDays(14)->format('Y-m-d');
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

        // Prevent duplicate package invoices
        $existing = self::where('book_id', $book->id)
            ->where('type', 'package')
            ->first();

        if ($existing) {
            return $existing;
        }

        return self::create([
            'book_id' => $book->id,
            'user_id' => $authorUserId,
            'type' => 'package',
            'description' => 'Biaya Paket Penerbitan: ' . $package->name,
            'amount' => $package->price,
            'status' => 'pending',
            'notes' => 'Invoice diterbitkan otomatis saat paket penerbitan dipilih.',
        ]);
    }

    /**
     * Auto-create a revision invoice if this is not the first revision on this stage.
     * First revision per stage is free; subsequent revisions are charged.
     *
     * @param  float  $revisionFeeRate  Fraction of package price (default 15%)
     */
    public static function createRevisionInvoiceIfNeeded(
        Book $book,
        string $stage,
        float $revisionFeeRate = 0.15
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

        // First revision (count becomes 1 after this call) is free
        if ($revisionCount < 1) {
            return null;
        }

        $feeBase = optional($book->publishingPackage)->price ?? 0;
        $fee = round($feeBase * $revisionFeeRate, 2);

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
            'revision_count' => $revisionCount + 1,
            'description' => sprintf(
                'Biaya Revisi Ke-%d – Tahap %s (%s)',
                $revisionCount + 1,
                $stageLabel,
                $book->judul
            ),
            'amount' => $fee,
            'status' => 'pending',
            'notes' => 'Revisi pertama tiap tahap gratis. Revisi selanjutnya dikenakan biaya ' . ($revisionFeeRate * 100) . '% dari harga paket.',
        ]);
    }
}
