<?php
declare(strict_types=1);

namespace Infrastructure\Entrypoint\Rest\Cellphones\Request;

use Illuminate\Foundation\Http\FormRequest;

final class CreateCellphoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // apply auth middleware elsewhere
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
            'operating_system' => 'required|string',
            'network_technology' => 'required|string',
            'camera_count' => 'required|integer',
            'cpu_brand' => 'required|string',
            'cpu_speed_ghz' => 'required|numeric',
            'sim_count' => 'required|integer',
        ];
    }
}
