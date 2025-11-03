<?php

namespace Sae\Models\Graph\Histogram;

use Sae\Models\Graph\AGraph;

class HistogramGraph extends AGraph {
    public function __construct(array $labels, array $datasets) {
        parent::__construct("bar", $labels, $datasets); // Changé de "histogram" à "bar"
    }
}
