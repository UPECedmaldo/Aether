<?php

namespace Sae\Controllers;

use DateTime;
use Sae\Models\DataObject\MeasureType;
use Sae\Models\DataObject\WLibrary;
use Sae\Models\Graph\Bar\BarGraph;
use Sae\Models\Graph\Bar\BarGraphDataSet;
use Sae\Models\Graph\Line\LineGraph;
use Sae\Models\Graph\Line\LineGraphDataSet;
use Sae\Models\Graph\Pie\PieGraph;
use Sae\Models\Graph\Pie\PieGraphDataSet;
use Sae\Models\Graph\Radar\RadarGraph;
use Sae\Models\Graph\Radar\RadarGraphDataSet;
use Sae\Models\Http\FlashMessage;
use Sae\Models\Repository\MeasureRepository;
use Sae\Models\Repository\StationRepository;
use Sae\Models\Repository\UserRepository;
use Sae\Models\Repository\WLibraryRepository;
use Sae\Models\Repository\HistoricRepository;
use Sae\Models\Recommendation\RecommendationEngine;
use Sae\Utils\FormUtil;
use Sae\Utils\MeasureUtil;

/**
 * Contrôleur des météothèques
 */
class WLibraryController extends AController {

    /**
     * Constructeur de la classe WLibraryController
     */
    public function __construct() {
        parent::__construct("library");
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
        if($type == "list") {
            $this->list();
            return true;
        } else if($type == "create") {
            $this->create();
            return true;
        } else if($type == "created") {
            $this->created();
            return true;
        } else if($type == "add") {
            $this->add();
            return true;
        } else if($type == "del") {
            $this->del();
            return true;
        } else if($type == "consult") {
            $this->consult($_GET);
            return true;
        } else if($type == "edit") {
            $this->see($_GET, true);
            return true;
        } else if($type == "graph") {
            $this->graph($_GET);
            return true;
        } else if($type == "update") {
            $this->update();
            return true;
        } else if($type == "updated") {
            $this->updated();
            return true;
        } else if($type == "delete") {
            $this->delete();
            return true;
        }

        return false;
    }

    /**
     * Affiche la liste des météothèques
     * @return void
     */
    private function list() : void {
        // Récupérer les données nécessaires
        $wlRepository = new WLibraryRepository();
        $userRepository = new UserRepository();

        // Obtenir l'utilisateur actuel
        $user = $userRepository->selectCurrent();
        $logged = ($user != null);

        // Récupérer les météothèques publiques
        $publicLibraries = $wlRepository->selectPublic(view: true);

        // Si l'utilisateur est connecté, récupérer ses météothèques
        $myRepositories = [];
        $recommandations = [];
        if ($logged) {
            $myRepositories = $wlRepository->selectByUser($user->getId(), view: true);

            // Ajout des recommandations si la classe existe
            if (class_exists('Sae\Models\Recommendation\RecommendationEngine')) {
                $recommandations = RecommendationEngine::getRecommendations($user->getId());
            }
        }

        // CORRIGÉ - Simplement utiliser les noms des fichiers CSS sans balises HTML
        $vars = [
            'title' => 'Météothèques',
            'styles' => [
                'library/list.css',
                'library/recommendations.css'
            ],
            'publicLibraries' => $publicLibraries,
            'logged' => $logged,
            'myRepositories' => $myRepositories,
            'recommandations' => $recommandations
        ];

        // Charger la vue
        $this->loadView('list', $vars);
    }

    /**
     * Crée une météothèque
     * @return void
     */
    private function create() : void {

        if(!self::requireLogin("/library/create"))
            return;

        $this->loadView("create", vars: [
            "title" => "Créer une météothèque",
            "styles" => ["library/create.css"],
        ], autoFlashMessages: false);

    }

