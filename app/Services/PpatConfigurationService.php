<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class PpatConfigurationService
{
    /**
     * Placeholder prefix whose group is automatically populated from this
     * configuration on the akta forms (e.g. `{{$dppat_name}}`).
     */
    public const AUTOFILL_PREFIX = 'dppat';

    /**
     * Whether a placeholder tag belongs to the auto-filled PPAT group.
     */
    public function isAutofillTag(string $tag): bool
    {
        return TemplateAktaService::groupPrefixForTag($tag) === self::AUTOFILL_PREFIX;
    }

    /**
     * @return array<string, string>
     */
    public function getConfiguration(): array
    {
        $path = $this->getFilePath();

        if (! File::exists($path)) {
            $this->writeConfiguration($this->defaults());

            return $this->defaults();
        }

        $decoded = json_decode(File::get($path), true);

        $stored = is_array($decoded)
            ? array_filter($decoded, fn ($value, $key) => $this->isAutofillTag((string) $key), ARRAY_FILTER_USE_BOTH)
            : [];

        return array_merge($this->defaults(), $stored);
    }

    /**
     * Persist any provided values whose key belongs to the PPAT auto-fill
     * group. Values are stored verbatim (including empties) so that a field
     * stays recognised as config-managed.
     *
     * @param  array<string, string>  $values
     */
    public function updateConfiguration(array $values): void
    {
        $payload = [];

        foreach ($values as $key => $value) {
            if (is_string($key) && $this->isAutofillTag($key)) {
                $payload[$key] = is_scalar($value) ? (string) $value : '';
            }
        }

        $this->writeConfiguration($payload);
    }

    /**
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return [
            'dppat_name' => '',
            'dppat_work_area' => '',
            'dppat_appointment_number' => '',
            'dppat_appointment_date' => '',
            'dppat_office_address' => '',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function templateDefaults(): array
    {
        return $this->getConfiguration();
    }

    private function getFilePath(): string
    {
        return storage_path('app/private/config/ppat.json');
    }

    /**
     * @param  array<string, string>  $payload
     */
    private function writeConfiguration(array $payload): void
    {
        $path = $this->getFilePath();
        $directory = dirname($path);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0777, true);
        }

        File::put(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
