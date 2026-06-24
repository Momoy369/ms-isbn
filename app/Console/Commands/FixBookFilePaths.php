<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\BookFile;

#[Signature('app:fix-book-file-paths')]
#[Description('Command description')]
class FixBookFilePaths extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        BookFile::all()
            ->each(function ($file) {

                if (
                    str_contains(
                        $file->file_path,
                        'storage\\app\\generated\\'
                    )
                ) {

                    preg_match(

                        '/generated\\\\(.+)/',

                        $file->file_path,

                        $matches

                    );

                    if (
                        isset(
                        $matches[1]
                    )
                    ) {

                        $file->update([

                            'file_path' =>
                                'generated/' .
                                str_replace(
                                    '\\',
                                    '/',
                                    $matches[1]
                                )

                        ]);
                    }
                }
            });
    }
}
