<?php
declare(strict_types=1);

namespace App\Application\Cellphone\Dto\Response;

final class CellphoneListResponse implements \JsonSerializable
{
    /**
     * @param CellphoneResponse[] $items
     */
    public function __construct(public array $items, public int $total = 0) {}

    /**
     * Serializa la lista para la API.
     *
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        $items = array_map(function ($i) {
            // Si el item es JsonSerializable lo usamos, si es DTO lo serializa también
            if (is_object($i) && $i instanceof \JsonSerializable) {
                return $i->jsonSerialize();
            }
            // Si es un array o objeto simple, fuerza array
            if (is_array($i)) {
                return $i;
            }
            if (is_object($i) && method_exists($i, 'toArray')) {
                return $i->toArray();
            }
            // fallback coercitivo: obtiene propiedades públicas
            if (is_object($i)) {
                return get_object_vars($i);
            }
            return (array) $i;
        }, $this->items);

        return [
            'items' => $items,
            'total' => $this->total,
        ];
    }
}
