<?php

namespace Sae\Controllers;

use DateTime;
use Sae\Models\Accessor\ClimateAccessor;
use Sae\Models\Accessor\SynopAccessor;
use Sae\Models\DataObject\MeasureType;
use Sae\Models\France\FranceMap;
use Sae\Models\France\FranceMapPart;
use Sae\Models\Http\Session;
use Sae\Models\Map\MapView;
use Sae\Models\Map\MapViewMarker;
use Sae\Models\Repository\RegionRepository;
use Sae\Models\Repository\StationRepository;
use Sae\Utils\MeasureUtil;
use Sae\Utils\OtherUtil;

/**
 * Contrôleur de la page d'accueil
 */
class IndexController extends AController {

    /**
     * Constructeur de la classe IndexController
     */
    public function __construct() {
        parent::__construct("index");
    }

    /**
     * Gère les requêtes HTTP
     * @param array $path
     * @return bool
     */
    public function index(array $path): bool {

        if(count($path) <= 1 || $path[1] == "welcome") {
            $this->welcome();
            return true;
        } else if($path[1] == "map") {
            $this->map();
            return true;
        } else if($path[1] == "credits") {
            $this->credits();
            return true;
        } else if($path[1] == "climate-map") {
            $this->climateMap();
            return true;
        }

        return false;
    }

    /**
     * Affiche la page d'erreur 404
     * avec une petite blague
     * @return void
     */
    public function error404() {

        $jokes = [
            "Cette page n'a jamais existé... comme un ciel bleu pendant une tempête. 🌪️",
            "Cette page est introuvable, un peu comme un flocon de neige en été. ❄️☀️",
            "Cette page ? Elle est aussi réelle qu'un arc-en-ciel la nuit. 🌈🌌",
            "Impossible de trouver cette page, elle est partie avec le vent. 🌬️",
            "Cette page n'existe pas, tout comme la pluie dans un désert. 🌵☀️",
            "Chercher cette page, c'est comme attendre un éclair sans nuages. ⚡☁️",
            "Cette page ? Une légende urbaine, comme la météo qui ne se trompe jamais. 📡❌",
            "Cette page n'a jamais existé, un peu comme de la neige sur une plage tropicale. 🏖️❄️",
            "Cette page est aussi introuvable qu'un parapluie dans une tornade. 🌪️☂️",
            "Cette page n'existe pas, un peu comme un soleil qui brille sous la pluie. ☔☀️"
        ];

        $joke = $jokes[rand(0, count($jokes) - 1)];

        $this->loadView("404", vars: [
            "title" => "404",
            "styles" => ["index/404.css"],

            "joke" => $joke
        ]);
    }

    /**
     * Affiche la page de bienvenue
     * @return void
     */
    private function welcome() : void {

        $today = new DateTime();
        $month = $today->format("m");

        $formattedMonth = "On est quel mois déjà ?";
        switch ($month) {
            case "01":
                $formattedMonth = "Janvier";
                break;
            case "02":
                $formattedMonth = "Février";
                break;
            case "03":
                $formattedMonth = "Mars";
                break;
            case "04":
                $formattedMonth = "Avril";
                break;
            case "05":
                $formattedMonth = "Mai";
                break;
            case "06":
                $formattedMonth = "Juin";
                break;
            case "07":
                $formattedMonth = "Juillet";
                break;
            case "08":
                $formattedMonth = "Août";
                break;
            case "09":
                $formattedMonth = "Septembre";
                break;
            case "10":
                $formattedMonth = "Octobre";
                break;
            case "11":
                $formattedMonth = "Novembre";
                break;
            case "12":
                $formattedMonth = "Décembre";
                break;
        }

        $todayEarly = new DateTime();
        $todayEarly->setTime(0, 0, 0);

        $todayLate = new DateTime();
        $todayLate->setTime(23, 59, 59);

        $session = Session::getInstance();
        if($session->has("welcome-temp")) {

            $cached = $session->get("welcome-temp");
            $min = $cached["min"];
            $max = $cached["max"];
            $current = $cached["current"];

        } else {

            $stationCode = str_pad(81401, 5, "0", STR_PAD_LEFT);
            $measures = SynopAccessor::getMeasuresBetweenDate($stationCode, $todayEarly, $todayLate, 24);

            MeasureType::init();
            $measures = MeasureUtil::filter($measures, MeasureType::$Temperature);

            $min = null; $max = null; $current = null;
            if(count($measures) > 0) {

                $min = $measures[0]->getValue();
                $max = $measures[0]->getValue();
                $current = $measures[0]->getValue();

                foreach ($measures as $measure) {
                    $value = $measure->getValue();
                    if($value < $min)
                        $min = $value;
                    if($value > $max)
                        $max = $value;
                }

            }

            $session->set("welcome-temp", [
                "min" => $min,
                "max" => $max,
                "current" => $current
            ]);

        }

        $formattedToday = $today->format("d") . " " . $formattedMonth . " " . $today->format("Y");

        $this->loadView("welcome", vars: [
            "title" => "Bienvenue",
            "styles" => ["index/welcome.css"],

            "today" => $formattedToday,
            "min" => $min ?? "N/A",
            "max" => $max ?? "N/A",
            "current" => $current ?? "N/A"
        ]);
    }

