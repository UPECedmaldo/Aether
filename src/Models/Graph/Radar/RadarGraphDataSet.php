<?php

namespace Sae\Models\Graph\Radar;

use Sae\Models\Graph\AGraphDataSet;

class RadarGraphDataSet extends AGraphDataSet {

    private string $backgroundColor;
    private string $borderColor;
    private bool $fill = true;

    public function __construct(string $label, array $data, string $backgroundColor, string $borderColor) {
        parent::__construct($label, $data);
        $this->backgroundColor = $backgroundColor;
        $this->borderColor = $borderColor;
    }

    public function isFill(): bool {
        return $this->fill;
    }

    public function setFill(bool $fill): RadarGraphDataSet {
        $this->fill = $fill;
        return $this;
    }

    public function getBorderColor(): string {
        return $this->borderColor;
    }

    public function getBackgroundColor(): string {
        return $this->backgroundColor;
    }

}