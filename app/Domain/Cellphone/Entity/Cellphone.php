<?php
declare(strict_types=1);

namespace App\Domain\Cellphone\Entity;

use App\Domain\Shared\AggregateRoot;
use App\Domain\Cellphone\ValueObject\CellphoneId;
use App\Domain\Cellphone\ValueObject\Brand;
use App\Domain\Cellphone\ValueObject\Imei;
use App\Domain\Cellphone\ValueObject\ScreenSize;
use App\Domain\Cellphone\ValueObject\Megapixels;
use App\Domain\Cellphone\ValueObject\RAM;
use App\Domain\Cellphone\ValueObject\Storage;
use App\Domain\Cellphone\ValueObject\OperatingSystem;
use App\Domain\Cellphone\ValueObject\Operator;
use App\Domain\Cellphone\ValueObject\NetworkTechnology;
use App\Domain\Cellphone\ValueObject\Connectivity;
use App\Domain\Cellphone\ValueObject\Cameras;
use App\Domain\Cellphone\ValueObject\Cpu;
use App\Domain\Cellphone\ValueObject\BooleanFeature;
use App\Domain\Cellphone\ValueObject\SimCount;
use App\Domain\Cellphone\Event\CellphoneRegistered;
use App\Domain\Cellphone\Event\CellphoneSpecificationsUpdated;

final class Cellphone extends AggregateRoot
{
    private CellphoneId $id;
    private Brand $brand;
    private Imei $imei;
    private ScreenSize $screenSize;
    private Megapixels $megapixels;
    private RAM $ram;
    private Storage $primaryStorage;
    private ?Storage $secondaryStorage;
    private OperatingSystem $os;
    private ?Operator $operator;
    private NetworkTechnology $networkTechnology;
    private Connectivity $connectivity;
    private Cameras $cameras;
    private Cpu $cpu;
    private BooleanFeature $nfc;
    private BooleanFeature $fingerprint;
    private BooleanFeature $ir;
    private BooleanFeature $waterResistant;
    private SimCount $simCount;

    private function __construct(
        CellphoneId $id,
        Brand $brand,
        Imei $imei,
        ScreenSize $screenSize,
        Megapixels $megapixels,
        RAM $ram,
        Storage $primaryStorage,
        ?Storage $secondaryStorage,
        OperatingSystem $os,
        ?Operator $operator,
        NetworkTechnology $networkTechnology,
        Connectivity $connectivity,
        Cameras $cameras,
        Cpu $cpu,
        BooleanFeature $nfc,
        BooleanFeature $fingerprint,
        BooleanFeature $ir,
        BooleanFeature $waterResistant,
        SimCount $simCount
    ) {
        $this->id = $id;
        $this->brand = $brand;
        $this->imei = $imei;
        $this->screenSize = $screenSize;
        $this->megapixels = $megapixels;
        $this->ram = $ram;
        $this->primaryStorage = $primaryStorage;
        $this->secondaryStorage = $secondaryStorage;
        $this->os = $os;
        $this->operator = $operator;
        $this->networkTechnology = $networkTechnology;
        $this->connectivity = $connectivity;
        $this->cameras = $cameras;
        $this->cpu = $cpu;
        $this->nfc = $nfc;
        $this->fingerprint = $fingerprint;
        $this->ir = $ir;
        $this->waterResistant = $waterResistant;
        $this->simCount = $simCount;
    }

    public static function register(
        CellphoneId $id,
        Brand $brand,
        Imei $imei,
        ScreenSize $screenSize,
        Megapixels $megapixels,
        RAM $ram,
        Storage $primaryStorage,
        ?Storage $secondaryStorage,
        OperatingSystem $os,
        ?Operator $operator,
        NetworkTechnology $networkTechnology,
        Connectivity $connectivity,
        Cameras $cameras,
        Cpu $cpu,
        BooleanFeature $nfc,
        BooleanFeature $fingerprint,
        BooleanFeature $ir,
        BooleanFeature $waterResistant,
        SimCount $simCount
    ): self {
        $cell = new self(
            $id,
            $brand,
            $imei,
            $screenSize,
            $megapixels,
            $ram,
            $primaryStorage,
            $secondaryStorage,
            $os,
            $operator,
            $networkTechnology,
            $connectivity,
            $cameras,
            $cpu,
            $nfc,
            $fingerprint,
            $ir,
            $waterResistant,
            $simCount
        );

        $cell->recordEvent(new CellphoneRegistered($id, $imei->toString(), $brand->toString()));
        return $cell;
    }

