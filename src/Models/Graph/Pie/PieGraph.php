<?php

namespace Sae\Models\Graph\Pie;

use Sae\Models\Graph\AGraph;

class PieGraph extends AGraph {

    public function __construct(array $labels, array $datasets, string $type = "pie") {
        parent::__construct($type, $labels, $datasets);
    }

}