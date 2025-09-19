<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class CellphoneModel extends Model
{
    protected $table = 'cellphones';

    protected $primaryKey = 'id';

    public $incrementing = false; // we use domain ids (UUID strings)

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'brand',
        'imei',
        'screen_size',
        'megapixels',
        'ram_mb',
        'storage_primary_mb',
        'storage_secondary_mb',
        'operating_system',
        'operator',
        'network_technology',
        'wifi',
        'bluetooth',
        'camera_count',
        'cpu_brand',
        'cpu_speed_ghz',
        'nfc',
        'fingerprint',
        'ir',
        'water_resistant',
        'sim_count',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'wifi' => 'boolean',
        'bluetooth' => 'boolean',
        'nfc' => 'boolean',
        'fingerprint' => 'boolean',
        'ir' => 'boolean',
        'water_resistant' => 'boolean',
        'screen_size' => 'float',
        'megapixels' => 'float',
        'ram_mb' => 'integer',
        'storage_primary_mb' => 'integer',
        'storage_secondary_mb' => 'integer',
        'cpu_speed_ghz' => 'float',
        'camera_count' => 'integer',
        'sim_count' => 'integer',
    ];
}
