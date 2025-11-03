<?php

namespace Sae\Models\Graph\Pie;

use Sae\Models\Graph\AGraphDataSet;

class PieGraphDataSet extends AGraphDataSet {

    private array $backgroundColor;

    public function __construct(string $label, array $data, array $backgroundColor) {
        parent::__construct($label, $data);
        $this->backgroundColor = $backgroundColor;
    }

    public function getBackgroundColor(): array {
        return $this->backgroundColor;
    }

}