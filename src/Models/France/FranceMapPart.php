<?php

namespace Sae\Models\France;

use JsonSerializable;


/**
 * Classe représentant une partie de la carte de France (Région)
 */
class FranceMapPart implements JsonSerializable {

    private string $region;
    private string $text;
    private string $color;
    private string $fillColor;

    public function __construct(string $region, string $text, string $color, string $fillColor) {
        $this->region = $region;
        $this->text = $text;
        $this->color = $color;
        $this->fillColor = $fillColor;
    }

    public function getRegion(): string {
        return $this->region;
    }

    public function getText(): string {
        return $this->text;
    }

    public function getColor(): string {
        return $this->color;
    }

    public function getFillColor(): string {
        return $this->fillColor;
    }

    public function jsonSerialize(): array {
        return [
            "region" => $this->region,
            "text" => $this->text,
            "color" => $this->color,
            "fillColor" => $this->fillColor
        ];
    }
}