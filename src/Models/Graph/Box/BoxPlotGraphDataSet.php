<?php

namespace Sae\Models\Graph\Box;

use Sae\Models\Graph\AGraphDataSet;

class BoxPlotGraphDataSet extends AGraphDataSet {
    private string $backgroundColor;
    private string $borderColor;
    private array $statistics;
    private array $boxplotStats;

    public function __construct(string $label, array $data, string $color) {
        parent::__construct($label, $data);
        $this->backgroundColor = $this->adjustOpacity($color, 0.5);
        $this->borderColor = $color;
        $this->statistics = [];
        
        // Calcul des statistiques pour la boîte à moustaches
        sort($data);
        $count = count($data);
        $this->boxplotStats = [
            'min' => $count > 0 ? min($data) : 0,
            'q1' => $this->calculateQuartile($data, 0.25),
            'median' => $this->calculateQuartile($data, 0.5),
            'q3' => $this->calculateQuartile($data, 0.75),
            'max' => $count > 0 ? max($data) : 0
        ];
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

    private function calculateQuartile(array $data, float $quartile): float {
        $count = count($data);
        if ($count === 0) return 0;
        
        $index = $quartile * ($count - 1);
        $floor = floor($index);
        $fraction = $index - $floor;
        
        if ($fraction === 0) {
            return $data[$floor];
        }
        return $data[$floor] + ($data[$floor + 1] - $data[$floor]) * $fraction;
    }

    public function getBackgroundColor(): string {
        return $this->backgroundColor;
    }

    public function getBorderColor(): string {
        return $this->borderColor;
    }

    public function setStatistics(array $stats): void {
        $this->statistics = $stats;
    }

    public function getStatistics(): array {
        return $this->statistics;
    }

    public function getBoxplotStats(): array {
        return $this->boxplotStats;
    }
}
