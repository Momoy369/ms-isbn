<?php

namespace App\Services;

use App\Models\Book;
use ZipArchive;

class ISBNPackageService
{
    public function generate(
        Book $book
    ) {
        if (
            $book->workflow_status
            !== 'ready_for_isbn'
        ) {

            throw new \Exception(
                'Buku belum siap ISBN'
            );
        }
        $zipName =
            'isbn-package-' .
            $book->nomor_naskah .
            '.zip';

        $zipPath =
            storage_path(
                'app/public/' .
                $zipName
            );

        $zip =
            new ZipArchive();

        $zip->open(
            $zipPath,
            ZipArchive::CREATE
            |
            ZipArchive::OVERWRITE
        );

        $requiredFiles = [

            'cover',

            'naskah_final',

            'halaman_judul',

            'copyright',

            'surat_permohonan',

            'attachment_isbn'

        ];

        $missing = [];

        foreach (
            $requiredFiles as $type
        ) {

            $file =
                $book->getActiveFile(
                    $type
                );

            if (!$file) {

                $missing[] =
                    $type;

                continue;
            }

            $realPath =
                $file->getAbsolutePath();

            if (
                !file_exists(
                    $realPath
                )
            ) {
                continue;
            }

            $folder =
                match ($type) {

                    'cover'
                    => 'COVER',

                    'naskah_final'
                    => 'NASKAH',

                    default
                    => 'DOKUMEN_ISBN'
                };

            $zip->addFile(

                $realPath,

                $folder . '/' .
                $file->original_name

            );
        }

        if (
            count($missing)
        ) {
            $labels = [

                'cover' =>
                    'Cover',

                'naskah_final' =>
                    'Naskah Final',

                'halaman_judul' =>
                    'Halaman Judul',

                'copyright' =>
                    'Copyright',

                'surat_permohonan' =>
                    'Surat Permohonan',

                'attachment_isbn' =>
                    'Attachment ISBN'

            ];

            $missingText = [];

            foreach (
                $missing as $item
            ) {

                $missingText[] =
                    $labels[$item]
                    ?? $item;
            }

            throw new \Exception(

                'Paket ISBN belum dapat dibuat. Dokumen yang masih kurang: ' .

                implode(
                    ', ',
                    $missingText
                )

            );
        }

        $manifest =

            "Nomor Naskah : {$book->nomor_naskah}\n" .
            "Judul : {$book->judul}\n" .
            "Penulis : {$book->penulis_1}\n" .
            "Nomor Surat : {$book->nomor_surat}\n" .
            "Tanggal Generate : " .
            now();

        $zip->addFromString(

            'README.txt',

            $manifest

        );

        $zip->close();

        return $zipName;
    }
}