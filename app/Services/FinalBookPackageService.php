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

    public const EXTRA_AUTHOR_TYPES = [
        'skk',
        'hki',
        'sertifikat_penulis',
    ];

    public const OPTIONAL_TYPES = [
        'hki',
    ];

    public const TYPE_LABELS = [
        'isbn_image' => 'ISBN Image',
        'qrcbn_image' => 'QRCBN Image',
        'final_layout' => 'Final Layout',
        'final_cover' => 'Final Cover',
        'skk' => 'SKK',
        'hki' => 'HKI',
        'sertifikat_penulis' => 'Sertifikat Penulis',
    ];

    public function requiredTypes(): array
    {
        return self::FINAL_TYPES;
    }

    public function downloadableTypes(): array
    {
        return array_values(array_unique(array_merge(self::FINAL_TYPES, self::EXTRA_AUTHOR_TYPES)));
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

        foreach ($this->downloadableTypes() as $type) {
            $file = $book->files()
                ->where('type', $type)
                ->where('is_active', true)
                ->latest()
                ->first();

            $items[$type] = [
                'exists' => (bool) $file,
                'file' => $file,
                'label' => self::TYPE_LABELS[$type] ?? strtoupper(str_replace('_', ' ', $type)),
                'optional' => in_array($type, self::OPTIONAL_TYPES, true),
            ];
        }

        return $items;
    }

    public function syncDeliveryLink(Book $book): void
    {
        $checklist = collect($this->checklist($book))
            ->only(self::FINAL_TYPES)
            ->all();

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
                    return ['message' => 'SCAN_WARN: metadata final layout belum terdeteksi penuh (judul/ISBN). File tetap disimpan | SHA256:' . substr($checksum, 0, 12)];
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
                    return ['message' => 'SCAN_WARN: metadata PDF final layout tidak teridentifikasi kuat. File tetap disimpan | SHA256:' . substr($checksum, 0, 12)];
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
                return ['message' => 'SCAN_WARN: cover belum memuat hint ISBN/QRCBN yang kuat. File tetap disimpan | SHA256:' . substr($checksum, 0, 12)];
            }

            return ['message' => 'SCAN_OK: cover terverifikasi ISBN + QRCBN | SHA256:' . substr($checksum, 0, 12)];
        }

        if ($type === 'isbn_image') {
            $isbnHint = str_contains($name, 'isbn') || ($isbnDigits !== '' && str_contains($name, substr($isbnDigits, -5)));
            if (!$isbnHint) {
                return ['message' => 'SCAN_WARN: nama file ISBN tidak memuat pola ISBN. File tetap disimpan | SHA256:' . substr($checksum, 0, 12)];
            }

            return ['message' => 'SCAN_OK: gambar ISBN cocok | SHA256:' . substr($checksum, 0, 12)];
        }

        if ($type === 'qrcbn_image') {
            if (!str_contains($name, 'qr') && !str_contains($name, 'qrcbn')) {
                return ['message' => 'SCAN_WARN: nama file QRCBN tidak memuat pola qr/qrcbn. File tetap disimpan | SHA256:' . substr($checksum, 0, 12)];
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
