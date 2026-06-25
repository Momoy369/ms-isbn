<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\DocumentGeneratorService;
use App\Models\BookFile;
use App\Services\IsbnAuditService;
use App\Services\WorkflowService;
use App\Services\MetadataExtractorService;
use App\Services\ManuscriptAnalyzerService;
use App\Services\ISBNPackageService;
use App\Services\BookActivityService;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function titlePage(
        Book $book,
        DocumentGeneratorService $generator,
        BookActivityService $activity
    ) {
        $result =
            $generator
                ->generateTitlePage($book);

        BookFile::create([

            'book_id' => $book->id,

            'type' => 'halaman_judul',

            'original_name' =>
                $result['file_name'],

            'file_path' =>
                $result['file_path'],

            'mime_type' =>
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

            'file_size' =>
                filesize(
                    $result['file_path']
                ),

            'is_active' =>
                true

        ]);

        $activity->log(

            $book,

            'Generate Halaman Judul',

            'Dokumen halaman judul dibuat'

        );

        return back()->with(
            'success',
            'Halaman Judul berhasil dibuat'
        );
    }

    public function download(
        BookFile $file
    ) {

        $user = auth()->user();

        if (
            $user->role === 'author'
        ) {

            $book = $file->book;

            if (
                $book->author_user_id !==
                $user->id
            ) {

                abort(403);
            }
        }

        if (
            !Storage::disk('public')
                ->exists(
                    $file->file_path
                )
        ) {

            abort(404);
        }

        $absolutePath = Storage::disk('public')
            ->path(
                $file->file_path
            );

        return response()->download(

            $absolutePath,

            $file->original_name

        );
    }

    public function requestLetter(
        Book $book,
        DocumentGeneratorService $generator,
        BookActivityService $activity
    ) {
        $result =
            $generator
                ->generateRequestLetter(
                    $book
                );

        BookFile::create([

            'book_id' =>
                $book->id,

            'type' =>
                'surat_permohonan',

            'original_name' =>
                $result['file_name'],

            'file_path' =>
                $result['file_path'],

            'mime_type' =>
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

            'file_size' =>
                filesize(
                    $result['file_path']
                ),

            'is_active' =>
                true

        ]);

        $activity->log(

            $book,

            'Generate Surat Permohonan',

            'Surat permohonan ISBN dibuat'

        );

        return back()->with(
            'success',
            'Surat Permohonan berhasil dibuat'
        );

    }

    public function copyright(
        Book $book,
        DocumentGeneratorService $generator,
        BookActivityService $activity
    ) {
        $result =
            $generator
                ->generateCopyright($book);

        BookFile::create([

            'book_id' =>
                $book->id,

            'type' =>
                'copyright',

            'original_name' =>
                $result['file_name'],

            'file_path' =>
                $result['file_path'],

            'mime_type' =>
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

            'file_size' =>
                filesize(
                    $result['file_path']
                ),

            'is_active' =>
                true

        ]);

        $activity->log(

            $book,

            'Generate Copyright',

            'Halaman copyright dibuat'

        );

        return back()->with(
            'success',
            'Copyright berhasil dibuat'
        );
    }

    public function attachment(
        Book $book,
        DocumentGeneratorService $generator,
        BookActivityService $activity
    ) {
        $result =
            $generator
                ->generateAttachment(
                    $book
                );

        BookFile::create([

            'book_id' =>
                $book->id,

            'type' =>
                'attachment_isbn',

            'original_name' =>
                $result['file_name'],

            'file_path' =>
                $result['file_path'],

            'mime_type' =>
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

            'file_size' =>
                filesize(
                    $result['file_path']
                ),

            'is_active' =>
                true

        ]);

        $activity->log(

            $book,

            'Generate Attachment ISBN',

            'Attachment ISBN dibuat'

        );

        return back()->with(
            'success',
            'Attachment ISBN berhasil dibuat'
        );
    }

    public function audit(
        Book $book,
        IsbnAuditService $audit,
        WorkflowService $workflow,
        BookActivityService $activity
    ) {
        $audit->run($book);

        $workflow->update($book, $activity);

        $activity->log(

            $book,

            'Audit ISBN',

            'Audit ISBN dijalankan'

        );

        return back()->with(
            'success',
            'Audit selesai'
        );
    }

    public function analyzeMetadata(
        Book $book,
        MetadataExtractorService $service,
        BookActivityService $activity
    ) {
        $result =
            $service->analyze($book);

        $activity->log(

            $book,

            'Analisis Metadata',

            'Metadata buku dianalisis'

        );

        return back()->with([
            'success' =>
                'Metadata berhasil dianalisis',

            'metadata_result' =>
                $result
        ]);
    }

    public function analyzeManuscript(
        Book $book,
        ManuscriptAnalyzerService $service,
        BookActivityService $activity
    ) {
        $service->analyze($book);

        $activity->log(

            $book,

            'Analisis Naskah',

            'Analisis isi naskah dijalankan'

        );

        return back()->with(
            'success',
            'Analisis naskah berhasil dijalankan'
        );
    }

    public function generateAll(
        Book $book
    ) {
        $generator =
            app(
                DocumentGeneratorService::class
            );

        $generator
            ->generateTitlePage(
                $book
            );

        $generator
            ->generateCopyright(
                $book
            );

        $generator
            ->generateRequestLetter(
                $book
            );

        $generator
            ->generateAttachment(
                $book
            );

        return back()
            ->with(
                'success',
                'Paket ISBN berhasil dibuat'
            );
    }

    public function generatePackage(
        Book $book,
        ISBNPackageService $service,
        BookActivityService $activity
    ) {
        try {

            $zipName =
                $service->generate(
                    $book
                );

            $activity->log(

                $book,

                'Generate Paket ISBN',

                'ZIP ISBN berhasil dibuat'

            );

            return response()
                ->download(

                    storage_path(
                        'app/public/' .
                        $zipName
                    )

                );

        } catch (\Exception $e) {

            return back()->with(

                'warning',

                $e->getMessage()

            );
        }
    }


}