    /**
     * Crée une météothèque
     * @return void
     */
    private function created() : void {

        if(!self::requireLogin("/library/create"))
            return;

        if(!FormUtil::checkFields($_POST, ["name", "state", "color"])) {
            self::redirect("/library/create");
            return;
        }

        $name = $_POST["name"];
        if(strlen($name) < 0 || strlen($name) > 256) {
            $flashMessage = new FlashMessage("error", "Le nom doit être compris entre 1 et 256 caractères");
            $flashMessage->save();
            self::redirect("/library/create");
            return;
        }

        $wlRepository = new WLibraryRepository();
        $userRepository = new UserRepository();

        $user = $userRepository->selectCurrent();
        $existing = $wlRepository->selectByName($name, $user->getId());
        if($existing != null) {
            $flashMessage = new FlashMessage("error", "Vous avez déjà une météothèque avec ce nom");
            $flashMessage->save();
            self::redirect("/library/create");
            return;
        }

        $color = $_POST["color"];
        if(!preg_match("/^#[0-9a-fA-F]{6}$/", $color)) {
            $flashMessage = new FlashMessage("error", "Couleur invalide");
            $flashMessage->save();
            self::redirect("/library/create");
            return;
        }

        if(str_starts_with($color, "#"))
            $color = substr($color, 1);

        $state = $_POST["state"];
        switch($state) {
            case "public":
                $state = 1;
                break;
            case "private":
                $state = 0;
                break;
            case "friends":
                $state = 2;
                break;
            default:
                $flashMessage = new FlashMessage("error", "État invalide");
                $flashMessage->save();
                self::redirect("/library/create");
                return;
        }

        $library = new WLibrary(-1, $user->getId(), $name, $state, date("Y-m-d H:i:s"), $color);
        $wlRepository->insert($library);

        $flashMessage = new FlashMessage("success", "Météothèque créée");
        $flashMessage->save();

        self::redirect("/library/list");
    }

    /**
     * Ajoute une référence à une météothèque
     * @return bool
     */
    private function add() : bool {

        if(!self::requireLogin("/library/list")) {
            echo "Not logged in";
            return false;
        }

        if(!FormUtil::checkFields($_POST, ["library", "add_type"], flashMessages: false)) {
            echo "Invalid fields";
            return false;
        }

        $wlRepository = new WLibraryRepository();
        $userRepository = new UserRepository();

        $library = $wlRepository->select($_POST["library"]);
        if($library == null) {
            echo "Invalid library";
            return false;
        }

        $user = $userRepository->selectCurrent();
        if(!$wlRepository->isOwnerOf($library->getId(), $user->getId())) {
            echo "Not owner";
            return false;
        }

        $add_type = $_POST["add_type"];
        switch ($add_type) {

            case "station":
                if(!FormUtil::checkFields($_POST, ["station_code"], flashMessages: false)) {
                    echo "Invalid fields - station";
                    return false;
                }

                $stationCode = $_POST["station_code"];
                $result = $wlRepository->addStation($library->getId(), $stationCode);
                break;

            case "measure": // removed check for value
                if(!FormUtil::checkFields($_POST, ["measure_id", "station_code", "type", "date"], flashMessages: false)) {
                    echo "Invalid fields - measure";
                    return false;
                }

                $measureId = $_POST["measure_id"];

                $type = $_POST["type"];
                $objectType = MeasureType::getByCode($type);
                if($objectType == null) { // Invalid type
                    echo "Invalid type";
                    return false;
                }

                $stationCode = $_POST["station_code"];
                $value = floatval($_POST["value"] ?? 0);
                $date = $_POST["date"];

                $result = $wlRepository->addMeasure($library->getId(), $measureId, $stationCode, $type, $value, $date);
                break;

            default:
                return false;
        }

        if($result) {
            echo "Success";
            return true;
        } else {
            echo "Already in";
            return false;
        }
    }

    /**
     * Supprime une référence d'une météothèque
     * @return bool
     */
    private function del() {

        if(!self::requireLogin("/library/list")) {
            echo "Not logged in";
            return false;
        }

        if(!FormUtil::checkFields($_POST, ["library", "del_type", "reference_id"], flashMessages: false)) {
            echo "Invalid fields";
            return false;
        }

        $wlRepository = new WLibraryRepository();
        $userRepository = new UserRepository();

        $library = $wlRepository->select($_POST["library"]);
        if($library == null) {
            echo "Invalid library";
            return false;
        }

        $user = $userRepository->selectCurrent();
        if(!$wlRepository->isOwnerOf($library->getId(), $user->getId())) {
            echo "Not owner";
            return false;
        }

        $del_type = $_POST["del_type"];
        if($del_type != "station" && $del_type != "measure") {
            echo "Invalid type";
            return false;
        }

        if($del_type == "measure")
            $del_type = "mesure";

        $reference_id = $_POST["reference_id"];
        $result = $wlRepository->deleteReference($library->getId(), $del_type, $reference_id);

        if($result) {
            echo "Success";
            return true;
        } else {
            echo "Not found";
            return false;
        }
    }

