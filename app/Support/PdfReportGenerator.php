<?php

namespace App\Support;

/**
 * Minimal, dependency-free PDF report generator.
 *
 * Produces a valid multi-page PDF (Helvetica) containing a title, a set of
 * metadata lines, and a sequence of blocks (key/value lists or tables).
 * It is intentionally simple: no images, no styling beyond bold headings,
 * but it paginates automatically and is enough for a printable report.
 */
class PdfReportGenerator
{
    private const PAGE_W = 595.28;
    private const PAGE_H = 841.89;
    private const MARGIN = 40.0;
    private const LINE_H = 14.0;

    private string $title = 'Laporan';
    private array $meta = [];
    private array $blocks = [];

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function meta(array $lines): self
    {
        $this->meta = array_values($lines);

        return $this;
    }

    /**
     * @param  array<int, array{0:string, 1:mixed}>  $pairs
     */
    public function keyValues(string $heading, array $pairs): self
    {
        $rows = [];

        foreach ($pairs as $pair) {
            $rows[] = [(string) ($pair[0] ?? ''), (string) ($pair[1] ?? '')];
        }

        $this->blocks[] = [
            'heading' => $heading,
            'type' => 'kv',
            'headers' => ['Keterangan', 'Nilai'],
            'rows' => $rows,
        ];

        return $this;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function table(string $heading, array $headers, array $rows): self
    {
        $this->blocks[] = [
            'heading' => $heading,
            'type' => 'table',
            'headers' => array_values($headers),
            'rows' => array_map(fn ($r) => array_map(fn ($c) => (string) $c, array_values($r)), $rows),
        ];

        return $this;
    }

    public function render(): string
    {
        $pages = $this->buildPages();

        return $this->assemble($pages);
    }

    private function buildPages(): array
    {
        $pages = [];
        $y = self::PAGE_H - self::MARGIN;
        $stream = '';

        $ensureSpace = function (float $need) use (&$stream, &$y, &$pages): void {
            if ($y - $need < self::MARGIN) {
                $pages[] = $stream;
                $stream = '';
                $y = self::PAGE_H - self::MARGIN;
            }
        };

        $stream .= $this->textLine($this->title, self::MARGIN, $y, 16, true);
        $y -= 20;

        foreach ($this->meta as $line) {
            $ensureSpace(self::LINE_H);
            $stream .= $this->textLine($line, self::MARGIN, $y, 8, false);
            $y -= self::LINE_H - 4;
        }

        $y -= 8;

        foreach ($this->blocks as $block) {
            $ensureSpace(self::LINE_H * 2);
            $stream .= $this->textLine($block['heading'], self::MARGIN, $y, 11, true);
            $y -= self::LINE_H;

            $headers = $block['headers'];
            $rows = $block['rows'];
            $colCount = count($headers);
            $usable = self::PAGE_W - self::MARGIN * 2;
            $colW = $usable / max($colCount, 1);
            $xs = [];

            for ($i = 0; $i < $colCount; $i++) {
                $xs[$i] = self::MARGIN + $colW * $i;
            }

            $ensureSpace(self::LINE_H);
            $stream .= $this->rowLine($xs, $colW, $headers, $y, 9, true);
            $y -= self::LINE_H;

            foreach ($rows as $row) {
                $ensureSpace(self::LINE_H);
                $cells = [];

                for ($i = 0; $i < $colCount; $i++) {
                    $cells[$i] = $row[$i] ?? '';
                }

                $stream .= $this->rowLine($xs, $colW, $cells, $y, 9, false);
                $y -= self::LINE_H;
            }

            $y -= 8;
        }

        $pages[] = $stream;

        return $pages;
    }

    private function textLine(string $text, float $x, float $y, float $size, bool $bold): string
    {
        $font = $bold ? 'F2' : 'F1';

        return sprintf(
            "BT /%s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n",
            $font,
            $size,
            $x,
            $y,
            $this->escape($text)
        );
    }

    /**
     * @param  array<int, float>  $xs
     * @param  array<int, string>  $cells
     */
    private function rowLine(array $xs, float $colW, array $cells, float $y, float $size, bool $bold): string
    {
        $font = $bold ? 'F2' : 'F1';
        $maxChars = max(3, (int) floor($colW / ($size * 0.52)));
        $parts = ["BT /{$font} {$size} Tf"];

        foreach ($cells as $i => $cell) {
            $parts[] = sprintf('1 0 0 1 %.2F %.2F Tm (%s) Tj', $xs[$i] ?? self::MARGIN, $y, $this->escape($this->truncate($cell, $maxChars)));
        }

        $parts[] = "ET";

        return implode(' ', $parts)."\n";
    }

    private function truncate(string $text, int $maxChars): string
    {
        if ($maxChars > 0 && strlen($text) > $maxChars) {
            return substr($text, 0, max(0, $maxChars - 1)).'-';
        }

        return $text;
    }

    private function escape(string $text): string
    {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        if ($converted === false || $converted === '') {
            $converted = preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';
        }

        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $converted);
    }

    /**
     * @param  array<int, string>  $pageStreams
     */
    private function assemble(array $pageStreams): string
    {
        $pageCount = count($pageStreams);

        $catalogN = 1;
        $pagesN = 2;
        $next = 3;

        $pageObjNums = [];
        $contentObjNums = [];

        for ($i = 0; $i < $pageCount; $i++) {
            $pageObjNums[$i] = $next++;
            $contentObjNums[$i] = $next++;
        }

        $fontRegN = $next++;
        $fontBoldN = $next++;
        $totalObjects = $next - 1;

        $kids = implode(' ', array_map(fn ($num) => "{$num} 0 R", $pageObjNums));
        $fontRes = "F1 {$fontRegN} 0 R /F2 {$fontBoldN} 0 R";

        $bodies = [];
        $bodies[$catalogN] = "<</Type/Catalog/Pages {$pagesN} 0 R>>";
        $bodies[$pagesN] = "<</Type/Pages/Kids[{$kids}]/Count {$pageCount}>>";

        foreach ($pageStreams as $i => $stream) {
            $len = strlen($stream);
            $bodies[$pageObjNums[$i]] = sprintf(
                '<</Type/Page/Parent %d 0 R/MediaBox[0 0 %.2F %.2F]/Resources<</Font<<%s>>>>/Contents %d 0 R>>',
                $pagesN,
                self::PAGE_W,
                self::PAGE_H,
                $fontRes,
                $contentObjNums[$i]
            );
            $bodies[$contentObjNums[$i]] = "<</Length {$len}>>\nstream\n{$stream}\nendstream";
        }

        $bodies[$fontRegN] = '<</Type/Font/Subtype/Type1/BaseFont/Helvetica/Encoding/WinAnsiEncoding>>';
        $bodies[$fontBoldN] = '<</Type/Font/Subtype/Type1/BaseFont/Helvetica-Bold/Encoding/WinAnsiEncoding>>';

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        for ($num = 1; $num <= $totalObjects; $num++) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "{$num} 0 obj\n".$bodies[$num]."\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 ".($totalObjects + 1)."\n";
        $pdf .= sprintf("%010d %05d f \n", 0, 65535);

        for ($num = 1; $num <= $totalObjects; $num++) {
            $pdf .= sprintf("%010d %05d n \n", $offsets[$num], 0);
        }

        $pdf .= "trailer\n<</Size ".($totalObjects + 1)."/Root {$catalogN} 0 R>>\nstartxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }
}
