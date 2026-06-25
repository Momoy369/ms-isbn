<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AssignmentHistory;
use App\Models\User;
use App\Models\BookReview;
use App\Models\BookChapter;
use App\Models\BookClaimRequest;

/**
 * @property \Illuminate\Database\Eloquent\Collection $chapters
 */

class Book extends Model
{
    public const ISBN_AUDIT_REQUIRED_FILES = [
        'cover',
        'skk',
        'halaman_judul',
        'surat_permohonan',
        'copyright',
    ];

    public const ISBN_PACKAGE_REQUIRED_FILES = [
        'cover',
        'naskah_final',
        'halaman_judul',
        'copyright',
        'surat_permohonan',
        'attachment_isbn',
    ];

    protected $fillable = [

        'nomor_naskah',

        'nomor_surat',

        'judul',

        'subjudul',

        'penulis_1',

        'author_ktp_number',

        'penulis_2',

        'penulis_3',

        'nama_pena',

        'kategori',

        'link_produk',

        'final_drive_link',

        'final_ebook_link',

        'jumlah_cetak',

        'tahun_terbit',

        'isbn',

        'editor',

        'layouter',

        'jumlah_halaman',

        'selling_price',

        'revision_fee_amount',

        'royalty_enabled',

        'royalty_rate',

        'royalty_distribution_online',

        'royalty_distribution_ebook',

        'royalty_distribution_marketplace',

        'royalty_agreement_file_path',

        'royalty_contract_file_path',

        'royalty_notes',

        'royalty_enabled_at',

        'royalty_enabled_by_user_id',

        'royalty_contract_status',

        'royalty_contract_version',

        'royalty_contract_sent_at',

        'royalty_contract_accepted_at',

        'royalty_contract_rejected_at',

        'royalty_contract_acknowledgement',

        'ukuran_buku',

        'cetakan',

        'designer',

        'tahun_copyright',

        'workflow_status',

        'status',

        'metadata_locked',

        'tanggal_pengajuan_isbn',

        'tanggal_isbn_terbit',

        'tanggal_mulai_editing',

        'tanggal_mulai_layout',

        'tanggal_acc_penulis',

        'tanggal_mulai_cover',

        'author_user_id',

        'claimed_at',

        'links_unlocked_manually',

        'links_unlocked_at',

        'links_unlocked_by_user_id',

        'book_type',

        'publishing_package_id',
    ];

    protected $casts = [
        'royalty_enabled' => 'boolean',
        'royalty_distribution_online' => 'boolean',
        'royalty_distribution_ebook' => 'boolean',
        'royalty_distribution_marketplace' => 'boolean',
        'royalty_rate' => 'decimal:4',
        'royalty_enabled_at' => 'datetime',
        'royalty_contract_sent_at' => 'datetime',
        'royalty_contract_accepted_at' => 'datetime',
        'royalty_contract_rejected_at' => 'datetime',
        'royalty_contract_version' => 'integer',
    ];

    public function files()
    {
        return $this->hasMany(BookFile::class);
    }

    public function audits()
    {
        return $this->hasMany(
            AuditResult::class
        );
    }

    public function sections()
    {
        return $this->hasMany(
            ManuscriptSection::class
        );
    }

    public function metadata()
    {
        return $this->hasMany(
            BookMetadata::class
        );
    }

    public function contents()
    {
        return $this->hasMany(
            DocumentContent::class
        );
    }

    public function activeFiles()
    {
        return $this->hasMany(
            BookFile::class
        )->where(
                'is_active',
                true
            );
    }

    public function documentContents()
    {
        return $this->hasManyThrough(
            DocumentContent::class,
            BookFile::class
        );
    }

    public function getActiveFile(
        string $type
    ) {
        return $this->files()
            ->where(
                'type',
                $type
            )
            ->where(
                'is_active',
                true
            )
            ->latest()
            ->first();
    }

    public function fileHistories()
    {
        return $this->hasMany(
            BookFile::class
        )
            ->orderByDesc('created_at');
    }

    public const WORKFLOWS = [

        'draft',
        'editing',
        'editing_review',
        'layout',
        'layout_review',
        'cover_design',
        'cover_review',
        'acc_penulis',
        'audit_isbn',
        'ready_for_isbn',
        'isbn_submitted',
        'isbn_approved',
        'selesai'

    ];

