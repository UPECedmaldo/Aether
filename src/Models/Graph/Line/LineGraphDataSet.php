<?php

namespace Sae\Models\Graph\Line;

use Sae\Models\Graph\AGraphDataSet;

/**
 * Classe représentant un jeu de données pour un graphique en courbes
 */
class LineGraphDataSet extends AGraphDataSet {

    private string $borderColor;
    private bool $fill = false;
    private float $tension = 0.1;

    public function __construct(string $label, array $data, string $borderColor) {
        parent::__construct($label, $data);
        $this->borderColor = $borderColor;
    }

    public function getBorderColor(): string{
        return $this->borderColor;
    }

    public function isFill(): bool {
        return $this->fill;
    }

    public function setFill(bool $fill): LineGraphDataSet {
        $this->fill = $fill;
        return $this;
    }

    public function getTension(): float {
        return $this->tension;
    }

    public function setTension(float $tension): LineGraphDataSet {
        $this->tension = $tension;
        return $this;
    }

}