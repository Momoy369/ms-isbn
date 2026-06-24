<?php

namespace App\Services;

use App\Models\Book;
use PhpOffice\PhpWord\PhpWord;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class DocumentGeneratorService
{
    public function generateTitlePage(Book $book)
    {
        $template = new TemplateProcessor(
            storage_path(
                'app/templates/halaman_judul.docx'
            )
        );

        $template->setValue(
            'judul',
            $book->judul
        );

        $template->setValue(
            'subjudul',
            $book->subjudul ?? ''
        );

        $template->setValue(
            'penulis',
            $this->getAuthors($book)
        );

        $folder =
            $this->ensureBookFolder($book);

        $fileName =
            'halaman_judul.docx';

        $relativePath =
            'generated/' .
            $book->nomor_naskah .
            '/' .
            $fileName;

        $absolutePath =
            storage_path(
                'app/' .
                $relativePath
            );

        $template->saveAs(
            $absolutePath
        );

        return [

            'file_name' =>
                $fileName,

            'file_path' =>
                $relativePath

        ];
    }

    public function generateRequestLetter(Book $book)
    {
        $template = new TemplateProcessor(
            storage_path(
                'app/templates/surat_permohonan.docx'
            )
        );

        $template->setValue(
            'judul',
            $book->judul
        );

        $template->setValue(
            'subjudul',
            $book->subjudul ?: ''
        );

        $template->setValue(
            'penulis',
            $this->getAuthors($book)
        );

        $template->setValue(
            'link_produk',
            $book->link_produk
        );

        $template->setValue(
            'jumlah_cetak',
            $book->jumlah_cetak
        );

        $template->setValue(
            'tanggal',
            now()->format('d F Y')
        );

        $nomorSurat =
            $book->nomor_surat
            ?? $this->generateLetterNumber();

        $template->setValue(
            'nomor_surat',
            $nomorSurat
        );

        if (!$book->nomor_surat) {

            $book->update([
                'nomor_surat' => $nomorSurat
            ]);
        }

        $folder =
            storage_path(
                'app/generated/' .
                $book->nomor_naskah
            );

        if (!file_exists($folder)) {

            mkdir(
                $folder,
                0777,
                true
            );
        }

        $fileName =
            'surat_permohonan.docx';

        $relativePath =
            'generated/' .
            $book->nomor_naskah .
            '/' .
            $fileName;

        $absolutePath =
            storage_path(
                'app/' .
                $relativePath
            );

        $template->saveAs(
            $absolutePath
        );

        return [

            'file_name' =>
                $fileName,

            'file_path' =>
                $relativePath

        ];
    }

    private function generateLetterNumber()
    {
        $lastBook = Book::whereNotNull('nomor_surat')
            ->latest()
            ->first();

        $number = 1;

        if ($lastBook) {

            $lastNumber =
                intval(
                    substr(
                        $lastBook->nomor_surat,
                        0,
                        3
                    )
                );

            $number = $lastNumber + 1;
        }

        return sprintf(
            '%03d/MS/%s/%s',
            $number,
            now()->format('m'),
            now()->format('Y')
        );
    }

    public function generateCopyright(Book $book)
    {
        $template = new TemplateProcessor(
            storage_path(
                'app/templates/copyright.docx'
            )
        );

        $template->setValue(
            'judul',
            $book->judul
        );

        $template->setValue(
            'subjudul',
            $book->subjudul ?? ''
        );

        $template->setValue(
            'penulis',
            $this->getAuthors($book)
        );

        $template->setValue(
            'editor',
            $book->editor
        );

        $template->setValue(
            'layouter',
            $book->layouter
        );

        $template->setValue(
            'isbn',
            $book->isbn
        );

        $template->setValue(
            'jumlah_halaman',
            $book->jumlah_halaman
        );

        $template->setValue(
            'ukuran_buku',
            $book->ukuran_buku
        );

        $template->setValue(
            'cetakan',
            $book->cetakan
        );

        $template->setValue(
            'designer',
            $book->designer
        );

        $template->setValue(
            'tahun_copyright',
            $book->tahun_copyright
        );

        $template->setValue(
            'tahun_terbit',
            $book->tahun_terbit
        );

        $folder =
            $this->ensureBookFolder($book);

        $fileName =
            'copyright.docx';

        $relativePath =
            'generated/' .
            $book->nomor_naskah .
            '/' .
            $fileName;

        $absolutePath =
            storage_path(
                'app/' .
                $relativePath
            );

        $template->saveAs(
            $absolutePath
        );

        return [

            'file_name' =>
                $fileName,

            'file_path' =>
                $relativePath

        ];
    }

    private function ensureBookFolder(Book $book)
    {
        $folder = storage_path(
            'app/generated/' .
            $book->nomor_naskah
        );

        if (!file_exists($folder)) {

            mkdir(
                $folder,
                0777,
                true
            );
        }

        return $folder;
    }

    private function getAuthors(Book $book)
    {
        $authors = [];

        if (!empty($book->penulis_1)) {
            $authors[] = $book->penulis_1;
        }

        if (!empty($book->penulis_2)) {
            $authors[] = $book->penulis_2;
        }

        if (!empty($book->penulis_3)) {
            $authors[] = $book->penulis_3;
        }

        return implode("\n", $authors);
    }

    public function generateAttachment(
        Book $book
    ) {
        $phpWord = new PhpWord();

        $section =
            $phpWord->addSection();

        $section->addText(
            'ATTACHMENT ISBN'
        );

        $section->addTextBreak();

        $section->addText(
            'Judul: ' .
            $book->judul
        );

        $section->addText(
            'Penulis: ' .
            $book->penulis_1
        );

        $section->addText(
            'Nomor Naskah: ' .
            $book->nomor_naskah
        );

        $folder =
            $this->ensureBookFolder(
                $book
            );

        $fileName =
            'attachment_isbn.docx';

        $relativePath =
            'generated/' .
            $book->nomor_naskah .
            '/' .
            $fileName;

        $absolutePath =
            storage_path(
                'app/' .
                $relativePath
            );

        $phpWord->save(
            $absolutePath,
            'Word2007'
        );

        return [

            'file_name' =>
                $fileName,

            'file_path' =>
                $relativePath

        ];
    }
}