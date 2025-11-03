<?php

namespace Sae\Models\Map;

use JsonSerializable;

/**
 * Classe représentant un point sur une carte
 */
class MapViewPoint implements JsonSerializable {

    private float $latitude;
    private float $longitude;

    public function __construct(float $latitude, float $longitude) {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getLatitude(): float {
        return $this->latitude;
    }

    public function getLongitude(): float {
        return $this->longitude;
    }

    public function jsonSerialize(): array {
        return [
            'lat' => $this->latitude,
            'lon' => $this->longitude
        ];
    }

}