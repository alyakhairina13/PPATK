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
        preg_match_all('/(?:\{!!|\{\{)\s*(.+?)\s*(?:!!\}|\}\})/s', $content, $matches);

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
        return preg_replace_callback(
            '/(\{!!|\{\{)\s*(.+?)\s*(!!\}|\}\})/s',
            function (array $matches) use ($payload): string {
                $resolved = $this->resolveExpression(trim($matches[2]), $payload);

                return $resolved === null ? $matches[0] : $resolved;
            },
            $content
        ) ?? $content;
    }

    private function replaceBinaryDocTemplateExpressions(string $content, array $payload): string
    {
        return preg_replace_callback(
            '/(\{!!|\{\{)\s*(.+?)\s*(!!\}|\}\})/s',
            function (array $matches) use ($payload): string {
                $resolved = $this->resolveExpression(trim($matches[2]), $payload);

                if ($resolved === null) {
                    return $matches[0];
                }

                return $this->fitReplacementToOriginalByteLength($resolved, strlen($matches[0]));
            },
            $content
        ) ?? $content;
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
