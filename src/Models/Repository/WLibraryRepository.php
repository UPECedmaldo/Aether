<?php

namespace Sae\Models\Repository;

use DateTime;
use Exception;
use Sae\Models\Accessor\SQLAccessor;
use Sae\Models\DataObject\Measure;
use Sae\Models\DataObject\Station;
use Sae\Models\DataObject\View\ListedWLibrary;
use Sae\Models\DataObject\WLibrary;

/**
 * Classe représentant un dépôt de bibliothèques météo
 */
class WLibraryRepository extends ARepository {

    public function __construct() {
        parent::__construct("meteotheque", "id_meteotheque", ["id_meteotheque", "id_utilisateur", "nom", "etat", "creation", "couleur"]);
    }

    protected function createFromArray(array $data): WLibrary {
        return new WLibrary(
            $data["id_meteotheque"],
            $data["id_utilisateur"],
            $data["nom"],
            $data["etat"],
            $data["creation"],
            $data["couleur"]
        );
    }

    /**
     * Récupère les météothèques d'un utilisateur
     * et si elle est en vue, les convertit en vue
     * @param int $userId Identifiant de l'utilisateur
     * @param bool $view
     * @return array
     */
    public function selectByUser(int $userId, bool $view = false): array {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM meteotheque WHERE id_utilisateur = :id_utilisateur";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_utilisateur', $userId);
        $libraries = $this->performSQL($stmt, true);

        if(!$view)
            return $libraries;

        $viewLibraries = [];
        foreach($libraries as $library)
            $viewLibraries[] = $this->convertToView($library);
        return $viewLibraries;
    }

    /**
     * Récupère les météothèques publiques
     * et si elle est en vue, les convertit en vue
     * @param bool $view
     * @return array
     */
    public function selectPublic(bool $view = false): array {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM meteotheque WHERE etat = 1";
        $stmt = $pdo->prepare($sql);
        $libraries = $this->performSQL($stmt, true);
        if(!$view)
            return $libraries;

        $viewLibraries = [];
        foreach($libraries as $library)
            $viewLibraries[] = $this->convertToView($library);
        return $viewLibraries;
    }

    /**
     * Récupère une météothèque par son nom et un utilisateur
     * @param string $name Nom de la bibliothèque
     * @param int $userId Identifiant de l'utilisateur
     * @return WLibrary|null
     */
    public function selectByName(string $name, int $userId = -1) : ?WLibrary {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM meteotheque WHERE nom = :nom";
        if($userId != -1)
            $sql .= " AND id_utilisateur = :id_utilisateur";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nom', $name);
        if($userId != -1)
            $stmt->bindParam(':id_utilisateur', $userId);
        return $this->performSQL($stmt);
    }