    /**
     * Affiche une météothèque
     * @param array $data Données de la requête
     * @param bool $edit Mode édition
     * @return void
     */
    private function see($data, bool $edit) {

        if($edit && !self::requireLogin("/library/" . ($edit ? "edit" : "consult") . "?library=" . $_GET["library"]))
            return;

        if(!FormUtil::checkFields($_GET, ["library"])) {
            self::redirect("/library/list");
            return;
        }

        $wlRepository = new WLibraryRepository();
        $userRepository = new UserRepository();

        $user = $userRepository->selectCurrent();
        $library = $wlRepository->select($_GET["library"]);
        if($library == null || ($edit && !$wlRepository->isOwnerOf($library->getId(), $user->getId()))) {

            $flashMessage = new FlashMessage("error", "Météothèque introuvable");
            $flashMessage->save();

            self::redirect("/library/list");
            return;
        }

        $isOwner = $user != null && $wlRepository->isOwnerOf($library->getId(), $user->getId());
        $edit = $edit && $isOwner;
        $library = $wlRepository->convertToView($library);

        $firstEver = $wlRepository->getFirstDateOf($library);
        $from = MeasureUtil::parseFromDate($data['from'] ?? $firstEver, $firstEver);

        if($from < $firstEver)
            $from = $firstEver;

        $lastEver = $wlRepository->getLastDateOf($library);
        $to = MeasureUtil::parseToDate($data['to'] ?? new DateTime(), $lastEver);

        if($to > $lastEver)
            $to = $lastEver;

        $from->setTime(0, 0, 0);
        $to->setTime(23, 59, 59);

        if($from > $to) {
            $from = (clone $to)->modify("-24 hours");
        }

        $measures = $wlRepository->getMeasures($library);
        $measures = MeasureUtil::sortByDate($measures);

        $length = count($measures);
        for ($i = 0; $i < $length; $i++) {
            if($measures[$i]->getDate() < $from) {
                unset($measures[$i]);
            } else if($measures[$i]->getDate() > $to) {
                unset($measures[$i]);
            }
        }

        $typeId = $data['type'] ?? "";

        $measureType = strlen($typeId) > 0 ? MeasureType::getByCode($typeId) : null;
        if($measureType == null) {
            // Todo handle flash message
        } else
            $measures = MeasureUtil::filter($measures, $measureType);

        $stations = $wlRepository->getStations($library);

        $measureRepository = new MeasureRepository();
        $measureStations = $measureRepository->getStationOfMeasures($measures);

        $sort = $data['sort'] ?? "date_desc";
        switch ($sort) {
            default:
            case "date_asc":
                usort($measures, fn($a, $b) => $a->getDate() <=> $b->getDate());
                break;
            case "date_desc":
                usort($measures, fn($a, $b) => $b->getDate() <=> $a->getDate());
                break;
            case "value_asc":
                usort($measures, fn($a, $b) => $a->getValue() <=> $b->getValue());
                break;
            case "value_desc":
                usort($measures, fn($a, $b) => $b->getValue() <=> $a->getValue());
                break;
        }

        $this->loadView("see", vars: [

            "title" => "Météothèque",
            "styles" => ["library/see.css"],

            "library" => $library,
            "edit" => $edit,
            "isOwner" => $isOwner,

            "selectedType" => $measureType,
            "measureTypes" => MeasureType::values(),

            "from" => $from->format("Y-m-d"),
            "to" => $to->format("Y-m-d"),

            "sort" => $sort,

            "min" => $firstEver->format("Y-m-d"),
            "max" => $lastEver->format("Y-m-d"),

            "logged" => $user != null,

            "measures" => $measures,
            "stations" => $stations,
            "measureStations" => $measureStations

        ], autoFlashMessages: false);

    }

