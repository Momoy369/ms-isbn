<?php

namespace App\Services;

use App\Models\Book;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

class BookLayoutGeneratorService
{
    /**
     * Create a new class instance.
     */
    public function build(
        Book $book
    ) {

        if (
            $this->isNonFiction(
                $book
            )
        ) {

            // BAB I
            // BAB II
            // 1.1
            // 1.2

        } else {

            // BAB 1
            // Judul Novel
            // Sub bab tanpa nomor

        }

        $book->load(
            'sectionsGenerator'
        );

        $phpWord =
            new PhpWord();

        $phpWord->addTitleStyle(
            1,
            [
                'bold' => true,
                'size' => 22,
                'allCaps' => true
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 240,
                'spaceAfter' => 120
            ]
        );

        $phpWord->addTitleStyle(
            2,
            [
                'bold' => true,
                'size' => 18,
                'allCaps' => true
            ],
            [
                'alignment' => 'center',
                'spaceAfter' => 240
            ]
        );

        $phpWord->addTitleStyle(
            3,
            [
                'bold' => true,
                'italic' => true,
                'size' => 13
            ],
            [
                'alignment' => 'left',
                'spaceBefore' => 180,
                'spaceAfter' => 80
            ]
        );

        $coverSection =
            $phpWord->addSection([

                'pageSizeW' => 7937,
                'pageSizeH' => 11622,

                'marginTop' => 0,
                'marginBottom' => 0,
                'marginLeft' => 0,
                'marginRight' => 0

            ]);

        $section = $phpWord->addSection([

            'pageSizeW' => 7937,
            'pageSizeH' => 11622,

            'marginTop' => 1138,
            'marginBottom' => 1138,

            'marginLeft' => 1411,
            'marginRight' => 994,

            'gutter' => 0,

            'mirrorMargins' => true

        ]);

        $footer = $section->addFooter();

        $footer->addPreserveText(

            '{PAGE}',

            [],

            [

                'alignment' => 'center'

            ]

        );

        $this->buildCover(
            $coverSection,
            $book
        );

        $this->buildTitlePage(
            $section,
            $book
        );

        $this->buildCopyright(
            $section,
            $book
        );

        $this->buildPreface(
            $section,
            $book
        );

        $this->buildForeword(
            $section,
            $book
        );

        $this->buildBibliography(
            $section,
            $book
        );

        $this->buildAppendix(
            $section,
            $book
        );

        $this->buildTOC(
            $section
        );

        $this->buildChapters(
            $section,
            $book
        );

        $this->buildAuthorPage(
            $section,
            $book
        );

        return $phpWord;
    }

    private function buildCover(
        $section,
        Book $book
    ) {
        $cover =
            $book->files()
                ->where(
                    'type',
                    'cover'
                )
                ->where(
                    'is_active',
                    true
                )
                ->latest()
                ->first();

        if (!$cover) {
            return;
        }

        $coverPath =
            storage_path(
                'app/public/' .
                $cover->file_path
            );

        if (!file_exists($coverPath)) {
            return;
        }

        $section->addImage(

            $coverPath,

            [

                'width' => 397,

                'height' => 581,

                'positioning' => 'absolute',

                'posHorizontal' => 0,

                'posVertical' => 0

            ]

        );
    }

    private function buildTitlePage(
        $section,
        Book $book
    ) {
        $logoPath =
            storage_path(
                'app/templates/logo-ms.png'
            );

        $section->addTextBreak(1);

        $section->addText(

            $this->sanitizeText($book->judul),

            [

                'bold' => true,
                'size' => 24,
                'allCaps' => true

            ],

            [

                'alignment' => 'center'

            ]

        );

        $section->addTextBreak(2);

        $section->addText(

            $this->sanitizeText($book->subjudul ?? ''),

            [

                'size' => 16,
                'allCaps' => true

            ],

            [

                'alignment' => 'center'

            ]

        );

        $section->addTextBreak(3);

        $section->addText(

            $this->sanitizeText($book->penulis_1),

            [

                'bold' => true,
                'size' => 14,
                'allCaps' => true

            ],

            [

                'alignment' => 'center'

            ]

        );

        $section->addTextBreak(2);

        $section->addImage(

            $logoPath,

            [

                'width' => 99.84,

                'height' => 68.16,

                'alignment' => 'center'

            ]

        );

        $section->addTextBreak(1);

        $section->addText(

            'CV MITRA SENTOSA',

            [

                'bold' => true,

                'size' => 12

            ],

            [

                'alignment' => 'center'

            ]

        );

        $section->addPageBreak();
    }

