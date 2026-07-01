<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Services\PerpusnasIsbnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookWorkflowExecutePrimaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_primary_submits_isbn_when_state_ready_for_isbn(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $book = $this->createBook([
            'workflow_status' => 'ready_for_isbn',
        ]);

        $response = $this->actingAs($user)
            ->post(route('books.workflow.execute-primary', $book));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $book->refresh();
        $this->assertSame('isbn_submitted', $book->workflow_status);
        $this->assertNotNull($book->tanggal_pengajuan_isbn);
    }

    public function test_execute_primary_rejects_invalid_isbn_issue_date_when_state_isbn_submitted(): void
    {
        $user = User::factory()->create([
            'role' => 'isbn',
        ]);

        $book = $this->createBook([
            'workflow_status' => 'isbn_submitted',
        ]);

        $response = $this->actingAs($user)
            ->post(route('books.workflow.execute-primary', $book), [
                'isbn' => '9786020312345',
                'tanggal' => '2026-99-01',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $book->refresh();
        $this->assertSame('isbn_submitted', $book->workflow_status);
        $this->assertNull($book->tanggal_isbn_terbit);
    }

    public function test_execute_primary_approves_isbn_and_finishes_book_when_payload_is_valid(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $book = $this->createBook([
            'workflow_status' => 'isbn_submitted',
            'judul' => 'Buku Uji Perpusnas',
            'tahun_terbit' => 2026,
        ]);

        $this->mock(PerpusnasIsbnService::class, function ($mock): void {
            $mock->shouldReceive('verify')
                ->once()
                ->andReturn([
                    'verified' => true,
                    'message' => 'ISBN valid pada API mock test.',
                ]);
        });

        $response = $this->actingAs($user)
            ->post(route('books.workflow.execute-primary', $book), [
                'isbn' => '9786020312345',
                'tanggal' => '2026-07-01',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $book->refresh();
        $this->assertSame('selesai', $book->workflow_status);
        $this->assertSame('9786020312345', $book->isbn);
        $this->assertStringStartsWith('2026-07-01', (string) $book->tanggal_isbn_terbit);
    }

    public function test_execute_primary_allows_author_owner_to_approve_manuscript(): void
    {
        $author = User::factory()->create([
            'role' => 'author',
            'name' => 'Author Workflow Test',
        ]);

        $book = $this->createBook([
            'workflow_status' => 'acc_penulis',
            'author_user_id' => $author->id,
            'penulis_1' => $author->name,
        ]);

        $response = $this->actingAs($author)
            ->post(route('books.workflow.execute-primary', $book));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $book->refresh();
        $this->assertSame('audit_isbn', $book->workflow_status);
        $this->assertNotNull($book->tanggal_acc_penulis);
    }

    private function createBook(array $overrides = []): Book
    {
        $seed = strtoupper(substr(md5((string) microtime(true)), 0, 8));

        return Book::query()->create(array_merge([
            'nomor_naskah' => 'MS-' . $seed,
            'judul' => 'Judul Uji Workflow',
            'penulis_1' => 'Penulis Uji',
            'status' => 'draft',
            'workflow_status' => 'draft',
            'jumlah_cetak' => 100,
        ], $overrides));
    }
}
