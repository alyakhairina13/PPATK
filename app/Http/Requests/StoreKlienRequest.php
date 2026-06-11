<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKlienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:150',
            'nik' => 'required|digits:16|unique:klien,nik',
            'tempat_tanggal_lahir' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string|max:20',
            'pekerjaan' => 'required|string|max:100',
            'npwp' => 'nullable|string|max:30',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'nik' => 'NIK',
            'tempat_tanggal_lahir' => 'tempat tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'alamat' => 'alamat',
            'nomor_telepon' => 'nomor telepon',
            'pekerjaan' => 'pekerjaan',
            'npwp' => 'NPWP',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar dalam sistem.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
        ];
    }
}