    private function buildCopyright(
        $section,
        Book $book
    ) {

        $copyrightStyle = [

            'spaceAfter' => 0,

            'lineHeight' => 1.0

        ];

        $section->addText(
            $this->sanitizeText($book->judul),
            [

                'bold' => true,
                'allCaps' => true,

            ],
            $copyrightStyle
        );

        $section->addText(

            'Copyright © '
            . $this->sanitizeText($book->penulis_1 ?? '-')
            . ', '
            . ($book->tahun_copyright ?? now()->year),

            [],

            $copyrightStyle

        );

        $section->addText(

            'xxv + '
            . ($book->jumlah_halaman ?? '0')
            . ' hlm; 140 x 205 mm',
            $copyrightStyle

        );

        $section->addText(
            $book->cetakan
            ?? 'Cetakan I',
            $copyrightStyle
        );

        $section->addText(
            'ISBN: '
            . $this->sanitizeText($book->isbn ?? '-'),
            [

                'bold' => true,

            ],
            $copyrightStyle,
        );

        $section->addText(
            'Penulis:',
            [

                'bold' => true,

            ],
            $copyrightStyle,
        );

        $section->addText(
            $this->sanitizeText($book->penulis_1),
            $copyrightStyle
        );

        $section->addText(
            'Editor:',
            $copyrightStyle,
            [

                'bold' => true,

            ],
        );

        $section->addText(
            $this->sanitizeText($book->editor ?? '-'),
            $copyrightStyle
        );

        $section->addText(
            'Penata Letak:',
            [

                'bold' => true,

            ],
            $copyrightStyle,
        );

        $section->addText(
            $this->sanitizeText($book->layouter ?? '-'),
            $copyrightStyle
        );

        $section->addText(
            'Desain Sampul:',
            [

                'bold' => true,

            ],
            $copyrightStyle,
        );

        $section->addText(
            $this->sanitizeText($book->designer ?? '-'),
            $copyrightStyle
        );

        $section->addText(
            'Penerbit:',
            [

                'bold' => true,

            ],
            $copyrightStyle,
        );

        $section->addText(
            'CV MITRA SENTOSA',
            [

                'bold' => true,

            ],
            $copyrightStyle,
        );

        $section->addText(
            'Redaksi:',
            [

                'bold' => true,

            ],
            $copyrightStyle,
        );

        $section->addText(
            'Vila Mutiara Jaya Blok M 10 Cibitung Bekasi',
            $copyrightStyle

        );

        $section->addText(
            'Email: mspublishing8@gmail.com',
            $copyrightStyle
        );

        $section->addText(
            'Tlp/Fax: 021-883938',
            $copyrightStyle
        );

        $section->addTextBreak();

        $section->addText(

            'Hak cipta dilindungi undang-undang.',
            $copyrightStyle

        );

        $section->addText(

            'Dilarang memperbanyak maupun mengedarkan buku dalam bentuk dan dengan cara apa pun tanpa izin tertulis dari penerbit maupun penulis.',
            $copyrightStyle

        );

        $section->addPageBreak();
    }

    private function buildPreface(
        $section,
        Book $book
    ) {
        $preface =
            $book->sectionsGenerator
                ->where('section_type', 'preface')
                ->first();

        if (!$preface) {
            return;
        }

        $section->addTitle('KATA PENGANTAR', 1);

        $section->addTextBreak(4);

        $content = preg_replace(
            '/<p\b[^>]*>/i',
            '<p style="text-align:justify; line-height:1.4; text-indent:14pt; margin-bottom:8pt;">',
            $preface->content
        );

        // FIX: sanitize & characters before passing to PhpWord
        $this->addHtml($section, $content);

        $section->addPageBreak();
    }

