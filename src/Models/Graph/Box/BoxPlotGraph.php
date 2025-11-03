<?php

namespace Sae\Models\Graph\Box;

use Sae\Models\Graph\AGraph;

class BoxPlotGraph extends AGraph {
    private array $statistics;

    public function __construct(array $labels, array $datasets) {
        parent::__construct("boxplot", $labels, $datasets);
        $this->statistics = [];
    }

    public function draw(bool $inject = false, string $class = "measure-graph") : void {
        if($inject) {
            echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
            // Ajout de la bonne version du plugin boxplot
            echo '<script src="https://cdn.jsdelivr.net/npm/@sgratzl/chartjs-chart-boxplot@4"></script>';
        }
        
        echo "<div>";
        echo '<canvas id="' . $this->id . '" class="' . $class . '"></canvas>';
        echo "</div>";

        $var = "graph_" . $this->id;

        $options = [
            "responsive" => true,
            "maintainAspectRatio" => false,
            "indexAxis" => 'y',
            "plugins" => [
                "legend" => [
                    "display" => $this->legends
                ]
            ],
            "scales" => [
                "y" => [
                    "beginAtZero" => false
                ]
            ]
        ];

        // Transformer les données pour le format boxplot
        $boxplotData = [];
        foreach ($this->datasets as $dataset) {
            $rawData = $dataset->getData();
            $boxplotData[] = [
                "label" => $dataset->getLabel(),
                "data" => [$rawData], // Le plugin attend un tableau de tableaux
                "backgroundColor" => $dataset->getBackgroundColor(),
                "borderColor" => $dataset->getBorderColor(),
                "borderWidth" => 1
            ];
        }

        echo "<script>";
        echo "const " . $var . " = document.getElementById('" . $this->id . "');\n";
        echo "new Chart(" . $var . ", {\n";
        echo "\ttype: 'boxplot',\n";
        echo "\tdata: " . json_encode(['labels' => $this->labels, 'datasets' => $boxplotData], JSON_PRETTY_PRINT) . ",\n";
        echo "\toptions: " . json_encode($options, JSON_PRETTY_PRINT) . "\n";
        echo "});\n";
        echo "</script>\n";
    }

    public function setStatistics(array $stats): void {
        $this->statistics = $stats;
    }

    public function getStatistics(): array {
        return $this->statistics;
    }
}
