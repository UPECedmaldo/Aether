<?php

namespace Sae\Models\Map;

use JsonSerializable;

/**
 * Classe représentant un marqueur sur une carte
 */
class MapViewMarker implements JsonSerializable {

    private MapViewPoint $point;
    private string $hover;

    public function __construct(MapViewPoint $point, string $hover) {
        $this->point = $point;
        $this->hover = $hover;
    }

    public function getPoint(): MapViewPoint {
        return $this->point;
    }

    public function getHover(): string {
        return $this->hover;
    }

    public function jsonSerialize(): array {
        return [
            'point' => $this->point,
            'hover' => $this->hover
        ];
    }

}