    public function workflowIndex()
    {
        return array_search(

            $this->workflow_status,

            self::WORKFLOWS

        );
    }

    public function author()
    {
        return $this->belongsTo(
            User::class,
            'author_user_id'
        );
    }

    public function royaltyEnabledBy()
    {
        return $this->belongsTo(User::class, 'royalty_enabled_by_user_id');
    }

    public function publishingPackage()
    {
        return $this->belongsTo(
            PublishingPackage::class,
            'publishing_package_id'
        );
    }

    public function assignments()
    {
        return $this->hasMany(
            BookAssignment::class
        );
    }

    public function approvals()
    {
        return $this->hasMany(
            BookApproval::class
        );
    }

    public function activities()
    {
        return $this->hasMany(
            BookActivity::class
        )
            ->latest();
    }

    public function syncAssignments()
    {
        $this->syncAssignmentRole(
            'editor',
            $this->editor,
            3
        );

        $this->syncAssignmentRole(
            'layouter',
            $this->layouter,
            2
        );

        $this->syncAssignmentRole(
            'designer',
            $this->designer,
            2
        );
    }

    private function syncAssignmentRole(
        string $role,
        ?string $personName,
        int $slaDays
    ) {
        if (!$personName) {
            return;
        }

        $user = User::where(
            'name',
            $personName
        )->first();

        $assignment =
            $this->assignments()
                ->where(
                    'role',
                    $role
                )
                ->first();

        if (!$assignment) {

            $this->assignments()
                ->create([

                    'role' =>
                        $role,

                    'user_id' =>
                        $user?->id,

                    'person_name' =>
                        $personName,

                    'assigned_at' =>
                        now(),

                    'sla_days' =>
                        $slaDays,

                    'deadline_at' =>
                        now()->addDays(
                            $slaDays
                        )

                ]);

            $this->assignmentHistories()
                ->create([

                    'role' =>
                        $role,

                    'activity' =>
                        'assigned',

                    'new_person' =>
                        $personName

                ]);

            return;
        }

        if (
            $assignment->person_name
            !==
            $personName
        ) {

            $this->assignmentHistories()
                ->create([

                    'role' =>
                        $role,

                    'activity' =>
                        'reassigned',

                    'old_person' =>
                        $assignment
                            ->person_name,

                    'new_person' =>
                        $personName

                ]);
        }

        $assignment->update([

            'user_id' =>
                $user?->id,

            'person_name' =>
                $personName

        ]);
    }

    public function assignmentHistories()
    {
        return $this->hasMany(
            AssignmentHistory::class
        );
    }

    public function workflowSteps(): array
    {
        $baseSteps = [
            'draft',
            'editing',
            'editing_review',
            'layout',
            'layout_review',
            'cover_design',
            'cover_review',
            'acc_penulis',
            'audit_isbn',
            'ready_for_isbn',
            'isbn_submitted',
            'isbn_approved',
            'selesai',
        ];

        $package = $this->relationLoaded('publishingPackage')
            ? $this->publishingPackage
            : $this->publishingPackage()->first();

        if ($package && !$package->includes_editing) {
            $baseSteps = array_values(array_filter($baseSteps, static fn($step) => $step !== 'editing' && $step !== 'editing_review'));
        }

        return $baseSteps;
    }

    public function nextWorkflowStatus(): ?string
    {
        $steps = $this->workflowSteps();
        $currentIndex = array_search($this->workflow_status, $steps, true);

        if ($currentIndex === false) {
            return null;
        }

        return $steps[$currentIndex + 1] ?? null;
    }

    public function progressPercent()
    {
        $package = $this->relationLoaded('publishingPackage')
            ? $this->publishingPackage
            : $this->publishingPackage()->first();

        if ($package && !$package->includes_editing) {
            $progressSteps = [
                'draft' => 0,
                'layout' => 36,
                'layout_review' => 45,
                'cover_design' => 55,
                'cover_review' => 65,
                'acc_penulis' => 75,
                'audit_isbn' => 80,
                'ready_for_isbn' => 85,
                'isbn_submitted' => 90,
                'isbn_approved' => 100,
                'selesai' => 100,
            ];

            return $progressSteps[$this->workflow_status] ?? 0;
        }

        $steps = [
            'draft' => 0,
            'editing' => 10,
            'editing_review' => 20,
            'layout' => 30,
            'layout_review' => 40,
            'cover_design' => 50,
            'cover_review' => 60,
            'acc_penulis' => 70,
            'audit_isbn' => 80,
            'ready_for_isbn' => 85,
            'isbn_submitted' => 90,
            'isbn_approved' => 100,
            'selesai' => 100,
        ];

        return $steps[$this->workflow_status] ?? 0;
    }

