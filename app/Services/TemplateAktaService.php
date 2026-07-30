<?php

namespace App\Services;

use App\Models\TemplateAkta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class TemplateAktaService
{
    private const OLE_DOC_MAGIC = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";

    /**
     * Detect the group prefix of a placeholder tag dynamically and strictly.
     *
     * The prefix is the leading token of the tag name up to (but excluding)
     * the first underscore, kept verbatim including any trailing digits.
     * This means `dpihak1_nama` and `dpihak2_nama` resolve to the distinct
     * prefixes `dpihak1` and `dpihak2` (separate groups "Pihak 1" / "Pihak 2")
     * rather than being merged. Tags without an underscore (or whose token is
     * empty) have no detectable prefix.
     */
    public static function detectPrefix(string $tag): ?string
    {
        $pos = strpos($tag, '_');

        if ($pos === false || $pos === 0) {
            return null;
        }

        $token = substr($tag, 0, $pos);

        return $token !== '' ? $token : null;
    }

    /**
     * All configured prefix (group) aliases, as managed from the template
     * management screen.
     *
     * @return array<string, string>
     */
    public static function prefixGroupLabels(): array
    {
        return app(TagAliasService::class)->prefixAliases();
    }

    /**
     * All configured tag (field) aliases.
     *
     * @return array<string, string>
     */
    public static function tagLabels(): array
    {
        return app(TagAliasService::class)->tagAliases();
    }

    /**
     * Resolve the group prefix for a given placeholder tag name.
     * Tags without a detectable prefix fall back to the `lainnya` bucket.
     */
    public static function groupPrefixForTag(string $tag): string
    {
        return self::detectPrefix($tag) ?? 'lainnya';
    }

    public static function groupLabelForPrefix(string $prefix): string
    {
        $aliases = app(TagAliasService::class);

        return $aliases->prefixLabel($prefix) ?? $aliases->fallbackPrefixLabel($prefix);
    }

    /**
     * Group an ordered list of tag names by their detected prefix, preserving
     * the order in which each group first appears in the tag list as well as
     * the original order of tags within a group.
     *
     * @param  array<int, string>  $tags
     * @return array<string, array<int, string>>
     */
    public static function groupTagsByPrefix(array $tags): array
    {
        $ordered = [];

        foreach ($tags as $tag) {
            $prefix = self::groupPrefixForTag($tag);
            $ordered[$prefix][] = $tag;
        }

        return $ordered;
    }

    /**
     * Resolve the human readable label for a single placeholder tag.
     * Prefers an explicit alias; otherwise derives a CamelCase label from
     * the part of the name that follows the group prefix.
     */
    public static function labelForTag(string $tag): string
    {
        $explicit = app(TagAliasService::class)->tagLabel($tag);

        if ($explicit !== null) {
            return $explicit;
        }

        $prefix = self::groupPrefixForTag($tag);
        $remainder = $prefix !== 'lainnya'
            ? ltrim(substr($tag, strlen($prefix)), '_')
            : $tag;

        $label = self::camelToLabel($remainder);

        return $label !== '' ? $label : self::camelToLabel($tag);
    }

    /**
     * Convert a snake_case / camelCase identifier into a human readable,
     * CamelCase-derived label (e.g. `work_area` -> "Work Area",
     * `tanggalLahir` -> "Tanggal Lahir", `nama` -> "Nama").
     */
    private static function camelToLabel(string $value): string
    {
        $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value) ?? $value;
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $value) ?? $value;
        $value = preg_replace('/([A-Z])([A-Z][a-z])/', '$1 $2', $value) ?? $value;

        $parts = preg_split('/\s+/', trim($value)) ?: [];
        $parts = array_map('ucfirst', $parts);

        return trim(implode(' ', $parts));
    }

    public function __construct(private readonly PpatConfigurationService $ppatConfigurationService)
    {
    }

    /**
     * @return array<int, string>
     */
    public function extractTagsFromUploadedFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $content = match ($extension) {
            'docx' => $this->readDocxContents($file->getRealPath()),
            'doc' => file_get_contents($file->getRealPath()) ?: '',
            default => throw new RuntimeException('Format template tidak didukung.'),
        };

        return $this->extractTagsFromString($content);
    }

    public function storeUploadedTemplate(UploadedFile $file, string $slug): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = sprintf('akta-%s.%s', $slug, $extension);

        return $file->storeAs('templates', $filename, 'local');
    }

    public function renderMergedDocument(TemplateAkta $template, array $payload, int $aktaId): string
    {
        $payload = array_merge($payload, $this->ppatConfigurationService->templateDefaults());
        $sourcePath = Storage::disk('local')->path($template->file_path);
        $extension = strtolower($template->file_extension);
        $targetDirectory = Storage::disk('local')->path('tmp');

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        $targetPath = $targetDirectory.DIRECTORY_SEPARATOR.sprintf(
            'akta-%d-%s-%s.%s',
            $aktaId,
            $template->slug,
            uniqid(),
            $extension
        );

        match ($extension) {
            'docx' => $this->renderDocx($sourcePath, $targetPath, $payload),
            'doc' => $this->renderDoc($sourcePath, $targetPath, $payload),
            default => throw new RuntimeException('Format template tidak didukung untuk download.'),
        };

        return $targetPath;
    }

    public function contentTypeForExtension(string $extension): string
    {
        return match (strtolower($extension)) {
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            default => 'application/octet-stream',
        };
    }

    /**
     * @return array<int, string>
     */
    private function extractTagsFromString(string $content): array
    {
        $plainText = strip_tags($content);

        preg_match_all('/(?:\{!!|\{\{)\s*(.+?)\s*(?:!!\}|\}\})/s', $plainText, $matches);

        $tags = [];

        foreach ($matches[1] as $expression) {
            preg_match_all('/\$([A-Za-z_][A-Za-z0-9_]*)/', $expression, $variableMatches);

            foreach ($variableMatches[1] as $variable) {
                $tags[$variable] = $variable;
            }
        }

        ksort($tags);

        return array_values($tags);
    }

    private function readDocxContents(string $path): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZipArchive tidak tersedia untuk membaca file DOCX.');
        }

        $zip = new ZipArchive();
        $result = $zip->open($path);

        if ($result !== true) {
            throw new RuntimeException('File DOCX tidak dapat dibaca.');
        }

        $buffers = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->statIndex($i);
            $name = $entry['name'] ?? null;

            if (! $name || ! str_starts_with($name, 'word/') || ! str_ends_with($name, '.xml')) {
                continue;
            }

            $buffers[] = $zip->getFromIndex($i) ?: '';
        }

        $zip->close();

        return html_entity_decode(implode("\n", $buffers), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function renderDoc(string $sourcePath, string $targetPath, array $payload): void
    {
        $content = file_get_contents($sourcePath);

        if ($content === false) {
            throw new RuntimeException('Template DOC tidak dapat dibaca.');
        }

        $rendered = $this->isBinaryDoc($content)
            ? $this->replaceBinaryDocTemplateExpressions($content, $payload)
            : $this->replaceTemplateExpressions($content, $payload);

        file_put_contents($targetPath, $rendered);
    }

    private function renderDocx(string $sourcePath, string $targetPath, array $payload): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZipArchive tidak tersedia untuk menghasilkan file DOCX.');
        }

        if (! copy($sourcePath, $targetPath)) {
            throw new RuntimeException('Template DOCX tidak dapat disalin.');
        }

        $zip = new ZipArchive();
        $result = $zip->open($targetPath);

        if ($result !== true) {
            throw new RuntimeException('Template DOCX tidak dapat dibuka untuk diproses.');
        }

        $entries = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->statIndex($i);
            $name = $entry['name'] ?? null;

            if (! $name || ! str_starts_with($name, 'word/') || ! str_ends_with($name, '.xml')) {
                continue;
            }

            $entries[] = $name;
        }

        foreach ($entries as $name) {
            $xml = $zip->getFromName($name);

            if ($xml === false) {
                continue;
            }

            $zip->deleteName($name);
            $zip->addFromString($name, $this->replaceTemplateExpressions($xml, $payload));
        }

        $zip->close();
    }

    private function replaceTemplateExpressions(string $content, array $payload): string
    {
        return $this->replaceExpressions($content, $payload, padToOriginalLength: false);
    }

    private function replaceBinaryDocTemplateExpressions(string $content, array $payload): string
    {
        return $this->replaceExpressions($content, $payload, padToOriginalLength: true);
    }

    /**
     * Replace Blade-style placeholder expressions ({{ $var }}, {!! $var !!})
     * with values from the payload, even when Word has scattered the
     * expression across multiple XML text runs.
     *
     * Uses a character scanner instead of a single regex so that XML tags
     * between the opener ({{) and closer (}}) are transparently skipped
     * without the risk of one placeholder's opener consuming another
     * placeholder's closer (which a lazy .*? regex would do).
     */
    private function replaceExpressions(string $content, array $payload, bool $padToOriginalLength): string
    {
        $result = '';
        $len = strlen($content);
        $i = 0;

        while ($i < $len) {
            $opener = $this->matchOpener($content, $i);

            if ($opener === null) {
                $result .= $content[$i];
                $i++;
                continue;
            }

            [$openTag, $closeTag, $openLen] = $opener;
            $scan = $this->scanToCloser($content, $i + $openLen, $closeTag);

            if ($scan === null) {
                $result .= $content[$i];
                $i++;
                continue;
            }

            [$text, $endPos] = $scan;
            $expression = trim(strip_tags($text));
            $resolved = $this->resolveExpression($expression, $payload);

            if ($resolved === null) {
                $result .= substr($content, $i, $endPos - $i);
            } elseif ($padToOriginalLength) {
                $result .= $this->fitReplacementToOriginalByteLength($resolved, $endPos - $i);
            } else {
                $result .= $resolved;
            }

            $i = $endPos;
        }

        return $result;
    }

    /**
     * @return array{string,string,int}|null  [openMarker, closeMarker, openLength]
     */
    private function matchOpener(string $content, int $pos): ?array
    {
        if ($content[$pos] !== '{') {
            return null;
        }

        if (substr($content, $pos, 3) === '{!!') {
            return ['{!!', '!!}', 3];
        }

        if (substr($content, $pos, 2) === '{{') {
            return ['{{', '}}', 2];
        }

        return null;
    }

    /**
     * Scan from $start, skipping XML tags, until $closeTag is found.
     * If another opener ({{ or {!!) is encountered first, return null
     * (the original opener was unmatched).
     *
     * @return array{string,int}|null  [textContent, positionAfterCloseTag]
     */
    private function scanToCloser(string $content, int $start, string $closeTag): ?array
    {
        $len = strlen($content);
        $closeLen = strlen($closeTag);
        $text = '';
        $j = $start;

        while ($j < $len) {
            if ($content[$j] === '<') {
                $tagEnd = strpos($content, '>', $j);
                if ($tagEnd === false) {
                    return null;
                }
                $j = $tagEnd + 1;
                continue;
            }

            if ($content[$j] === $closeTag[0]) {
                $afterCloser = $this->matchCloserSkippingTags($content, $j, $closeTag);

                if ($afterCloser !== null) {
                    return [$text, $afterCloser];
                }
            }

            if ($content[$j] === '{' && $this->matchOpener($content, $j) !== null) {
                return null;
            }

            $text .= $content[$j];
            $j++;
        }

        return null;
    }

    /**
     * Check whether $closeTag appears at $pos, tolerating XML tags
     * between its individual characters (Word may split `}}` across
     * separate `<w:t>` runs just as it splits `{{`).
     *
     * @return int|null  Position immediately after the closer, or null.
     */
    private function matchCloserSkippingTags(string $content, int $pos, string $closeTag): ?int
    {
        $len = strlen($content);
        $closeLen = strlen($closeTag);
        $j = $pos;

        for ($ci = 0; $ci < $closeLen; $ci++) {
            while ($j < $len && $content[$j] === '<') {
                $tagEnd = strpos($content, '>', $j);

                if ($tagEnd === false) {
                    return null;
                }

                $j = $tagEnd + 1;
            }

            if ($j >= $len || $content[$j] !== $closeTag[$ci]) {
                return null;
            }

            $j++;
        }

        return $j;
    }

    private function fitReplacementToOriginalByteLength(string $replacement, int $targetLength): string
    {
        $sanitized = str_replace(["\r", "\n"], ' ', $replacement);
        $sanitized = mb_strcut($sanitized, 0, $targetLength, 'UTF-8');

        return str_pad($sanitized, $targetLength, ' ');
    }

    private function isBinaryDoc(string $content): bool
    {
        return str_starts_with($content, self::OLE_DOC_MAGIC);
    }

    private function resolveExpression(string $expression, array $payload): ?string
    {
        if (preg_match('/^\$([A-Za-z_][A-Za-z0-9_]*)$/', $expression, $matches)) {
            return (string) ($payload[$matches[1]] ?? '');
        }

        if (preg_match('/^(mb_strtoupper|strtoupper|mb_strtolower|strtolower|ucwords|ucfirst|trim)\(\s*\$([A-Za-z_][A-Za-z0-9_]*)\s*\)$/', $expression, $matches)) {
            $function = $matches[1];
            $value = (string) ($payload[$matches[2]] ?? '');

            return match ($function) {
                'mb_strtoupper' => mb_strtoupper($value),
                'strtoupper' => strtoupper($value),
                'mb_strtolower' => mb_strtolower($value),
                'strtolower' => strtolower($value),
                'ucwords' => ucwords($value),
                'ucfirst' => ucfirst($value),
                'trim' => trim($value),
                default => $value,
            };
        }

        if (preg_match('/^\$([A-Za-z_][A-Za-z0-9_]*)\s*\?\?\s*[\'"]([^\'"]*)[\'"]$/', $expression, $matches)) {
            $value = $payload[$matches[1]] ?? null;

            return $value === null || $value === '' ? $matches[2] : (string) $value;
        }

        return null;
    }
}
