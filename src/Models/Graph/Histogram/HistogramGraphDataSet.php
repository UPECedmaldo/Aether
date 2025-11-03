<?php

namespace Sae\Models\Graph\Histogram;

use Sae\Models\Graph\AGraphDataSet;

class HistogramGraphDataSet extends AGraphDataSet {
    private string $backgroundColor;
    private string $borderColor;
    private int $barThickness;

    public function __construct(string $label, array $data, string $color) {
        parent::__construct($label, $data);
        $this->backgroundColor = $this->adjustOpacity($color, 0.5);
        $this->borderColor = $color;
        $this->barThickness = 20;
    }

    private function adjustOpacity(string $color, float $opacity): string {
        $hex = ltrim($color, '#');
        return sprintf('rgba(%d, %d, %d, %.1f)', 
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
            $opacity
        );
    }

    public function getBackgroundColor(): string {
        return $this->backgroundColor;
    }

    public function getBorderColor(): string {
        return $this->borderColor;
    }

    public function getBarThickness(): int {
        return $this->barThickness;
    }
}