    public function workflowDate(
        string $workflow
    ) {
        return match ($workflow) {

            'editing'
            => $this->tanggal_mulai_editing,

            'layout'
            => $this->tanggal_mulai_layout,

            'acc_penulis'
            => $this->tanggal_acc_penulis,

            'isbn_submitted'
            => $this->tanggal_pengajuan_isbn,

            'isbn_approved'
            => $this->tanggal_isbn_terbit,

            'cover_design'
            => $this->tanggal_mulai_cover,

            default
            => null
        };
    }

    public function missingIsbnAuditFiles(): array
    {
        $missing = [];

        foreach (self::ISBN_AUDIT_REQUIRED_FILES as $type) {
            if (!$this->files()->where('type', $type)->exists()) {
                $missing[] = $type;
            }
        }

        return $missing;
    }

    public function missingIsbnPackageFiles(): array
    {
        $missing = [];

        foreach (self::ISBN_PACKAGE_REQUIRED_FILES as $type) {
            if (!$this->files()->where('type', $type)->exists()) {
                $missing[] = $type;
            }
        }

        return $missing;
    }

    public function hasCompleteIsbnAuditFiles(): bool
    {
        return count($this->missingIsbnAuditFiles()) === 0;
    }

    public function hasCompleteIsbnPackageFiles(): bool
    {
        return count($this->missingIsbnPackageFiles()) === 0;
    }

    public function canSubmitIsbnToPerpusnas(): bool
    {
        return $this->workflow_status === 'ready_for_isbn';
    }

    public function canApproveIsbnIssued(): bool
    {
        return $this->workflow_status === 'isbn_submitted';
    }

    public function canGenerateIsbnPackage(): bool
    {
        return in_array($this->workflow_status, ['ready_for_isbn', 'isbn_submitted', 'isbn_approved', 'selesai'], true);
    }

    public function isRoyaltyEligible(): bool
    {
        return (bool) $this->royalty_enabled;
    }

    public function isRoyaltyContractAccepted(): bool
    {
        return $this->royalty_contract_status === 'accepted';
    }

    public function royaltyRate(): float
    {
        $rate = (float) ($this->royalty_rate ?? 0);

        if ($rate <= 0) {
            return 0.20;
        }

        return min($rate, 1.00);
    }

    public function canDistributeByChannel(string $channel, string $format): bool
    {
        if (!$this->isRoyaltyEligible()) {
            return false;
        }

        if ($format === 'ebook' && !$this->royalty_distribution_ebook) {
            return false;
        }

        if ($channel === 'marketplace') {
            return (bool) $this->royalty_distribution_marketplace;
        }

        return (bool) $this->royalty_distribution_online;
    }

    public function reviews()
    {
        return $this->hasMany(
            BookReview::class
        );
    }

    public function claimRequests()
    {
        return $this->hasMany(BookClaimRequest::class);
    }

    public function packageItems()
    {
        return $this->hasMany(BookPackageItem::class)->orderBy('created_at');
    }