    /**
     * Affiche la carte de France
     * @return void
     */
    private function map() : void {

        $stationRepository = new StationRepository();
        $stations = $stationRepository->selectAll();

        $mapView = new MapView();
        foreach ($stations as $station) {
            $point = $stationRepository->coordinatesOf($station->getId());
            if($point === null)
                continue;

            $hover = "<h3>{$station->getName()}</h3>
                <p>Id : {$station->getId()} 
                <br>
                Code : {$station->getCode()}
                <br>
                Commune : {$station->getCityName()}
                <br>
                Département : {$station->getDepartmentName()}
                <br>
                Région : {$station->getRegionName()}
                <br>
                EPCI : {$station->getEpciName()}
                <br><br>";

            $hover .= <<<HTML
                <a href="/station/measures?station={$station->getId()}">
                    <button>Mesures</button>
                </a>
                <a href="/graph/measures?station={$station->getId()}">
                    <button>Graphiques</button>
                </a>
                <a href="/graph/select?selected={$station->getId()}#form">
                    <button>Comparer</button>
                </a>
            HTML;

            $marker = new MapViewMarker($point, $hover);
            $mapView->addMarker($marker);
        }

        $convertedStations = [];
        foreach ($stations as $station) {

            if($station->getName() === null)
                continue;

            $point = $stationRepository->coordinatesOf($station->getId());
            if($point === null)
                continue;

            $convertedStations[] = [
                "name" => $station->getName(),
                "long" => $point->getLongitude(),
                "lat" => $point->getLatitude()
            ];
        }

        $this->loadView("map", vars: [

            "title" => "Carte des stations",
            "styles" => ["index/map.css"],

            "stations" => $stations,
            "convertedStations" => $convertedStations,
            "map" => $mapView
        ]);

    }

    /**
     * Affiche la page des crédits
     * @return void
     */
    private function credits() : void {
        $this->loadView("credits", vars: [
            "title" => "Crédits",
            "styles" => ["index/credits.css"]
        ]);
    }

    private function climateMap() : void {
        $from = new DateTime();
        $from->modify("-30 days");
        $to = new DateTime();
        $relative = false;

        if(isset($_GET["from"]))
            $from = new DateTime($_GET["from"]);
        if(isset($_GET["to"]))
            $to = new DateTime($_GET["to"]);

        if(isset($_GET["compare2010"]))
            $relative = $_GET["compare2010"] === "1" || $_GET["compare2010"] === "true" || $_GET["compare2010"] === "on";

        MeasureType::init();

        $parts = [];
        $regions = [];

        $regionRepository = new RegionRepository();
        $stationRepository = new StationRepository();
        foreach ($stationRepository->selectAll() as $station) {
            $region = $station->getRegion();
            if(in_array($region, $regions))
                continue;

            $regions[] = $region;
            $region = $regionRepository->select($region);
            if($region === null)
                continue;

            $point = $stationRepository->coordinatesOf($station->getId());

            $average = 0;
            if ($relative) {
                // Si mode comparaison, utiliser climate2010Anomaly
                $average = MeasureUtil::climateAnomaly($region, $from, $to);
            } else {
                // Mode normal: température moyenne
                $temperature = ClimateAccessor::fetchTemperatures($point, $from, $to);
                if (count($temperature) > 0) {
                    foreach ($temperature as $measure)
                        $average += $measure->getValue();
                    $average /= count($temperature);
                }
            }

            $average = round($average, 2);

            $minTemp = $relative ? -0.5 : 0;
            $maxTemp = $relative ? 2 : 15;

            $color = MeasureUtil::coloredTemperature($average, $minTemp, $maxTemp);
            $darker = OtherUtil::darkerColor($color);

            $part = new FranceMapPart(
                $region->getName(),
                ($average > 0 ? "+" : "") . $average . " " . MeasureType::$Temperature->getUnit(),
                $color,
                $darker
            );
            $parts[] = $part;
        }

        $franceMap = new FranceMap();
        foreach ($parts as $part)
            $franceMap->addPart($part);

        $this->loadView("climate-map", vars: [
            "title" => $relative ? "Anomalie climatique (vs 2010)" : "Carte climatique",
            "franceMap" => $franceMap,

            "from" => $from->format("Y-m-d"),
            "to" => $to->format("Y-m-d"),
            "relative" => $relative,
        ]);
    }

}