<?php

namespace Sae\Controllers;

use DateTime;
use Sae\Models\Accessor\SynopAccessor;
use Sae\Models\DataObject\MeasureType;
use Sae\Models\Http\FlashMessage;
use Sae\Models\Repository\HistoricRepository;
use Sae\Models\Repository\StationRepository;
use Sae\Models\Repository\UserRepository;
use Sae\Models\Repository\WLibraryRepository;
use Sae\Utils\LabelUtil;
use Sae\Utils\MeasureUtil;

/**
 * Contrôleur des stations
 */
class StationController extends AController {

    /**
     * Constructeur de la classe StationController
     */
    public function __construct(){
        parent::__construct("station");
    }

    /**
     * Gère les requêtes HTTP
     * @param array $path
     * @return bool
     */
    public function index(array $path): bool {

        if(count($path) < 2)
            return false;

        $action = $path[1];
        if($action == "measures") {
            $this->measures($_GET);
            return true;
        } else if($action == "export") {
            $this->export($_GET);
            return true;
        }

        return true;
    }

    /**
     * Affiche les mesures d'une station
     * @param array $data Données de la requête
     * @return void
     */
    private static function collectMeasuresFromForm($form) : array {

        if(!isset($form["station"])) {
            $message = new FlashMessage("error", "Aucune station sélectionnée");
            $message->save();
            return [];
        }

        $repository = new StationRepository();
        $station = $repository->select($form["station"]);
        if($station == null) {
            $message = new FlashMessage("error", "La station sélectionnée n'existe pas");
            $message->save();
            return [];
        }

        $firstEver = MeasureUtil::getFirstEverMeasure();
        $from = MeasureUtil::parseFromDate($form['from'] ?? (new DateTime())->modify("-24 hours"), $firstEver);

        $lastEver = MeasureUtil::getLastEverMeasure();
        $to = MeasureUtil::parseToDate($form['to'] ?? new DateTime(), $lastEver);

        $from->setTime(0, 0, 0);
        $to->setTime(23, 59, 59);

        if($from > $to) {
            $from = (clone $to)->modify("-24 hours");
        }

        $diffInDays = $from->diff($to)->days;
        if($diffInDays < 1)
            $diffInDays = 1;

        $where = "(date >= '" . $from->format('Y-m-d\TH:i:s') . "' AND date <= '" . $to->format('Y-m-d\TH:i:s') . "')";

        if($diffInDays > 7) {
            $where .=  " AND HOUR(date) = 0";
        }

        $measures = SynopAccessor::getMeasuresWithWhere($station->getCode(), $where);

        $typeId = $form['type'] ?? "";
        $measureType = strlen($typeId) > 0 ? MeasureType::getByCode($typeId) : null;
        if($measureType == null) {
            // Todo handle flash message
        } else
            $measures = MeasureUtil::filter($measures, $measureType);

        return $measures;
    }

