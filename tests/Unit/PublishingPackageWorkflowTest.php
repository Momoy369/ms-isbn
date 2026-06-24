<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\PublishingPackage;
use PHPUnit\Framework\TestCase;

class PublishingPackageWorkflowTest extends TestCase
{
    public function test_package_without_editing_skips_editing_steps(): void
    {
        $book = new Book(['workflow_status' => 'draft']);
        $package = new PublishingPackage([
            'includes_editing' => false,
            'includes_layout' => true,
            'includes_cover_design' => true,
        ]);

        $book->setRelation('publishingPackage', $package);

        $this->assertSame([
            'draft',
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
        ], $book->workflowSteps());
    }

    public function test_progress_is_calculated_from_package_specific_steps(): void
    {
        $book = new Book(['workflow_status' => 'layout']);
        $package = new PublishingPackage([
            'includes_editing' => false,
            'includes_layout' => true,
            'includes_cover_design' => true,
        ]);

        $book->setRelation('publishingPackage', $package);

        $this->assertSame(36, $book->progressPercent());
    }
}