    private function graph($data) : void {

        if(!FormUtil::checkFields($data, ["library", "station", "from", "to"])) {
            self::redirect("/library/list");
            return;
        }

        $wlRepository = new WLibraryRepository();

        $library = $data["library"];
        $library = $wlRepository->select($library);
        if($library == null) {
            $message = new FlashMessage("error", "Météothèque introuvable");
            $message->save();
            self::redirect("/library/list");
            return;
        }

        $stationRepository = new StationRepository();
        $station = $data["station"];
        $station = $stationRepository->select($station);

        if($station == null) {
            $message = new FlashMessage("error", "Station introuvable");
            $message->save();
            self::redirect("/library/list");
            return;
        }

        $from = MeasureUtil::parseFromDate($data['from'], $wlRepository->getFirstDateOf($library));
        $to = MeasureUtil::parseToDate($data['to'], $wlRepository->getLastDateOf($library));

        $from->setTime(0, 0, 0);
        $to->setTime(23, 59, 59);

        $rawMeasures = $wlRepository->getMeasures($library);

        $measures = [];
        foreach ($rawMeasures as $measure) {
            if(($measure->getStation() == $station->getCode() || $measure->getStation() == $station->getId()) && $measure->getDate() >= $from && $measure->getDate() <= $to)
                $measures[] = $measure;
        }

        if(empty($measures)) {
            $message = new FlashMessage("error", "Aucune mesure trouvée");
            $message->save();
            self::redirect("/library/list");
            return;
        }

        $measureTypes = [];
        foreach ($measures as $measure)
            if(!in_array($measure->getType()->getName(), $measureTypes))
                $measureTypes[] = $measure->getType()->getName();

        $graphs = [];

        foreach (MeasureType::values() as $type) {

            $rawDataSet = self::generateDataSet($measures, $type);
            if(empty($rawDataSet))
                continue;

            $graphName = $type->getName() . " (" . $type->getUnit() . ")";
            if($type === MeasureType::$Temperature || $type === MeasureType::$SeaLevelPressure || $type === MeasureType::$Visibility) {

                $backgroundColor = $type === MeasureType::$Temperature ? "rgba(238,240,10,1)" : ($type === MeasureType::$SeaLevelPressure ? " rgba(139,10,223,1)" : "#0b9823");

                $dataSet = new LineGraphDataSet($graphName, $rawDataSet[1], $backgroundColor);
                $graph = new LineGraph($rawDataSet[0], [$dataSet]);

            } else if($type === MeasureType::$Precipitation || $type === MeasureType::$StationPressure) {

                $backgroundColor = $type === MeasureType::$Precipitation ? "rgba(12,64,117,1)" : "rgba(139,10,223,1)";
                $backgroundColors = [];
                for($i = 0; $i < count($rawDataSet[0]); $i++)
                    $backgroundColors[] = $backgroundColor;

                $dataSet = new BarGraphDataSet($graphName, $rawDataSet[1], $backgroundColors);
                $graph = new BarGraph($rawDataSet[0], [$dataSet]);

            } else if($type === MeasureType::$WindSpeed) {

                $backgroundColor = "rgba(0, 0, 255, 0.2)";
                $borderColor = "#0000ff";

                $dataSet = new RadarGraphDataSet($graphName, $rawDataSet[1], $backgroundColor, $borderColor);
                $graph = new RadarGraph($rawDataSet[0], [$dataSet]);

            } else if($type === MeasureType::$CloudCover) {

                $amounts = [
                    "Peu nuageux" => 0, # 0-25%
                    "Nuageux" => 0, # 25-50%
                    "Très nuageux" => 0, # 50-75%
                    "Couvert" => 0 # 75-100%
                ];

                foreach ($measures as $measure) {
                    $value = $measure->getValue();
                    if($value < 25)
                        $amounts["Peu nuageux"]++;
                    else if($value < 50)
                        $amounts["Nuageux"]++;
                    else if($value < 75)
                        $amounts["Très nuageux"]++;
                    else
                        $amounts["Couvert"]++;
                }

                $dataSet = new PieGraphDataSet($graphName, array_values($amounts), ["#0081ff", "#8f8f8f", " #3d3d3d", "#360802"]);
                $graph = new PieGraph(array_keys($amounts), [$dataSet]);

            } else
                echo "Type {$type->getName()} non implémenté";

            if(isset($graph))
                $graphs[] = $graph;
        }

        $this->loadView("graph", vars: [

            "title" => "Graphique",
            "styles" => ["library/graph.css"],

            "library" => $library,
            "measures" => $measures,
            "station" => $station,
            "from" => $from,
            "to" => $to,
            "measureTypes" => $measureTypes,

            "graphs" => $graphs

        ], autoFlashMessages: false);
    }

    /**
     * Met à jour une météothèque
     * @return void
     */
    private function update() : void {

        if(!self::requireLogin("/library/list"))
            return;

        if(!FormUtil::checkFields($_GET, ["library"])) {
            self::redirect("/library/list");
            return;
        }

        $wlRepository = new WLibraryRepository();
        $userRepository = new UserRepository();

        $user = $userRepository->selectCurrent();
        $library = $wlRepository->select($_GET["library"]);
        if($library == null || !$wlRepository->isOwnerOf($library->getId(), $user->getId())) {
            $flashMessage = new FlashMessage("error", "Météothèque introuvable");
            $flashMessage->save();
            self::redirect("/library/list");
            return;
        }

        $this->loadView("update", vars: [

            "title" => "Modifier la météothèque",
            "styles" => ["library/update.css"],

            "library" => $library

        ], autoFlashMessages: false);

    }

