<?php

namespace Sae\Models\Graph;

/**
 * Classe abstraite représentant un graphique
 */
abstract class AGraph {

    private static string $scriptUrl = "https://cdn.jsdelivr.net/npm/chart.js";
    public static string $scriptDiv = "<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
        <script src='https://cdn.jsdelivr.net/npm/chartjs-chart-box-and-violin-plot@4.0.0/build/index.umd.min.js'></script>";

    protected string $id;
    protected string $type;
    protected array $labels;
    protected array $datasets;

    protected bool $legends = true;
    protected string $unit = "";

    public function __construct(string $type, array $labels, array $datasets) {
        $this->id = uniqid();
        $this->type = $type;
        $this->labels = $labels;
        $this->datasets = $datasets;
    }

    /** Permet de dessiner le graphique
     * @param bool $inject
     * @param string $class
     * @return void
     */
    public function draw(bool $inject = false, string $class = "measure-graph") : void {
        echo "<div>";
        echo '<canvas id="' . $this->id . '" class="' . $class . '"></canvas>';
        echo "</div>";

        if($inject) {
            echo self::$scriptDiv;
        }

        $var = "graph_" . $this->id;

        $options = array(
            "maintainAspectRatio" => false,
            "responsive" => true,
            "animation" => array(
                "duration" => 0
            ),
            "plugins" => array(
                "legend" => array(
                    "display" => $this->legends
                ),
                "tooltip" => array(
                    "enabled" => true
                )
            ),
            "scales" => array(
                "y" => array(
                    "beginAtZero" => true
                )
            )
        );

        // Units

        $fixUnit = false;
        if($fixUnit) {

            if($this->unit !== "") {
                $options["plugins"] = array(
                    "tooltip" => array(
                        "callbacks" => array(
                            "label" => null,
                        )
                    )
                );
            }

            $labelCallback = "function(item) { return item.parsed.y + ' " . $this->unit . "'; }";
            $options["plugins"]["tooltip"]["callbacks"]["label"] = $labelCallback;

            $jsonOptions = json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $jsonOptions = str_replace(
                '"' . $labelCallback . '"',
                'function(item) { return (typeof item.parsed.y == "number" ? item.parsed.y.toFixed(2) : item.parsed.y) + \' ' . $this->unit . '\'; }',
                $jsonOptions
            );

        } else {
            $jsonOptions = json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        echo "<script>";
        echo "const " . $var . " = document.getElementById('" . $this->id . "');\n";
        echo "new Chart(" . $var . ", {\n";
            echo "\ttype: '" . $this->type . "',\n";

            $data = array(
                "labels" => $this->labels,
                "datasets" => $this->datasets
            );

            echo "\tdata: " . json_encode($data, JSON_PRETTY_PRINT) . ",\n";
            echo "\toptions: " . $jsonOptions . "\n";
        echo "});\n";
        echo "</script>\n";
    }

    public function getLabels(): array {
        return $this->labels;
    }

    public function getDatasets(): array {
        return $this->datasets;
    }

    public function hasLegends() : bool {
        return $this->legends;
    }

    /** Permet de définir si on peut masquer les mesures
     * @param bool $legends
     * @return void
     */
    public function setLegends(bool $legends) : void {
        $this->legends = $legends;
    }

    public function getUnit() : string {
        return $this->unit;
    }

    public function setUnit(string $unit) : void {
        $this->unit = $unit;
    }

}