    private function buildTOC(
        $section
    ) {
        $section->addTitle(
            'DAFTAR ISI',
            1
        );

        $section->addTextBreak(4);

        $section->addTOC(

            [

                'size' => 11

            ]

        );

        $section->addPageBreak();
    }

    private function buildChapters(
        $section,
        Book $book
    ) {

        if (
            $this->isPoetry(
                $book
            )
        ) {

            return $this->buildPoems(
                $section,
                $book
            );

        }

        if (
            $this->isNonFiction(
                $book
            )
        ) {

            return $this->buildNonFictionChapters(
                $section,
                $book
            );

        }

        $chapterNumber = 1;
        $subChapterNumber = 1;

        foreach ($book->sectionsGenerator->sortBy('sort_order') as $item) {

            /*
            |--------------------------------------------------------------------------
            | BAB
            |--------------------------------------------------------------------------
            */

            if ($item->section_type === 'chapter') {
                $section->addTitle('BAB ' . $chapterNumber, 1);
                $section->addTitle(strtoupper($this->sanitizeText($item->title)), 2);

                $section->addText('❦', ['size' => 16], ['alignment' => 'center']);
                $section->addTextBreak();

                $content = preg_replace(
                    '/<p\b[^>]*>/i',
                    '<p style="text-align:justify; line-height:1.4; text-indent:14pt; margin-bottom:8pt;">',
                    $item->content
                );

                // FIX: sanitize & characters before passing to PhpWord
                $this->addHtml($section, $content);
                $section->addPageBreak();

                $chapterNumber++;
                $subChapterNumber = 1;
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | SUB BAB
            |--------------------------------------------------------------------------
            */

            if ($item->section_type === 'subchapter') {
                $section->addTitle(
                    ($chapterNumber - 1) . '.' . $subChapterNumber . ' ' . $this->sanitizeText($item->title),
                    3
                );

                $content = preg_replace(
                    '/<p\b[^>]*>/i',
                    '<p style="text-align:justify; line-height:1.4; text-indent:14pt; margin-bottom:8pt;">',
                    $item->content
                );

                // FIX: sanitize & characters before passing to PhpWord
                $this->addHtml($section, $content);
                $subChapterNumber++;
                continue;
            }
        }
    }

    private function buildAuthorPage(
        $section,
        Book $book
    ) {
        $author =
            $book->sectionsGenerator
                ->where('section_type', 'author')
                ->first();

        if (!$author) {
            return;
        }

        $section->addTitle('TENTANG PENULIS', 1);

        $content = preg_replace(
            '/<p\b[^>]*>/i',
            '<p style="text-align:justify; line-height:1.4; text-indent:14pt; margin-bottom:8pt;">',
            $author->content
        );

        // FIX: sanitize & characters before passing to PhpWord
        $this->addHtml($section, $content);

        $section->addPageBreak();
    }

    private function buildBibliography(
        $section,
        Book $book
    ) {
        $data =
            $book->sectionsGenerator
                ->where('section_type', 'bibliography')
                ->first();

        if (!$data) {
            return;
        }

        $section->addTitle('DAFTAR PUSTAKA', 1);

        $content = preg_replace(
            '/<p\b[^>]*>/i',
            '<p style="text-align:justify; line-height:1.4; margin-left:14pt; text-indent:-14pt; margin-bottom:8pt;">',
            $data->content
        );

        // FIX: sanitize & characters before passing to PhpWord
        $this->addHtml($section, $content);
    }

    private function isNonFiction(
        Book $book
    ): bool {

        return
            $book->book_type
            ===
            'nonfiction';

    }

    private function toRoman(
        int $number
    ): string {

        $map = [

            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1

        ];

        $result = '';

        foreach (
            $map as $roman => $value
        ) {

            while (
                $number >= $value
            ) {

                $result .= $roman;

                $number -= $value;

            }
        }

        return $result;
    }

    private function buildNonFictionChapters(
        $section,
        Book $book
    ) {
        $chapterNumber = 1;

        $subNumber = 1;

        foreach (
            $book->sectionsGenerator
                ->sortBy('sort_order')
            as $item
        ) {

            if (
                $item->section_type
                ===
                'chapter'
            ) {

                $roman =
                    $this->toRoman(
                        $chapterNumber
                    );

                $section->addTitle(

                    'BAB ' .
                    $roman,

                    1

                );

                $section->addTitle(

                    strtoupper(
                        $this->sanitizeText($item->title)
                    ),

                    2

                );

                $chapterNumber++;

                $subNumber = 1;

                continue;
            }

            if (
                $item->section_type
                ===
                'subchapter'
            ) {

                $section->addTitle(

                    ($chapterNumber - 1)
                    . '.'
                    . $subNumber
                    . ' '
                    . $this->sanitizeText($item->title),

                    3

                );

                $content =
                    str_replace(

                        '<p>',

                        '<p style="
                        text-align:justify;
                        margin-bottom:8px;
                    ">',

                        $item->content

                    );

                // FIX: sanitize & characters before passing to PhpWord
                $this->addHtml($section, $content);

                $subNumber++;

                continue;
            }
        }
    }

    private function buildForeword(
        $section,
        Book $book
    ) {
        $item =
            $book->sectionsGenerator
                ->where(
                    'section_type',
                    'foreword'
                )
                ->first();

        if (!$item) {
            return;
        }

        $section->addTitle(
            'PRAKATA',
            1
        );

        // FIX: sanitize & characters before passing to PhpWord
        $this->addHtml($section, $item->content);

        $section->addPageBreak();
    }

    private function buildAppendix(
        $section,
        Book $book
    ) {
        $item =
            $book->sectionsGenerator
                ->where(
                    'section_type',
                    'appendix'
                )
                ->first();

        if (!$item) {
            return;
        }

        $section->addTitle(
            'LAMPIRAN',
            1
        );

        // FIX: sanitize & characters before passing to PhpWord
        $this->addHtml($section, $item->content);

        $section->addPageBreak();
    }

    private function isPoetry(
        Book $book
    ): bool {

        return
            $book->book_type
            ===
            'poetry';

    }

    private function buildPoems(
        $section,
        Book $book
    ) {
        $firstPoem = true;

        foreach (
            $book->sectionsGenerator
                ->sortBy('sort_order')
            as $item
        ) {

            if (
                !in_array(
                    $item->section_type,
                    [
                        'poem',
                        'chapter'
                    ]
                )
            ) {
                continue;
            }

            if (!$firstPoem) {
                $section->addPageBreak();
            }

            $section->addTitle(
                $this->sanitizeText($item->title),
                1
            );

            $section->addTextBreak(2);

            $content = preg_replace(
                '/<p\b[^>]*>/i',
                '<p style="text-align:justify; line-height:1.4; text-indent:14pt; margin-bottom:8pt;">',
                $item->content
            );

            // FIX: sanitize & characters before passing to PhpWord
            $this->addHtml($section, $content);

            $firstPoem = false;
        }
    }

    private function cleanHtml(
        string $html
    ): string {

        $html = preg_replace(
            '/<span[^>]*>/i',
            '',
            $html
        );

        $html = preg_replace(
            '/<\/span>/i',
            '',
            $html
        );

        return $html;
    }

    /**
     * Sanitize HTML content before passing to PhpWord's Html::addHtml().
     *
     * A .docx file is a ZIP of XML files. Any character that is illegal
     * in XML 1.0 — most notably a bare '&' not belonging to an entity
     * reference, but also C0 control characters — silently corrupts the
     * document.  A simple regex is fragile because '&' can also appear
     * inside attribute values or get introduced by the editor's rich-text
     * output in forms we can't predict.
     *
     * The most reliable fix is a DOMDocument round-trip:
     *   1. loadHTML()  — lenient HTML parser; accepts broken markup and
     *                    bare '&' signs without throwing errors.
     *   2. saveHTML()  — serialises back to well-formed HTML, escaping
     *                    every bare '&' as '&amp;' automatically.
     *
     * Additionally we strip XML 1.0 illegal control characters before
     * parsing (U+0000–U+0008, U+000B, U+000C, U+000E–U+001F, U+007F)
     * because DOMDocument cannot recover from them at all.
     */
    private function sanitizeHtml(
        string $html
    ): string {

        if (empty(trim($html))) {
            return $html;
        }

        // Strip XML 1.0 illegal control characters first.
        $html = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $html
        );

        // Escape bare ampersands before the DOM parser sees them.
        $html = preg_replace(
            '/&(?!(?:[a-zA-Z]+|#[0-9]+|#x[0-9a-fA-F]+);)/u',
            '&amp;',
            $html
        );

        $html = preg_replace('/<(\/)?(script|style|link|meta|title|head|body|html)\b[^>]*>/i', '', $html);

        libxml_use_internal_errors(true);
        libxml_disable_entity_loader(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $dom->loadHTML(
            '<?xml encoding="UTF-8"?>'
            . '<html>'
            . '<head>'
            . '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'
            . '</head>'
            . '<body>' . $html . '</body>'
            . '</html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);

        if (!$body) {
            return $html;
        }

        $output = '';
        foreach ($body->childNodes as $node) {
            $output .= $dom->saveHTML($node);
        }

        return $output;
    }

    /**
     * Central wrapper around Html::addHtml() that always sanitizes
     * content before rendering, preventing XML corruption in .docx files.
     */
    private function addHtml(
        $section,
        string $html
    ): void {

        $plainText = $this->convertHtmlToPlainText($html);

        if (trim($plainText) !== '') {
            $this->addPlainTextFallback($section, $plainText);
            return;
        }

        $safeHtml = $this->sanitizeHtml($html);

        if (trim($safeHtml) === '') {
            return;
        }

        try {
            Html::addHtml($section, $safeHtml);
        } catch (\Throwable $e) {
            $this->addPlainTextFallback($section, $safeHtml);
        }
    }

    /**
     * Convert HTML content to plain text while preserving paragraph boundaries.
     * This keeps the content visible in Word even when the HTML is malformed,
     * contains unsupported nodes, or includes risky characters such as '&'.
     */
    private function convertHtmlToPlainText(
        string $html
    ): string {
        $text = preg_replace('/<!--.*?-->/s', ' ', $html);
        $text = preg_replace('/<(br|hr)\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<\/(p|div|section|article|li|ul|ol|h[1-6]|tr|table|blockquote)>/i', "\n", $text);
        $text = preg_replace('/<[^>]+>/u', ' ', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/ *\n */u', "\n", $text);
        $text = preg_replace('/\n{3,}/u', "\n\n", $text);

        return trim($text);
    }

    /**
     * Fallback renderer for malformed or unsupported HTML fragments.
     * This avoids crashing Word generation when PhpWord cannot safely parse
     * specific markup such as broken tags, unsupported nodes, or edge cases.
     */
    private function addPlainTextFallback(
        $section,
        string $html
    ): void {
        $text = $html;
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/\n{3,}/u', "\n\n", $text);

        $paragraphs = preg_split('/\n\s*\n/u', trim($text));

        foreach ($paragraphs as $paragraph) {
            $clean = trim($paragraph);
            if ($clean !== '') {
                $section->addText($this->sanitizeText($clean));
                $section->addTextBreak(1);
            }
        }
    }

    /**
     * Sanitize a plain-text string for addText() / addTitle().
     * PhpWord's XMLWriter already escapes '&' → '&amp;' automatically,
     * but XML 1.0 control characters bypass that and still corrupt the file.
     */
    private function sanitizeText(
        ?string $text
    ): string {

        if ($text === null || $text === '') {
            return '';
        }

        $text = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $text
        );

        return str_replace('&', '&amp;', $text);
    }
}