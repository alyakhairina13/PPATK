<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class PpatConfigurationService
{
    /**
     * @return array<string, string>
     */
    public function getConfiguration(): array
    {
        $path = $this->getFilePath();

        if (! File::exists($path)) {
            $defaults = $this->defaults();
            $this->writeConfiguration($defaults);

            return $defaults;
        }

        $decoded = json_decode(File::get($path), true);

        if (! is_array($decoded)) {
            $defaults = $this->defaults();
            $this->writeConfiguration($defaults);

            return $defaults;
        }

        return array_merge($this->defaults(), array_intersect_key($decoded, $this->defaults()));
    }

    /**
     * @param  array<string, string>  $values
     */
    public function updateConfiguration(array $values): void
    {
        $payload = array_merge($this->defaults(), array_intersect_key($values, $this->defaults()));

        $this->writeConfiguration($payload);
    }

    /**
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return [
            'ppat_name' => '',
            'work_area' => '',
            'appointment_number' => '',
            'appointment_date' => '',
            'office_address' => '',
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
