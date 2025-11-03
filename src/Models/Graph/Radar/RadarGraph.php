<?php

namespace Sae\Models\Graph\Radar;

use Sae\Models\Graph\AGraph;

class RadarGraph extends AGraph {

    public function __construct(array $labels, array $datasets) {
        parent::__construct("radar", $labels, $datasets);
    }

}