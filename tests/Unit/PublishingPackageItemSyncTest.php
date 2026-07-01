<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\PublishingPackage;
use App\Models\PublishingPackageItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishingPackageItemSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_creates_package_items_from_selected_package(): void
    {
        $package = PublishingPackage::create([
            'name' => 'Paket Premium',
            'description' => 'Include advanced tasks',
            'includes_editing' => true,
            'includes_layout' => true,
            'includes_cover_design' => true,
            'includes_author_certificate' => true,
            'includes_google_scholar' => true,
            'requires_hki_registration' => true,
            'default_print_quantity' => 1000,
        ]);

        $package->items()->create([
            'name' => 'Sertifikat Penulis',
            'assigned_to_role' => 'editor',
            'is_required' => true,
        ]);

        $book = Book::create([
            'nomor_naskah' => 'PKG-001',
            'judul' => 'Testing Package',
            'penulis_1' => 'Author',
            'jumlah_cetak' => 500,
            'publishing_package_id' => $package->id,
        ]);

        $book->syncPackageItems();

        $items = $book->packageItems()->get();

        $this->assertCount(1, $items);
        $this->assertSame('Sertifikat Penulis', $items->first()->name);
        $this->assertFalse($items->first()->is_completed);
    }
}
