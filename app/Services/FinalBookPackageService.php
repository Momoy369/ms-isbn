<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FinalBookPackageService
{
    public const FINAL_TYPES = [
        'isbn_image',
        'qrcbn_image',
        'final_layout',
        'final_cover',
    ];

    public function requiredTypes(): array
    {
        return self::FINAL_TYPES;
    }

    public function validateAndStore(Book $book, string $type, UploadedFile $file, ?string $note, string $senderRole): BookFile
    {
        if (!in_array($type, self::FINAL_TYPES, true)) {
            throw new \InvalidArgumentException('Tipe file final tidak valid.');
        }

        $scan = $this->scanFile($book, $type, $file, $note);

        BookFile::where('book_id', $book->id)
            ->where('type', $type)
            ->update(['is_active' => false]);

        $folderName = Str::slug($book->judul ?: $book->nomor_naskah ?: ('book-' . $book->id));
        $path = $file->store('final-books/' . $folderName . '/' . $type, 'public');

        $version = (int) (BookFile::where('book_id', $book->id)
            ->where('type', $type)
            ->max('version') ?? 0) + 1;

        $record = BookFile::create([
            'book_id' => $book->id,
            'type' => $type,
            'original_name' => $file->getClientOriginalName(),
            'note' => trim(($note ? $note . ' | ' : '') . $scan['message']),
            'sender_role' => $senderRole,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'is_active' => true,
            'version' => $version,
        ]);

        $this->syncDeliveryLink($book);

        return $record;
    }

    public function checklist(Book $book): array
    {
        $items = [];

        foreach (self::FINAL_TYPES as $type) {
            $file = $book->files()
                ->where('type', $type)
                ->where('is_active', true)
                ->latest()
                ->first();

            $items[$type] = [
                'exists' => (bool) $file,
                'file' => $file,
            ];
        }

        return $items;
    }

    public function syncDeliveryLink(Book $book): void
    {
        $checklist = $this->checklist($book);
        $allReady = collect($checklist)->every(fn($row) => $row['exists'] === true);

        if ($allReady) {
            $book->update([
                'final_drive_link' => route('author.books.final-files.index', $book),
            ]);
        }
    }

    private function scanFile(Book $book, string $type, UploadedFile $file, ?string $note): array
    {
        $name = strtolower($file->getClientOriginalName());
        $isbn = strtolower((string) ($book->isbn ?? ''));
        $title = strtolower((string) ($book->judul ?? ''));

        if ($type === 'final_layout') {
            $ext = strtolower((string) $file->getClientOriginalExtension());
            if (!in_array($ext, ['docx', 'pdf'], true)) {
                throw new \RuntimeException('Final layout harus DOCX atau PDF.');
            }

            if ($ext === 'docx') {
                $tmpPath = $file->getRealPath() ?: '';
                $text = strtolower((string) app(DocxAnalyzerService::class)->extractText($tmpPath));
                $titleOk = $title !== '' && str_contains($text, Str::lower(Str::limit($book->judul, 20, '')));
                $isbnOk = $isbn === '' ? true : str_contains($text, $isbn);

                if (!$titleOk || !$isbnOk) {
                    throw new \RuntimeException('Scan metadata final layout gagal: judul/ISBN belum terdeteksi di dokumen.');
                }
            }

            return ['message' => 'SCAN_OK: metadata final layout valid'];
        }

        if ($type === 'final_cover') {
            $noteText = strtolower((string) $note);
            $hasIsbnHint = str_contains($noteText, 'isbn') || str_contains($name, 'isbn');
            $hasQrcbnHint = str_contains($noteText, 'qrcbn') || str_contains($name, 'qrcbn') || str_contains($name, 'qr');

            if (!$hasIsbnHint || !$hasQrcbnHint) {
                throw new \RuntimeException('Scan cover gagal: sertakan penanda ISBN dan QRCBN pada catatan/nama file.');
            }

            return ['message' => 'SCAN_OK: cover mengandung indikator ISBN + QRCBN'];
        }

        if ($type === 'isbn_image' && !str_contains($name, 'isbn')) {
            throw new \RuntimeException('File gambar ISBN sebaiknya mengandung kata isbn pada nama file.');
        }

        if ($type === 'qrcbn_image' && !str_contains($name, 'qr')) {
            throw new \RuntimeException('File gambar QRCBN sebaiknya mengandung kata qr/qrcbn pada nama file.');
        }

        return ['message' => 'SCAN_OK: file final tervalidasi'];
    }
}
