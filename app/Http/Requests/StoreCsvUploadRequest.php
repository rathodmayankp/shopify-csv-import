<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCsvUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:5120', // 5MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required' => 'Please choose a CSV file to upload.',
            'csv_file.mimes' => 'The file must be a .csv file.',
            'csv_file.max' => 'The file may not be larger than 5MB.',
        ];
    }
}