    /**
     * Insère une bibliothèque dans la base de données
     * @param WLibrary $library
     * @return void
     */
    public function insert(WLibrary $library): void {

        $pdo = SQLAccessor::getConnection();
        $sql = "INSERT INTO meteotheque (id_utilisateur, nom, etat, creation, couleur) VALUES (:id_utilisateur, :nom, :etat, :creation, :couleur)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "id_utilisateur" => $library->getUser(),
            "nom" => $library->getName(),
            "etat" => $library->getState(),
            "creation" => $library->getCreation(),
            "couleur" => $library->getColor()
        ]);
    }

    /**
     * Vérifie si un utilisateur est propriétaire d'une bibliothèque
     * @param int $libraryId
     * @param int $userId
     * @return bool
     */
    public function isOwnerOf(int $libraryId, int $userId) : bool {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM meteotheque WHERE id_meteotheque = :id_meteotheque AND id_utilisateur = :id_utilisateur";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id_meteotheque" => $libraryId, "id_utilisateur" => $userId]);
        return $stmt->rowCount() > 0;

    }

    /**
     * Supprime un favoris d'une bibliothèque à partir de son type et de son identifiant
     * @param int $libraryId
     * @param string $type
     * @param int|string $reference
     * @return bool
     */
    public function deleteReference(int $libraryId, string $type, int|string $reference) : bool {

        $pdo = SQLAccessor::getConnection();
        $sql = "DELETE FROM favoris WHERE id_meteotheque = :id_meteotheque AND reference_id = :reference_id AND type = :type";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "id_meteotheque" => $libraryId,
            "reference_id" => $reference,
            "type" => $type
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Ajoute une station aux favoris d'une bibliothèque
     * @param int $libraryId
     * @param int|string $stationCode
     * @return bool
     */
    public function addStation(int $libraryId, int|string $stationCode) : bool {

        $stationCode = str_pad($stationCode, 5, "0", STR_PAD_LEFT);

        $stationRepository = new StationRepository();
        $station = $stationRepository->selectByCode($stationCode);
        if($station == null) {
            echo "Station not found";
            return false;
        }

        if($this->isAlreadyIn($libraryId, "station", $station->getId()))
            return false;

        $pdo = SQLAccessor::getConnection();
        $sql = "INSERT INTO favoris (id_meteotheque, type, reference_id) VALUES (:id_meteotheque, :type, :reference_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "id_meteotheque" => $libraryId,
            "type" => "station",
            "reference_id" => $station->getId()
        ]);

        return true;
    }

    /**
     * Ajoute une mesure aux favoris d'une bibliothèque
     * @param int $libraryId
     * @param string $measureId
     * @param int|string $stationCode
     * @param string $type
     * @param float $value
     * @param string $date
     * @return bool
     */
    public function addMeasure(int $libraryId, string $measureId, int|string $stationCode, string $type, float $value, string $date) : bool {

        $stationCode = str_pad($stationCode, 5, "0", STR_PAD_LEFT);

        $stationRepository = new StationRepository();
        $station = $stationRepository->selectByCode($stationCode);
        if($station == null) {
            echo "Station not found";
            return false;
        }

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM mesure WHERE id_mesure = :id_mesure";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id_mesure" => $measureId]);
        if($stmt->rowCount() == 0) {

            $sql = "INSERT INTO mesure (id_mesure, id_station, type, valeur, date) VALUES (:id_mesure, :id_station, :type, :valeur, :date)";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                "id_mesure" => $measureId,
                "id_station" => $station->getId(),
                "type" => $type,
                "valeur" => $value,
                # my date is 24/03/2022 22:00
                "date" => DateTime::createFromFormat("d/m/Y H:i", $date)->format("Y-m-d H:i"),
            ]);
        }

        if($this->isAlreadyIn($libraryId, "mesure", $measureId))
            return false;

        $sql = "INSERT INTO favoris (id_meteotheque, type, reference_id) VALUES (:id_meteotheque, :type, :reference_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "id_meteotheque" => $libraryId,
            "type" => "mesure",
            "reference_id" => $measureId
        ]);

        return true;

    }

    /**
     * Vérifie si un élément est déjà dans une bibliothèque
     * @param int $libraryId
     * @param string $type
     * @param string $referenceId
     * @return bool
     */
    public function isAlreadyIn(int $libraryId, string $type, string $referenceId) : bool {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM favoris WHERE id_meteotheque = :id_meteotheque AND type = :type AND reference_id = :reference_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "id_meteotheque" => $libraryId,
            "type" => $type,
            "reference_id" => $referenceId
        ]);
        return $stmt->rowCount() > 0;

    }

    /**
     * Récupère la date de la première mesure d'une bibliothèque
     * @param WLibrary $library
     * @return DateTime
     */
    public function getFirstDateOf(WLibrary $library) : DateTime {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT MIN(date) as date FROM mesure WHERE id_mesure IN (SELECT reference_id FROM favoris WHERE id_meteotheque = :id_meteotheque AND type = 'mesure')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id_meteotheque" => $library->getId()]);
        $result = $stmt->fetch();
        if($result == null)
            return new DateTime();

        return new DateTime($result["date"]);
    }

    /**
     * Récupère la date de la dernière mesure d'une bibliothèque
     * @param WLibrary $library
     * @return DateTime
     */
    public function getLastDateOf(WLibrary $library) : DateTime {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT MAX(date) as date FROM mesure WHERE id_mesure IN (SELECT reference_id FROM favoris WHERE id_meteotheque = :id_meteotheque AND type = 'mesure')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id_meteotheque" => $library->getId()]);
        $result = $stmt->fetch();
        if($result == null)
            return new DateTime();

        return new DateTime($result["date"]);
    }

    /**
     * Récupère les mesures d'une bibliothèque
     * @param WLibrary $library
     * @return Measure[]
     */
    public function getMeasures(WLibrary $library) : array {
        return $this->getElements($library, "mesure");
    }

    /**
     * Récupère les stations d'une bibliothèque
     * @param WLibrary $library
     * @return Station[]
     */
    public function getStations(WLibrary $library) : array {
        return $this->getElements($library, "station");
    }

    /**
     * Convertit une bibliothèque en vue (nom de l'utilisateur, stations et mesures)
     * @param WLibrary $library
     * @return ListedWLibrary
     */
    public function convertToView(WLibrary $library) : ListedWLibrary {

        $userRepository = new UserRepository();
        $user = $userRepository->select($library->getUser());

        $userName = $user != null ? $user->getPseudo() : "Inconnu";
        $stations = $this->getElements($library, "station");
        $measures = $this->getElements($library, "mesure");

        return new ListedWLibrary(
            $library->getId(),
            $library->getUser(),
            $library->getName(),
            $library->getState(),
            $library->getCreation(),
            $library->getColor(),
            $userName,
            $stations,
            $measures
        );
    }

    /**
     * Récupère les éléments d'une bibliothèque selon un type et les convertit en objets
     * @param WLibrary $library
     * @param string $type
     * @return array
     */
    private function getElements(WLibrary $library, string $type) : array {

        switch ($type) {
            case "station":
                $repository = new StationRepository();
                break;
            case "mesure":
                $repository = new MeasureRepository();
                break;
            default:
                throw new Exception("Type d'élément inconnu");
        }

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT type, reference_id FROM favoris WHERE id_meteotheque = :id_meteotheque AND type = :type";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id_meteotheque" => $library->getId(), "type" => $type]);

        $results = $stmt->fetchAll();
        $elements = [];

        foreach($results as $result) {

            $referenceId = $result["reference_id"];
            $element = $repository->select($referenceId);
            if($element != null)
                $elements[] = $element;

        }

        return $elements;
    }

    /**
     * Récupère les bibliothèques visibles d'un utilisateur cible pour un utilisateur
     * - Bibliothèques publiques (état = 1) du targetId
     * - Bibliothèques privées si userId est le même que targetId
     * - Bibliothèques partagées entre amis (état = 2) si userId est ami avec targetId
     *
     * @param int|null $userId ID de l'utilisateur consultant le profil
     * @param int $targetId ID de l'utilisateur dont on veut voir les météothèques
     * @return array
     */
    public function getVisibleFor(int|null $userId, int $targetId) : array {
        $pdo = SQLAccessor::getConnection();

        // Si l'utilisateur n'est pas connecté, il ne voit que les météothèques publiques
        if($userId == null) {
            $sql = "SELECT * FROM meteotheque WHERE id_utilisateur = :targetId AND etat = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['targetId' => $targetId]);
        }
        // Si l'utilisateur consulte son propre profil, il voit toutes ses météothèques
        else if($userId == $targetId) {
            $sql = "SELECT * FROM meteotheque WHERE id_utilisateur = :targetId";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['targetId' => $targetId]);
        }
        // Sinon, il voit les météothèques publiques et celles partagées entre amis s'il est ami
        else {
            $sql = "SELECT m.* FROM meteotheque m
                LEFT JOIN follower f1 ON f1.id_utilisateur_1 = :userId AND f1.id_utilisateur_2 = :targetId
                LEFT JOIN follower f2 ON f2.id_utilisateur_1 = :targetId AND f2.id_utilisateur_2 = :userId
                WHERE m.id_utilisateur = :targetId 
                AND (m.etat = 1 OR (m.etat = 2 AND (f1.id_utilisateur_1 IS NOT NULL OR f2.id_utilisateur_2 IS NOT NULL)))";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'userId' => $userId,
                'targetId' => $targetId
            ]);
        }

        $libraries = [];
        while($row = $stmt->fetch()) {
            $libraries[] = new WLibrary(
                $row['id_meteotheque'],
                $row['id_utilisateur'],
                $row['nom'],
                $row['etat'],
                $row['creation'],
                $row['couleur']
            );
        }

        return $libraries;
    }

}