<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Http\UploadedFile;
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
        $this->assertMimeByType($type, $file);

        $name = strtolower($file->getClientOriginalName());
        $isbnDigits = $this->normalizeIsbn((string) ($book->isbn ?? ''));
        $title = (string) ($book->judul ?? '');
        $noteText = strtolower((string) $note);
        $checksum = hash_file('sha256', $file->getRealPath() ?: '');

        if ($type === 'final_layout') {
            $ext = strtolower((string) $file->getClientOriginalExtension());
            if (!in_array($ext, ['docx', 'pdf'], true)) {
                throw new \RuntimeException('Final layout harus DOCX atau PDF.');
            }

            if ($ext === 'docx') {
                $tmpPath = $file->getRealPath() ?: '';
                $text = (string) app(DocxAnalyzerService::class)->extractText($tmpPath);
                $titleOk = $this->containsTitleTokens($text, $title);
                $isbnOk = $isbnDigits === '' ? true : str_contains($this->normalizeIsbn($text), $isbnDigits);

                if (!$titleOk || !$isbnOk) {
                    throw new \RuntimeException('Scan metadata final layout gagal: judul/ISBN belum terdeteksi di dokumen.');
                }
            } else {
                $binaryContent = file_get_contents($file->getRealPath() ?: '');
                $binaryText = is_string($binaryContent) ? strtolower($binaryContent) : '';

                $isbnHintOk = $isbnDigits === ''
                    ? true
                    : str_contains(preg_replace('/[^0-9x]/', '', $binaryText) ?? '', strtolower($isbnDigits))
                    || str_contains($name, substr($isbnDigits, -5));

                $titleHintOk = $this->containsTitleTokens($name . ' ' . $noteText, $title);

                if (!$isbnHintOk || !$titleHintOk) {
                    throw new \RuntimeException('Scan metadata PDF belum valid: pastikan nama file/catatan memuat judul dan ISBN yang benar.');
                }
            }

            return ['message' => 'SCAN_OK: metadata final layout valid | SHA256:' . substr($checksum, 0, 12)];
        }

        if ($type === 'final_cover') {
            $hasIsbnHint = str_contains($noteText, 'isbn') || str_contains($name, 'isbn');
            $hasQrcbnHint = str_contains($noteText, 'qrcbn') || str_contains($name, 'qrcbn') || str_contains($name, 'qr');
            $isbnMatch = $isbnDigits === ''
                ? true
                : str_contains($noteText, $isbnDigits)
                || str_contains($name, $isbnDigits)
                || str_contains($noteText, substr($isbnDigits, -5));

            if (!$hasIsbnHint || !$hasQrcbnHint || !$isbnMatch) {
                throw new \RuntimeException('Scan cover gagal: sertakan penanda ISBN dan QRCBN pada catatan/nama file.');
            }

            return ['message' => 'SCAN_OK: cover terverifikasi ISBN + QRCBN | SHA256:' . substr($checksum, 0, 12)];
        }

        if ($type === 'isbn_image') {
            $isbnHint = str_contains($name, 'isbn') || ($isbnDigits !== '' && str_contains($name, substr($isbnDigits, -5)));
            if (!$isbnHint) {
                throw new \RuntimeException('File gambar ISBN tidak cocok: nama file harus memuat ISBN atau kata isbn.');
            }

            return ['message' => 'SCAN_OK: gambar ISBN cocok | SHA256:' . substr($checksum, 0, 12)];
        }

        if ($type === 'qrcbn_image') {
            if (!str_contains($name, 'qr') && !str_contains($name, 'qrcbn')) {
                throw new \RuntimeException('File gambar QRCBN tidak cocok: nama file harus memuat qr/qrcbn.');
            }

            return ['message' => 'SCAN_OK: gambar QRCBN cocok | SHA256:' . substr($checksum, 0, 12)];
        }

        return ['message' => 'SCAN_OK: file final tervalidasi | SHA256:' . substr($checksum, 0, 12)];
    }

    private function assertMimeByType(string $type, UploadedFile $file): void
    {
        $mime = strtolower((string) $file->getMimeType());

        if (in_array($type, ['isbn_image', 'qrcbn_image', 'final_cover'], true)) {
            if (!str_starts_with($mime, 'image/') && $mime !== 'application/pdf') {
                throw new \RuntimeException('File untuk ' . $type . ' harus berupa gambar/PDF.');
            }
        }

        if ($type === 'final_layout') {
            if (
                $mime !== 'application/pdf'
                && $mime !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ) {
                throw new \RuntimeException('Final layout harus berformat DOCX/PDF.');
            }
        }
    }

    private function normalizeIsbn(string $value): string
    {
        return preg_replace('/[^0-9Xx]/', '', strtolower($value)) ?? '';
    }

    private function containsTitleTokens(string $text, string $title): bool
    {
        $normalizedText = $this->normalizeFreeText($text);
        $normalizedTitle = $this->normalizeFreeText($title);

        if ($normalizedTitle === '' || $normalizedText === '') {
            return false;
        }

        if (str_contains($normalizedText, $normalizedTitle)) {
            return true;
        }

        $tokens = array_values(array_filter(explode(' ', $normalizedTitle), fn($token) => strlen($token) >= 4));
        if (empty($tokens)) {
            return false;
        }

        $matched = 0;
        foreach ($tokens as $token) {
            if (str_contains($normalizedText, $token)) {
                $matched++;
            }
        }

        return $matched >= min(2, count($tokens));
    }

    private function normalizeFreeText(string $value): string
    {
        $normalized = preg_replace('/[^a-z0-9\s]/ui', ' ', mb_strtolower($value)) ?? '';
        return preg_replace('/\s+/', ' ', trim($normalized)) ?? '';
    }
}