    /**
     * Affiche les mesures d'une station
     * @param array $data Données de la requête
     * @return void
     */
    private function measures($data) : void {

        if(!isset($data['station'])) {

            $message = new FlashMessage("error", "Aucune station sélectionnée");
            $message->save();

            self::redirectFallBack();
        }

        $repository = new StationRepository();
        $station = $repository->select($data['station']);
        if($station == null) {

            $message = new FlashMessage("error", "La station sélectionnée n'existe pas");
            $message->save();

            self::redirectFallBack();
        }

        $firstEver = MeasureUtil::getFirstEverMeasure();
        $from = MeasureUtil::parseFromDate($data['from'] ?? (new DateTime())->modify("-24 hours"), $firstEver);

        $lastEver = MeasureUtil::getLastEverMeasure();
        $to = MeasureUtil::parseToDate($data['to'] ?? new DateTime(), $lastEver);

        $from->setTime(0, 0, 0);
        $to->setTime(23, 59, 59);

        if($from > $to) {
            $from = (clone $to)->modify("-24 hours");
        }

        $min = ($firstEver != null) ? $firstEver->getDate() : null;
        $max = ($lastEver != null) ? $lastEver->getDate() : null;

        $diffInDays = $from->diff($to)->days;
        if($diffInDays < 1)
            $diffInDays = 1;

        $where = "(date >= '" . $from->format('Y-m-d\TH:i:s') . "' AND date <= '" . $to->format('Y-m-d\TH:i:s') . "')";

        if($diffInDays > 7) {
            $where .=  " AND HOUR(date) = 0";
        }

        $measures = SynopAccessor::getMeasuresWithWhere($station->getCode(), $where);

        $typeId = $data['type'] ?? "";
        $measureType = strlen($typeId) > 0 ? MeasureType::getByCode($typeId) : null;
        if($measureType == null) {
            // Todo handle flash message
        } else
            $measures = MeasureUtil::filter($measures, $measureType);

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

        $userRepo = new UserRepository();
        $user = $userRepo->selectCurrent();

        if($user != null) {
            $wlibraryRepo = new WLibraryRepository();
            $libraries = $wlibraryRepo->selectByUser($user->getId(), view: true);

             // Une fois ici atteint l'utilisateur aura voulu voir les mesures d'une station
            $historicRepo = new HistoricRepository();
            $historicRepo->addToCurrentUser("station", $station->getId());

        } else {
            $libraries = [];
        }

        $likedStations = [];
        if(!empty($libraries)) {
            foreach ($libraries as $library) {
                $lStations = [];
                foreach ($library->getStations() as $station)
                    $lStations[] = $station->getId();
                $likedStations[$library->getId()] = $lStations;
            }
        }

        $likedMeasures = [];
        if(!empty($libraries)) {
            foreach ($libraries as $library) {
                $lMeasures = [];
                foreach ($library->getMeasures() as $measure)
                    $lMeasures[] = $measure->getId();
                $likedMeasures[$library->getId()] = $lMeasures;
            }
        }

        $vars = [
            "station" => $station,
            "measures" => $measures,
            "title" => "Mesures de la station " . $station->getName(),
            "styles" => [
                "station/measures.css"
            ],

            "selectedType" => $measureType,
            "measureTypes" => MeasureType::values(),
            "from" => $from->format("Y-m-d"),
            "to" => $to->format("Y-m-d"),

            "sort" => $sort,

            "first" => $min?->format("Y-m-d H:i:s"),
            "last" => $max?->format("Y-m-d H:i:s"),

            "min" => $min?->format("Y-m-d"),
            "max" => $max?->format("Y-m-d"),

            "logged" => $user != null,
            "libraries" => $libraries,

            "likedStations" => $likedStations,
            "likedMeasures" => $likedMeasures

        ];
        $this->loadView('measures', $vars, autoFlashMessages: false);

    }

    /**
     * Exporte les mesures d'une station vers une météothèque
     * @param array $data Données de la requête
     * @return void
     */
    private function export($data) : void {

        if(!self::requireLogin(null)) {
            $this->measures($data);
            return;
        }

        if(!isset($data["library"])) {
            $message = new FlashMessage("error", "Aucune bibliothèque sélectionnée");
            $message->save();
            self::redirectFallBack();
            return;
        }

        $wlRepository = new WLibraryRepository();
        $userRepo = new UserRepository();

        $user = $userRepo->selectCurrent();
        $library = $wlRepository->select($data["library"]);

        if($library == null || !$wlRepository->isOwnerOf($library->getId(), $user->getId())) {
            $message = new FlashMessage("error", "La bibliothèque sélectionnée n'existe pas");
            $message->save();
            self::redirectFallBack();
            return;
        }

        $measures = self::collectMeasuresFromForm($data);
        if(empty($measures)) {
            $this->measures($data);
            return;
        }

        foreach ($measures as $measure) {
            $wlRepository->addMeasure($library->getId(), $measure->getId(), $measure->getStation(), $measure->getType()->getCode(), $measure->getValue(), $measure->getFormattedDate());
        }

        $currentUrl = $_SERVER['REQUEST_URI'];
        // replace root url to /station/measures
        $currentUrl = str_replace("/export", "/measures", $currentUrl);

        self::redirect($currentUrl);
    }

}
