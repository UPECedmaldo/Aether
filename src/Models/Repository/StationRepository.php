<?php

namespace Sae\Models\Repository;

use Sae\Models\Accessor\SQLAccessor;
use Sae\Models\DataObject\Region;
use Sae\Models\DataObject\Station;
use Sae\Models\Map\MapViewPoint;

/**
 * Classe représentant un dépôt de stations
 */
class StationRepository extends ARepository {

    public function __construct() {
        parent::__construct("station", "id_station", ["id_station", "code_station", "code_region", "code_departement", "code_commune", "code_epci"]);
    }

    protected function createFromArray(array $data): ?Station {
        return new Station($data['id_station'], $data['code_station'], $data['code_region'], $data['code_departement'], $data['code_commune'], $data['code_epci']);
    }

    /**
     * Retourne une station à partir de son code
     * @param string $code
     * @return Station|null
     */
    public function selectByCode(string $code) : ?Station {

        $code = str_pad($code, 5, "0", STR_PAD_LEFT);

        $pdo = SQLAccessor::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM station WHERE code_station = :code");
        $stmt->execute(['code' => $code]);

        return $this->performSQL($stmt);
    }

    /**
     * Retourne les stations appartenant à une région
     * @param Region $region
     * @return array
     */
    public function selectInRegion(Region $region) : array {

        $pdo = SQLAccessor::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM station WHERE code_region = :region");
        $stmt->execute(['region' => $region->getCode()]);

        return $this->performSQL($stmt, list: true);
    }

    /**
     * Retourne les stations dont le nom contient une chaîne de caractères
     * @param string $search
     * @return array
     */
    public function selectLike(string $search) : array {

        $pdo = SQLAccessor::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM station WHERE nom LIKE :search");
        $stmt->execute(['search' => '%' . $search . '%']);

        return $this->performSQL($stmt, list: true);
    }

    /**
     * Retourne les coordonnées d'une station en MapViewPoint
     * @param int $id
     * @return MapViewPoint|null
     */
    public function coordinatesOf(int $id) : ?MapViewPoint {
        $pdo = SQLAccessor::getConnection();
        $stmt = $pdo->prepare("SELECT latitude, longitude FROM coordonnees INNER JOIN station ON station.code_commune = coordonnees.code_commune WHERE id_station = :id");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();
        if(!$data)
            return null;

        return new MapViewPoint($data['latitude'], $data['longitude']);
    }

    public function nameOf(int $id) : string {
        $pdo = SQLAccessor::getConnection();
        $stmt = $pdo->prepare("SELECT nom_commune AS nom FROM commune INNER JOIN station ON station.code_commune = commune.code_commune WHERE id_station = :id");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();
        if(!$data)
            return "";

        return $data['nom'];
    }

}