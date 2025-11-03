<?php

namespace Sae\Models\Graph\Line;

use Sae\Models\Graph\AGraph;

/**
 * Classe représentant un graphique en courbes
 */
class LineGraph extends AGraph {

    public function __construct(array $labels, array $datasets) {
        parent::__construct("line", $labels, $datasets);
    }

}