    public function authorInvoices()
    {
        return $this->hasMany(\App\Models\AuthorInvoice::class);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\AuthorBookOrder::class);
    }

    public function externalSales()
    {
        return $this->hasMany(\App\Models\ExternalSalesRecord::class);
    }

    public function storeCatalogItems()
    {
        return $this->hasMany(StoreCatalogItem::class);
    }

    public function serviceOrders()
    {
        return $this->hasMany(\App\Models\AuthorServiceOrder::class);
    }

    public function royaltyLedgers()
    {
        return $this->hasMany(AuthorRoyaltyLedger::class);
    }

    public function effectiveSellingPrice(): float
    {
        if (!empty($this->selling_price) && $this->selling_price > 0) {
            return (float) $this->selling_price;
        }

        $pages = (int) ($this->jumlah_halaman ?: 0);

        if ($pages > 0) {
            $baseCost = $pages * 192;

            return round($baseCost * 2.5, 2);
        }

        $pkgPrice = (float) optional($this->publishingPackage)->price;

        return $pkgPrice > 0 ? $pkgPrice : 80000;
    }

    public function initialPackageInvoice(): ?AuthorInvoice
    {
        return $this->authorInvoices()
            ->where('is_package_billing', true)
            ->where('installment_number', 1)
            ->first();
    }

    public function hasPaidInitialPackageInvoice(): bool
    {
        $invoice = $this->initialPackageInvoice();

        if (!$invoice) {
            return true;
        }

        return $invoice->isPaid();
    }

    public function dpPaymentWarningMessage(): string
    {
        $invoice = $this->initialPackageInvoice();

        if (!$invoice) {
            return 'Invoice DP 50% belum tersedia untuk buku ini.';
        }

        return 'Tahap tidak dapat dilanjutkan sebelum invoice DP 50% #' . $invoice->invoice_number . ' dibayar.';
    }

    public function canAuthorAccessDeliveryLinks(): bool
    {
        if ($this->links_unlocked_manually) {
            return true;
        }

        if ($this->workflow_status !== 'selesai') {
            return false;
        }

        $packageInvoices = $this->authorInvoices()
            ->where('is_package_billing', true)
            ->get();

        if ($packageInvoices->isEmpty()) {
            return false;
        }

        $hasFinalInstallment = $packageInvoices->contains(
            fn($invoice) => (int) ($invoice->installment_number ?? 0) === 2
        );

        if (!$hasFinalInstallment) {
            return false;
        }

        return $packageInvoices->every(fn($invoice) => $invoice->isPaid());
    }

    public function hasOutstandingPackageBalance(): bool
    {
        return $this->authorInvoices()
            ->where('is_package_billing', true)
            ->where('status', '!=', 'paid')
            ->exists();
    }

    public function syncPackageItems(): void
    {
        $package = $this->publishingPackage()->first();

        if (!$package) {
            return;
        }

        $existingNames = [];

        foreach ($package->items as $item) {
            $record = $this->packageItems()->where('publishing_package_item_id', $item->id)->first();

            if (!$record) {
                $record = $this->packageItems()->create([
                    'publishing_package_item_id' => $item->id,
                    'name' => $item->name,
                    'assigned_to_role' => $item->assigned_to_role,
                    'is_required' => $item->is_required,
                ]);
            }

            $existingNames[] = $record->id;
        }

        $this->packageItems()->whereNotIn('id', $existingNames)->delete();
    }

    public function editorAssignment()
    {
        return $this->assignments()
            ->where('role', 'editor');
    }

    public function layouterAssignment()
    {
        return $this->assignments()
            ->where('role', 'layouter');
    }

    public function designerAssignment()
    {
        return $this->assignments()
            ->where('role', 'designer');
    }

    public function messages()
    {
        return $this->hasMany(
            BookMessage::class
        )->latest();
    }

    public function notificationRecipients()
    {
        $users = collect();

        if ($this->author) {
            $users->push($this->author);
        }

        foreach ($this->assignments as $assignment) {

            if ($assignment->user) {
                $users->push(
                    $assignment->user
                );
            }
        }

        return $users
            ->unique('id');
    }

    public function chapters()
    {
        return $this->hasMany(
            BookChapter::class
        )
            ->orderBy('chapter_order');
    }

    public function sectionsGenerator()
    {
        return $this->hasMany(
            BookSection::class
        )
            ->orderBy('sort_order');
    }

    public function layoutTemplate()
    {
        return $this->belongsTo(
            LayoutTemplate::class
        );
    }

    public function getWordCount()
    {
        $total = 0;

        foreach (
            $this->sectionsGenerator
            as $section
        ) {

            $total += str_word_count(
                strip_tags(
                    $section->content
                )
            );
        }

        return $total;
    }

    public function getEstimatedPages()
    {
        $words =
            $this->getWordCount();

        return ceil(
            $words / 350
        );
    }

    public function getChapterCount()
    {
        return $this
            ->sectionsGenerator
            ->where(
                'section_type',
                'chapter'
            )
            ->count();
    }

    public function getSubChapterCount()
    {
        return $this
            ->sectionsGenerator
            ->where(
                'section_type',
                'subchapter'
            )
            ->count();
    }
}