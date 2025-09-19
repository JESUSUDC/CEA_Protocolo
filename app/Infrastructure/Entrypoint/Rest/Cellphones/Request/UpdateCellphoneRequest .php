<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Cellphones\Request;

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
            'brand' => 'sometimes|string',
            'imei' => 'sometimes|string',
            'screen_size' => 'sometimes|numeric',
            'megapixels' => 'sometimes|numeric',
            'ram_mb' => 'sometimes|integer',
            'storage_primary_mb' => 'sometimes|integer',
            'storage_secondary_mb' => 'nullable|integer',
            'operating_system' => 'sometimes|string',
            'operator' => 'nullable|string',
            'network_technology' => 'sometimes|string',
            'wifi' => 'sometimes|boolean',
            'bluetooth' => 'sometimes|boolean',
            'camera_count' => 'sometimes|integer',
            'cpu_brand' => 'sometimes|string',
            'cpu_speed_ghz' => 'sometimes|numeric',
            'nfc' => 'sometimes|boolean',
            'fingerprint' => 'sometimes|boolean',
            'ir' => 'sometimes|boolean',
            'water_resistant' => 'sometimes|boolean',
            'sim_count' => 'sometimes|integer',
        ];
    }
}
