<?php

namespace Sae\Models\Graph\Bar;

use Sae\Models\Graph\AGraphDataSet;

class BarGraphDataSet extends AGraphDataSet {

    private array $backgroundColor;

    public function __construct(string $label, array $data, array $backgroundColor) {
        parent::__construct($label, $data);
        $this->backgroundColor = $backgroundColor;
    }

    public function getBackgroundColor(): array {
        return $this->backgroundColor;
    }

}