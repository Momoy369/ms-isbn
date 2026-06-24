<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;

class DocxMetadataReaderService
{
    public function extractText(
        string $filePath
    ): string {
        $phpWord =
            IOFactory::load(
                $filePath
            );

        $text = '';

        foreach (
            $phpWord->getSections()
            as $section
        ) {

            foreach (
                $section->getElements()
                as $element
            ) {

                if (
                    method_exists(
                        $element,
                        'getText'
                    )
                ) {

                    $text .=
                        $element->getText();

                    $text .= "\n";
                }
            }
        }

        return $text;
    }

    public function extractLines(
        string $filePath
    ): array {
        $text =
            $this->extractText(
                $filePath
            );

        $lines =
            explode(
                "\n",
                $text
            );

        $lines =
            array_map(
                'trim',
                $lines
            );

        $lines =
            array_filter(
                $lines
            );

        return array_values(
            $lines
        );
    }

    public function extractMetadata(
        string $filePath
    ): array {
        $lines =
            $this->extractLines(
                $filePath
            );

        $title = null;
        $subtitle = null;
        $author = null;

        foreach ($lines as $index => $line) {

            $line = trim($line);

            if (!$title) {

                if (
                    mb_strlen($line) > 3
                ) {

                    $title = $line;

                    continue;
                }
            }

            if (
                !$subtitle
                &&
                $title
                &&
                mb_strlen($line) > 5
                &&
                $line !== $title
            ) {

                if (
                    !preg_match(
                        '/^[A-Za-z\s\.\'\-]+$/u',
                        $line
                    )
                ) {

                    $subtitle = $line;

                    continue;
                }
            }

            if (
                !$author
            ) {

                if (
                    preg_match(
                        '/^[A-Za-zÀ-ÿ\s\.\'\-]+$/u',
                        $line
                    )
                ) {

                    $author = $line;

                    break;
                }
            }
        }

        return [

            'title' =>
                $title,

            'subtitle' =>
                $subtitle,

            'author' =>
                $author

        ];
    }
}