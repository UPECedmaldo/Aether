<?php

namespace Sae\Models\Graph\Bar;

use Sae\Models\Graph\AGraph;

class BarGraph extends AGraph {

    public function __construct(array $labels, array $datasets) {
        parent::__construct("bar", $labels, $datasets);
    }

}