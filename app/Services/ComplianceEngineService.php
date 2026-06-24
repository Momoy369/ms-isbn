<?php

namespace App\Services;

use App\Models\Book;
use App\Services\DocumentComparisonService;

class ComplianceEngineService
{

    public function run(Book $book)
    {
        $book->audits()->delete();

        $this->checkRequiredDocuments($book);

        $this->checkGeneratedDocuments($book);

        $this->checkBookMetadata($book);

        $this->checkISBNField($book);

        $this->checkPageCount($book);

        $this->checkMetadataLocked($book);

        $this->checkLetterNumber($book);

        $this->checkTableOfContents($book);

        $this->checkPreface($book);

        $this->checkDocumentConsistency($book);

        $this->checkApprovals($book);

        $this->updateWorkflowStatus($book);
    }

    private function checkRequiredDocuments(
        Book $book
    ) {
        $required = [

            'naskah_final',
            'cover',
            'skk'

        ];

        foreach ($required as $type) {

            $exists =
                $book->getActiveFile(
                    $type
                );

            $book->audits()->create([

                'rule' =>
                    'required_' . $type,

                'passed' =>
                    !is_null($exists),

                'message' =>
                    $exists
                    ? 'OK'
                    : 'Berkas tidak ditemukan'

            ]);
        }
    }

    private function checkISBNField(
        Book $book
    ) {
        $book->audits()->create([

            'rule' =>
                'isbn_field',

            'passed' =>
                !empty(
                $book->isbn
            ),

            'message' =>
                !empty(
                $book->isbn
            )
                ? 'ISBN tersedia'
                : 'ISBN belum diisi'

        ]);
    }

    private function checkPageCount(
        Book $book
    ) {
        $book->audits()->create([

            'rule' =>
                'page_count',

            'passed' =>
                !empty(
                $book->jumlah_halaman
            ),

            'message' =>
                !empty(
                $book->jumlah_halaman
            )
                ? 'OK'
                : 'Jumlah halaman belum diisi'

        ]);
    }

    private function checkLetterNumber(
        Book $book
    ) {
        $book->audits()->create([

            'rule' =>
                'nomor_surat',

            'passed' =>
                !empty(
                $book->nomor_surat
            ),

            'message' =>
                !empty(
                $book->nomor_surat
            )
                ? 'OK'
                : 'Nomor surat belum dibuat'

        ]);
    }

    private function checkGeneratedDocuments(
        Book $book
    ) {
        $required = [

            'halaman_judul',

            'copyright',

            'surat_permohonan',

            'attachment'

        ];

        foreach ($required as $type) {

            $exists =
                $book->getActiveFile(
                    $type
                );

            $book->audits()->create([

                'rule' =>
                    'generated_' . $type,

                'passed' =>
                    !is_null($exists),

                'message' =>
                    $exists
                    ? 'OK'
                    : 'Belum digenerate'

            ]);
        }
    }

    private function checkBookMetadata(
        Book $book
    ) {
        $required = [

            'judul' => $book->judul,

            'penulis_1' => $book->penulis_1,

            'editor' => $book->editor,

            'layouter' => $book->layouter

        ];

        foreach (
            $required as $field => $value
        ) {

            $book->audits()->create([

                'rule' =>
                    'metadata_' . $field,

                'passed' =>
                    !empty($value),

                'message' =>
                    !empty($value)
                    ? 'OK'
                    : ucfirst($field) . ' kosong'

            ]);
        }
    }

    private function checkTableOfContents(
        Book $book
    ) {
        $toc =
            $book->sections()
                ->where(
                    'section_type',
                    'table_of_contents'
                )
                ->first();

        $book->audits()->create([

            'rule' =>
                'table_of_contents',

            'passed' =>
                !is_null($toc),

            'message' =>
                $toc
                ? 'Daftar isi ditemukan'
                : 'Daftar isi tidak ditemukan'

        ]);
    }

    private function checkPreface(
        Book $book
    ) {
        $preface =
            $book->sections()
                ->where(
                    'section_type',
                    'preface'
                )
                ->first();

        if (!$preface) {

            $book->audits()->create([

                'rule' =>
                    'kata_pengantar',

                'passed' =>
                    true,

                'message' =>
                    'Tidak ada kata pengantar'

            ]);

            return;
        }

        $forbidden = [

            'kritik dan saran',

            'masih banyak kekurangan',

            'jauh dari kesempurnaan',

            'segala kekurangan',

            'kami harapkan kritik'

        ];

        $text =
            strtolower(
                $preface->content
            );

        $found = [];

        foreach (
            $forbidden as $keyword
        ) {

            if (
                str_contains(
                    $text,
                    $keyword
                )
            ) {

                $found[] =
                    $keyword;
            }
        }

        $book->audits()->create([

            'rule' =>
                'kata_pengantar',

            'passed' =>
                empty($found),

            'message' =>
                empty($found)
                ? 'OK'
                : implode(
                    ', ',
                    $found
                )

        ]);
    }

    private function updateWorkflowStatus(
        Book $book
    ) {
        $hasFailedAudit =
            $book->audits()
                ->where('passed', false)
                ->exists();

        $newStatus =
            $hasFailedAudit
            ? 'revisi'
            : 'ready_for_isbn';

        $book->update([

            'workflow_status' =>
                $newStatus

        ]);

        if (
            $newStatus === 'ready_for_isbn'
            &&
            !$book->nomor_surat
        ) {

            $book->update([

                'nomor_surat' =>
                    app(
                        \App\Services\LetterNumberService::class
                    )->generate()

            ]);
        }
    }

    private function checkDocumentConsistency(
        Book $book
    ) {
        $results =
            $this->comparison
                ->compare($book);

        $messages = [

            'title_page_match' =>
                'Judul halaman judul tidak sesuai',

            'title_page_author_match' =>
                'Penulis halaman judul tidak sesuai',

            'title_page_subtitle_match' =>
                'Subjudul halaman judul tidak sesuai',

            'copyright_match' =>
                'Judul copyright tidak sesuai',

            'copyright_author_match' =>
                'Penulis copyright tidak sesuai',

            'request_letter_match' =>
                'Surat permohonan tidak sesuai'

        ];

        foreach (
            $results as $rule => $passed
        ) {

            $book->audits()->create([

                'rule' =>
                    $rule,

                'passed' =>
                    $passed,

                'message' =>
                    $passed
                    ? 'Sesuai'
                    : (
                        $messages[$rule]
                        ?? 'Tidak sesuai'
                    )

            ]);
        }
    }

    private function checkMetadataLocked(
        Book $book
    ) {
        $book->audits()->create([

            'rule' =>
                'metadata_locked',

            'passed' =>
                $book->metadata_locked,

            'message' =>
                $book->metadata_locked
                ? 'Metadata sudah dikunci'
                : 'Metadata belum dikunci'

        ]);
    }

    protected $comparison;

    public function __construct(
        DocumentComparisonService $comparison
    ) {
        $this->comparison = $comparison;
    }

    private function checkApprovals(
        Book $book
    ) {
        $required = [

            'editor',

            'layout',

            'author'

        ];

        foreach (
            $required as $type
        ) {

            $approved =
                $book->approvals()
                    ->where(
                        'approval_type',
                        $type
                    )
                    ->exists();

            $book->audits()->create([

                'rule' =>
                    'approval_' . $type,

                'passed' =>
                    $approved,

                'message' =>
                    $approved
                    ? 'Sudah disetujui'
                    : 'Belum disetujui'

            ]);
        }
    }

}