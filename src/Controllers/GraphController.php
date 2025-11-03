<?php

namespace Sae\Controllers;

use DateTime;
use Sae\Models\Accessor\SynopAccessor;
use Sae\Models\DataObject\GraphType;
use Sae\Models\DataObject\MeasureType;
use Sae\Models\Graph\AGraph;
use Sae\Models\Graph\AGraphDataSet;
use Sae\Models\Graph\Line\LineGraph;
use Sae\Models\Graph\Line\LineGraphDataSet;
use Sae\Models\Graph\Bar\BarGraph;
use Sae\Models\Graph\Bar\BarGraphDataSet;
use Sae\Models\Graph\Box\BoxPlotGraph;
use Sae\Models\Graph\Box\BoxPlotGraphDataSet;
use Sae\Models\Graph\Histogram\HistogramGraph;
use Sae\Models\Graph\Histogram\HistogramGraphDataSet;
use Sae\Models\Graph\Radar\RadarGraph;
use Sae\Models\Graph\Radar\RadarGraphDataSet;
use Sae\Models\Http\FlashMessage;
use Sae\Models\Repository\StationRepository;
use Sae\Utils\LabelUtil;
use Sae\Utils\MeasureUtil;
use Sae\Models\DataObject\StatisticType;

/**
 * Contrôleur des graphiques
 */
class GraphController extends AController {

    /**
     * Constructeur de la classe GraphController
     */
    public function __construct(){
        parent::__construct("graph");
    }

    /**
     * Gère les requêtes HTTP
     * @param array $path
     * @return bool
     */
    public function index(array $path): bool {

        if(!isset($path[1]))
            return false;

        $type = $path[1];
        if($type == "measures") {
            $this->measures($_GET);
            return true;
        } else if($type == "select") {
            $this->select($_GET);
            return true;
        } else if($type == "compare") {
            $this->compare($_POST);
            return true;
        }

        return true;
    }

