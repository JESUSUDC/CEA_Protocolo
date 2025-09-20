<?php
declare(strict_types=1);

namespace App\Infrastructure\Entrypoint\Rest\Cellphones\Request;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCellphoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización se maneja en middleware
    }

    public function rules(): array
    {
        return [
            'brand' => 'required|string',
            'imei' => 'required|string',
            'screen_size' => 'required|numeric',
            'megapixels' => 'required|numeric',
            'ram_mb' => 'required|integer',
            'storage_primary_mb' => 'required|integer',
            'storage_secondary_mb' => 'nullable|integer',
            'operating_system' => 'required|string',
            'operator' => 'nullable|string',
            'network_technology' => 'required|string',
            'wifi' => 'required|boolean',
            'bluetooth' => 'required|boolean',
            'camera_count' => 'required|integer',
            'cpu_brand' => 'required|string',
            'cpu_speed_ghz' => 'required|numeric',
            'nfc' => 'required|boolean',
            'fingerprint' => 'required|boolean',
            'ir' => 'required|boolean',
            'water_resistant' => 'required|boolean',
            'sim_count' => 'required|integer',
        ];
    }
}