    // --- getters (examples, puedes añadir los que necesites)
    public function id(): CellphoneId { return $this->id; }
    public function imei(): Imei { return $this->imei; }
    public function brand(): Brand { return $this->brand; }
    public function isWaterResistant(): bool { return $this->waterResistant->value(); }
    // Getters que faltaban
    public function screenSize(): ScreenSize { return $this->screenSize; }
    public function megapixels(): Megapixels { return $this->megapixels; }
    public function ram(): RAM { return $this->ram; }
    public function primaryStorage(): Storage { return $this->primaryStorage; }
    public function secondaryStorage(): ?Storage { return $this->secondaryStorage; }
    public function os(): OperatingSystem { return $this->os; }
    public function operator(): ?Operator { return $this->operator; }
    public function networkTechnology(): NetworkTechnology { return $this->networkTechnology; }
    public function connectivity(): Connectivity { return $this->connectivity; }
    public function cameras(): Cameras { return $this->cameras; }
    public function cpu(): Cpu { return $this->cpu; }
    public function nfc(): BooleanFeature { return $this->nfc; }
    public function fingerprint(): BooleanFeature { return $this->fingerprint; }
    public function ir(): BooleanFeature { return $this->ir; }
    public function waterResistant(): BooleanFeature { return $this->waterResistant; }
    public function simCount(): SimCount { return $this->simCount; }


    // Example behaviour: update core specifications (atomic operation)
    public function updateSpecifications(
        ?Brand $brand = null,
        ?ScreenSize $screenSize = null,
        ?Megapixels $megapixels = null,
        ?RAM $ram = null,
        ?Storage $primaryStorage = null,
        ?Storage $secondaryStorage = null,
        ?OperatingSystem $os = null,
        ?NetworkTechnology $networkTechnology = null,
        ?Connectivity $connectivity = null,
        ?Cameras $cameras = null,
        ?Cpu $cpu = null,
        ?BooleanFeature $nfc = null,
        ?BooleanFeature $fingerprint = null,
        ?BooleanFeature $ir = null,
        ?BooleanFeature $waterResistant = null,
        ?SimCount $simCount = null
    ): void {
        $changed = false;

        if ($brand !== null && !$this->brand->equals($brand)) {
            $this->brand = $brand;
            $changed = true;
        }

        if ($screenSize !== null && !$this->screenSize->equals($screenSize)) {
            $this->screenSize = $screenSize;
            $changed = true;
        }

        if ($megapixels !== null && !$this->megapixels->equals($megapixels)) {
            $this->megapixels = $megapixels;
            $changed = true;
        }

        if ($ram !== null && !$this->ram->equals($ram)) {
            $this->ram = $ram;
            $changed = true;
        }

        if ($primaryStorage !== null && !$this->primaryStorage->equals($primaryStorage)) {
            $this->primaryStorage = $primaryStorage;
            $changed = true;
        }

        if ($secondaryStorage !== null) {
            if ($this->secondaryStorage === null || !$this->secondaryStorage->equals($secondaryStorage)) {
                $this->secondaryStorage = $secondaryStorage;
                $changed = true;
            }
        }

        if ($os !== null && !$this->os->equals($os)) {
            $this->os = $os;
            $changed = true;
        }

        if ($networkTechnology !== null && !$this->networkTechnology->equals($networkTechnology)) {
            $this->networkTechnology = $networkTechnology;
            $changed = true;
        }

        if ($connectivity !== null && !$this->connectivity->equals($connectivity)) {
            $this->connectivity = $connectivity;
            $changed = true;
        }

        if ($cameras !== null && !$this->cameras->equals($cameras)) {
            $this->cameras = $cameras;
            $changed = true;
        }

        if ($cpu !== null && !$this->cpu->equals($cpu)) {
            $this->cpu = $cpu;
            $changed = true;
        }

        if ($nfc !== null && !$this->nfc->equals($nfc)) {
            $this->nfc = $nfc;
            $changed = true;
        }

        if ($fingerprint !== null && !$this->fingerprint->equals($fingerprint)) {
            $this->fingerprint = $fingerprint;
            $changed = true;
        }

        if ($ir !== null && !$this->ir->equals($ir)) {
            $this->ir = $ir;
            $changed = true;
        }

        if ($waterResistant !== null && !$this->waterResistant->equals($waterResistant)) {
            $this->waterResistant = $waterResistant;
            $changed = true;
        }

        if ($simCount !== null && !$this->simCount->equals($simCount)) {
            $this->simCount = $simCount;
            $changed = true;
        }

        if ($changed) {
            $this->recordEvent(new CellphoneSpecificationsUpdated($this->id));
        }
    }

    public function assignOperator(Operator $operator): void
    {
        if ($this->operator === null || !$this->operator->equals($operator)) {
            $this->operator = $operator;
            $this->recordEvent(new CellphoneSpecificationsUpdated($this->id));
        }
    }

    public function changeImei(Imei $imei): void
    {
        if (!$this->imei->equals($imei)) {
            $this->imei = $imei;
            $this->recordEvent(new CellphoneSpecificationsUpdated($this->id));
        }
    }
}