    /**
     * Affiche les mesures d'une station
     * @param array $data Données de la requête
     * @return void
     */
    private function measures($data) : void {

        $stationId = $data['station'] ?? null;
        if($stationId == null)
            return; // Todo: Error

        $repository = new StationRepository();

        $station = $repository->select($stationId);
        if($station == null)
            return; // Todo: Error

        $firstEver = MeasureUtil::getFirstEverMeasure();
        $from = MeasureUtil::parseFromDate($data['from'] ?? null, $firstEver);

        $lastEver = MeasureUtil::getLastEverMeasure();
        $to = MeasureUtil::parseToDate($data['to'] ?? null, $lastEver);

        // Swap dates if from is greater than to
        if($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $points = 10;
        $measures = SynopAccessor::getMeasuresBetweenDate($station->getCode(), $from, $to, $points);

        $graphs = [];
        $labels = LabelUtil::generateDateLabels($from, $to, $points);

        $selectedGraphType = $data['graphType'] ?? 'line';
        $selectedStatType = $data['statType'] ?? 'raw';

        if (!GraphType::isValid($selectedGraphType)) {
            $selectedGraphType = 'line';
        }
        if (!StatisticType::isValid($selectedStatType)) {
            $selectedStatType = 'raw';
        }

        foreach (MeasureType::values() as $type) {
            $filtered = MeasureUtil::filter($measures, $type);
            $values = array_map(fn($measure) => $measure->getValue(), $filtered);

            if(empty($values)) continue;

            // Appliquer la transformation statistique
            $transformedValues = $this->transformValues($values, $selectedStatType);

            $dataSet = $this->createDataSet($type, $transformedValues, $selectedGraphType);
            $graph = $this->createGraph($selectedGraphType, $labels, [$dataSet]);
            $graph->setLegends(false);
            $graphs[] = $graph;
        }

        $this->loadView("measures", [

            "title" => "Graphiques de mesures",
            "styles" => ["graph/measures.css"],

            "selected" => $station,

            "from" => $from->format('Y-m-d'),
            "to" => $to->format('Y-m-d'),

            "min" => ($firstEver != null) ? $firstEver->getDate()->format('Y-m-d') : null,
            "max" => ($lastEver != null) ? $lastEver->getDate()->format('Y-m-d') : null,

            "graphs" => $graphs,
            "selectedGraphType" => $selectedGraphType,
            "selectedStatType" => $selectedStatType
        ]);

    }

    private function createDataSet($type, $values, $graphType): AGraphDataSet {
        $color = $type->getColor();
        switch ($graphType) {
            case 'line':
                return new LineGraphDataSet($type->getName() . " (" . $type->getUnit() . ")", $values, $color);
            case 'histogram':
                return new HistogramGraphDataSet($type->getName(), $values, $color);
            case 'boxplot':
                return new BoxPlotGraphDataSet($type->getName(), $values, $color);
            case 'radar':
                return new RadarGraphDataSet($type->getName(), $values, $this->adjustOpacity($color, 0.5), $color);
            case 'bar':
                return new BarGraphDataSet($type->getName(), $values, [$color]);
            default:
                return new LineGraphDataSet($type->getName(), $values, $color);
        }
    }

    private function createGraph($type, $labels, $datasets): AGraph {
        switch ($type) {
            case 'line':
                return new LineGraph($labels, $datasets);
            case 'histogram':
                return new HistogramGraph($labels, $datasets);
            case 'boxplot':
                return new BoxPlotGraph($labels, $datasets);
            case 'radar':
                return new RadarGraph($labels, $datasets);
            case 'bar':
                return new BarGraph($labels, $datasets);
            default:
                return new LineGraph($labels, $datasets);
        }
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

    private function transformValues(array $values, string $statType): array {
        switch ($statType) {
            case 'mean':
                return $this->calculateMovingAverage($values);
            case 'std':
                return $this->calculateStandardDeviation($values);
            case 'cumulative':
                return $this->calculateCumulative($values);
            case 'variation':
                return $this->calculateVariation($values);
            default:
                return $values;
        }
    }

    private function calculateMovingAverage(array $values, int $window = 3): array {
        $result = [];
        for ($i = 0; $i < count($values); $i++) {
            $sum = 0;
            $count = 0;
            for ($j = max(0, $i - $window + 1); $j <= min(count($values) - 1, $i + $window - 1); $j++) {
                $sum += $values[$j];
                $count++;
            }
            $result[] = $sum / $count;
        }
        return $result;
    }

    private function calculateStandardDeviation(array $values): array {
        $mean = array_sum($values) / count($values);
        return array_map(function($value) use ($mean) {
            return sqrt(pow($value - $mean, 2));
        }, $values);
    }

    private function calculateCumulative(array $values): array {
        $result = [];
        $sum = 0;
        foreach ($values as $value) {
            $sum += $value;
            $result[] = $sum;
        }
        return $result;
    }

    private function calculateVariation(array $values): array {
        $result = [0];
        for ($i = 1; $i < count($values); $i++) {
            $result[] = $values[$i] - $values[$i-1];
        }
        return $result;
    }

    /**
     * Affiche la page de sélection des stations
     * @param array $data Données de la requête
     * @return void
     */
    private function select($data) : void {

        $selectedId = $data["selected"] ?? null;
        $repository = new StationRepository();
        if($selectedId != null)
            $selected = $repository->select($selectedId);
        else
            $selected = null;

        $firstEver = MeasureUtil::getFirstEverMeasure();
        $from = new DateTime();
        $from->modify("-1 month");

        if($firstEver != null && $firstEver->getDate() > $from)
            $from = $firstEver->getDate();

        $to = new DateTime();
        $lastEver = MeasureUtil::getLastEverMeasure();
        if($lastEver != null && $lastEver->getDate() < $to)
            $to = $lastEver->getDate();


        if($from > $to) {
            $from = (clone $to)->modify("-1 month");
        }


        $formattedFrom = $from->format('Y-m-d');
        $formattedTo = $to->format('Y-m-d');

        $this->loadView("select", [

            "title" => "Comparer les Stations",
            "styles" => ["graph/select.css"],

            "stations" => $repository->selectAll(),
            "selected" => $selected,
            "measures" => MeasureType::values(),

            "from" => $formattedFrom,
            "to" => $formattedTo,

            "min" => ($firstEver != null) ? $firstEver->getDate()->format('Y-m-d') : null,
            "max" => ($lastEver != null) ? $lastEver->getDate()->format('Y-m-d') : null

        ], autoFlashMessages: false);

    }

    /**
 * Compare les mesures de deux stations
 * @param array $data Données de la requête
 * @return void
 */
private function compare($data) : void {

    $repository = new StationRepository();

    $station1Id = $data['station1'] ?? null;
    if($station1Id == null || ($station1 = $repository->select($station1Id)) == null) {

        $message = new FlashMessage("error", "Veuillez sélectionner une première station.");
        $message->save();

        self::redirect("/graph/select#form");
        return;
    }

    $station2Id = $data['station2'] ?? null;
    if($station2Id == null || ($station2 = $repository->select($station2Id)) == null) {

        $message = new FlashMessage("error", "Veuillez sélectionner une seconde station.");
        $message->save();

        self::redirect("/graph/select#form");
        return;
    }

    if($station1Id == $station2Id) {

        $message = new FlashMessage("error", "Les deux stations sélectionnées sont identiques.");
        $message->save();

        self::redirect("/graph/select#form");
        return;
    }

    $firstEver = MeasureUtil::getFirstEverMeasure();
    $from = MeasureUtil::parseFromDate($data['from'] ?? null, $firstEver);

    $lastEver = MeasureUtil::getLastEverMeasure();
    $to = MeasureUtil::parseToDate($data['to'] ?? null, $lastEver);

    // Swap dates if from is greater than to
    if($from > $to) {
        $temp = $from;
        $from = $to;
        $to = $temp;
    }

    if(isset($data['measure'])) {
        $selectedMeasure = MeasureType::getByCode($data['measure']);
    }

    if(!isset($selectedMeasure) || $selectedMeasure == null)
        $selectedMeasure = MeasureType::values()[0];

    $points = 10;
    $measures1 = SynopAccessor::getMeasuresBetweenDate($station1->getCode(), $from, $to, $points);
    $measures2 = SynopAccessor::getMeasuresBetweenDate($station2->getCode(), $from, $to, $points);

    // Filtrer les mesures pour les deux stations
    $filtered1 = MeasureUtil::filter($measures1, $selectedMeasure);
    $filtered2 = MeasureUtil::filter($measures2, $selectedMeasure);

    $measures = [
        $station1->getName() => $measures1,
        $station2->getName() => $measures2
    ];

    $graphs = [];
    $labels = LabelUtil::generateDateLabels($from, $to, 10);

    $color = self::generateRandomColor();
    $measuresTypes = [$selectedMeasure];
    foreach ($measuresTypes as $type) {

        $dataSets = [];
        foreach ($measures as $stationName => $stationMeasures) {

            $filtered = MeasureUtil::filter($stationMeasures, $type);
            $values = [];
            foreach($filtered as $measure) {
                $value = $measure->getValue();
                $values[] = $value;
            }

            $color = self::generateOppositeColor($color);
            $dataSet = new LineGraphDataSet($stationName, $values, $color);
            $dataSets[] = $dataSet;

        }

        $graph = new LineGraph($labels, $dataSets);
        $graph->setUnit($type->getUnit());
        $graphs[] = $graph;

    }

    $this->loadView("compare", [

        "title" => "Comparer les Stations",
        "styles" => ["graph/measures.css"],

        "selected1" => $station1,
        "selected2" => $station2,

        "from" => $from->format('Y-m-d'),
        "to" => $to->format('Y-m-d'),

        "min" => ($firstEver != null) ? $firstEver->getDate()->format('Y-m-d') : null,
        "max" => ($lastEver != null) ? $lastEver->getDate()->format('Y-m-d') : null,

        "measures" => MeasureType::values(),
        "selectedMeasure" => $selectedMeasure->getCode(),

        "graphs" => $graphs,
        
        // Passer les mesures filtrées à la vue
        "measures1" => $filtered1,
        "measures2" => $filtered2
    ]);
}

    /**
     * Génère une couleur aléatoire
     * @return string
     */
    private static function generateRandomColor() : string {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    /**
     * Génère la couleur opposée
     * @param string $hex
     * @return string
     */
    private static function generateOppositeColor(string $hex): string {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $oppositeR = 255 - $r;
        $oppositeG = 255 - $g;
        $oppositeB = 255 - $b;

        return sprintf("#%02x%02x%02x", $oppositeR, $oppositeG, $oppositeB);
    }

}