    /**
     * Met à jour une météothèque
     * @return void
     */
    private function updated() : void {


        if(!self::requireLogin("/library/list"))
            return;

        if(!FormUtil::checkFields($_POST, ["library", "name", "state", "color"])) {
            self::redirect("/library/list");
            return;
        }

        $wlRepository = new WLibraryRepository();
        $userRepository = new UserRepository();

        $user = $userRepository->selectCurrent();
        $library = $wlRepository->select($_POST["library"]);
        if($library == null || !$wlRepository->isOwnerOf($library->getId(), $user->getId())) {
            $flashMessage = new FlashMessage("error", "Météothèque introuvable");
            $flashMessage->save();
            self::redirect("/library/list");
            return;
        }

        $name = $_POST["name"];
        if(strlen($name) < 0 || strlen($name) > 256) {
            $flashMessage = new FlashMessage("error", "Le nom doit être compris entre 1 et 256 caractères");
            $flashMessage->save();
            self::redirect("/library/update?library=" . $library->getId());
            return;
        }

        $existing = $wlRepository->selectByName($name, $user->getId());
        if($existing != null && $existing->getId() != $library->getId()) {
            $flashMessage = new FlashMessage("error", "Vous avez déjà une météothèque avec ce nom");
            $flashMessage->save();
            self::redirect("/library/update?library=" . $library->getId());
            return;
        }

        $state = $_POST["state"];
        switch ($state) {
            case "public":
                $state = 1;
                break;
            case "private":
                $state = 0;
                break;
            case "friends":
                $state = 2;
                break;
            default:
                $flashMessage = new FlashMessage("error", "État invalide");
                $flashMessage->save();
                self::redirect("/library/update?library=" . $library->getId());
                return;
        }

        $color = $_POST["color"];
        if(!preg_match("/^#[0-9a-fA-F]{6}$/", $color)) {
            $flashMessage = new FlashMessage("error", "Couleur invalide");
            $flashMessage->save();
            self::redirect("/library/update?library=" . $library->getId());
            return;
        }

        if(str_starts_with($color, "#"))
            $color = substr($color, 1);

        $wlRepository->update($library->getId(), [
            "nom" => $name,
            "couleur" => $color,
            "etat" => $state
        ]);

        $flashMessage = new FlashMessage("success", "Météothèque mise à jour");
        $flashMessage->save();

        self::redirect("/library/list");
    }

    /**
     * Supprime une météothèque
     * @return void
     */
    private function delete() : void {

        if(!self::requireLogin("/library/list"))
            return;

        if(!FormUtil::checkFields($_POST, ["library"])) {
            self::redirect("/library/list");
            return;
        }

        $wlRepository = new WLibraryRepository();
        $userRepository = new UserRepository();

        $user = $userRepository->selectCurrent();
        $library = $wlRepository->select($_POST["library"]);
        if($library == null || !$wlRepository->isOwnerOf($library->getId(), $user->getId())) {
            $flashMessage = new FlashMessage("error", "Météothèque introuvable");
            $flashMessage->save();
            self::redirect("/library/list");
            return;
        }

        $wlRepository->delete($library->getId());

        $flashMessage = new FlashMessage("success", "Météothèque supprimée");
        $flashMessage->save();

        self::redirect("/library/list");
    }

    private static function generateDataSet(array $measures, MeasureType $type) : array {

        $filtered = MeasureUtil::filter($measures, $type);
        if(empty($filtered))
            return [];

        $compresed = MeasureUtil::compressMeasures($filtered, 10);

        $labels = [];
        $dataSet = [];

        foreach ($compresed as $measure) {
            $labels[] = $measure->getDate()->format("Y-m-d H:i");
            $dataSet[] = $measure->getValue();
        }

        return [$labels, $dataSet];
    }

    // Ajoutez cette méthode pour enregistrer les consultations de météothèques
    private function logLibraryView(int $libraryId) : void {
        // Vérifier si l'utilisateur est connecté
        $userRepository = new UserRepository();
        $user = $userRepository->selectCurrent();

        if($user !== null) {
            // Ajouter à l'historique de l'utilisateur si la classe HistoricRepository existe
            if (class_exists('Sae\Models\Repository\HistoricRepository')) {
                $historicRepository = new HistoricRepository();
                // Utiliser "station" ou "mesure" au lieu de "lib"
                // "station" est généralement préférable car une météothèque est une collection de stations
                $historicRepository->addToCurrentUser("station", $libraryId);
            }
        }
    }

    // Modifiez la méthode consult pour inclure l'enregistrement des vues
    private function consult($data) : void {
        // Récupérer l'ID de la bibliothèque
        $id = $data['library'] ?? null;
        if($id == null) {
            $this->list();
            return;
        }

        // Log de la consultation
        $this->logLibraryView((int)$id);

        // Appeler la méthode see avec edit=false
        $this->see($data, false);
    }

}