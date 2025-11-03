<?php

namespace Sae\Models\Repository;

use DateTime;
use Sae\Models\Accessor\SQLAccessor;
use Sae\Models\DataObject\Measure;
use Sae\Models\DataObject\MeasureType;
use Sae\Models\DataObject\View\ViewMeasure;

/**
 * Classe représentant un dépôt de mesures
 */
class MeasureRepository extends ARepository {

    public function __construct() {
        parent::__construct("mesure", "id_mesure", ["id_station", "type", "valeur", "date", "date_ajout"]);
    }

    protected function createFromArray(array $data): ViewMeasure {

        $date = new DateTime($data["date"]);

        $id = $data["id_mesure"];
        $measure = new ViewMeasure(
            $data["id_station"],
            $date,
            MeasureType::getByCode($data["type"]),
            $data["valeur"],
            new DateTime($data["date_ajout"])
        );

        $measure->setId($id);
        return $measure;
    }

    /**
     * Retourne les stations fournissant les mesures
     * @param array $measures
     * @return array
     */
    public function getStationOfMeasures(array $measures) : array {

        $pdo = SQLAccessor::getConnection();
        //$sql = "SELECT reference_id FROM favoris WHERE id_meteotheque = :id_meteotheque AND type = 'mesure' AND reference_id IN (";
        //$sql .= implode(",", array_fill(0, count($measureIds), "?"));


        if(empty($measures))
            return [];

        $measuresId = [];
        foreach($measures as $measure)
            $measuresId[] = $measure->getId();

        $sql = "SELECT DISTINCT(id_station) FROM mesure WHERE id_mesure IN (";
        $sql .= implode(",", array_fill(0, count($measuresId), "?"));
        $sql .= ")";

        $stmt = $pdo->prepare($sql);

        $stmt->execute($measuresId);
        $results = $stmt->fetchAll();

        $stations = [];
        $stationRepository = new StationRepository();
        foreach($results as $result) {
            $station = $stationRepository->select($result["id_station"]);
            if($station != null)
                $stations[] = $station;
        }

        return $stations;
    }

    public function oldMeasures(DateTime $from, DateTime $to) : array {

        $query = "SELECT * FROM mesures_historiques WHERE date BETWEEN :from AND :to";
        $stmt = SQLAccessor::getConnection()->prepare($query);

        $stmt->bindValue(":from", $from->format("Y-m-d H:i:s"));
        $stmt->bindValue(":to", $to->format("Y-m-d H:i:s"));

        $stmt->execute();
        $results = $stmt->fetchAll();

        if($results == null)
            return [];

        MeasureType::init();
        $mapping = [
            "temperature" => MeasureType::$Temperature,
            "precipitation" => MeasureType::$Precipitation,
            "pression_mere" => MeasureType::$SeaLevelPressure,
            "vitesse_vent" => MeasureType::$WindSpeed,
            "visibilite" => MeasureType::$Visibility,
            "nubulosite" => MeasureType::$CloudCover,
            "presssion_station" => MeasureType::$StationPressure,
            "geopotentiel" => MeasureType::$GeopotentialHeight,
            "hauteur_neige" => MeasureType::$SnowHeight,
        ];

        $measures = [];
        foreach($results as $result) {
            $date = new DateTime($result["date"]);

            foreach ($mapping as $key => $type) {
                $value = $result[$key];

                $measure = new Measure($result["id_station"], $date, $type, $value, $value);
                $measure->setId($result["id"]);

                $measures[] = $measure;
            }
        }

        return $measures;
    }


    public function oldestMeasureDate() : ?Measure {

        $query = "SELECT * FROM mesures_historiques WHERE date = (SELECT MIN(date) FROM mesure)";
        $stmt = SQLAccessor::getConnection()->prepare($query);

        $stmt->execute();
        $result = $stmt->fetch();

        if($result == null)
            return null;

        $date = new DateTime($result["date"]);

        return new Measure(
            $result["id_station"],
            $date,
            MeasureType::$Temperature,
            $result["temperature"],
            $result["temperature"]
        );
    }

}