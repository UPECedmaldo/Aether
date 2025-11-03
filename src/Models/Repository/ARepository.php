<?php

namespace Sae\Models\Repository;

use PDO;
use PDOStatement;
use Sae\Models\Accessor\SQLAccessor;
use Sae\Models\DataObject\ADataObject;


/**
 * Classe abstraite représentant un repository
 * qui gère les accès à une table SQL
 */
abstract class ARepository {

    protected string $table;
    protected string $primaryKey;
    protected array $columns;

    protected function __construct(string $table, string $primaryKey, array $columns) {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->columns = $columns;
    }

    /**
     * Crée un objet de données à partir d'un tableau
     * @param array $data
     * @return ADataObject|null
     */
    protected abstract function createFromArray(array $data): ?ADataObject;

    /**
     * Exécute une requête SQL
     * @param PDOStatement $statement
     * @param bool $list
     * @return ADataObject|array|null
     */
    protected function performSQL($statement, bool $list = false): ADataObject|array|null {
        $statement->execute();

        if($list) {
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            $dataObjects = [];
            foreach($results as $result)
                $dataObjects[] = $this->createFromArray($result);
            return $dataObjects;
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if(!$result)
            return null;

        return $this->createFromArray($result);
    }

    /**
     * Sélectionne un objet de données
     * @param mixed $primaryKey
     * @return ADataObject|null
     */
    public function select($primaryKey): ?ADataObject {

        $pdo = SQLAccessor::getConnection();

        $sql = "SELECT * FROM $this->table WHERE $this->primaryKey = :primaryKey";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':primaryKey', $primaryKey);
        return $this->performSQL($stmt);
    }

    /**
     * Sélectionne tous les objets de données
     * @return array
     */
    public function selectAll(): array {
        $pdo = SQLAccessor::getConnection();

        $sql = "SELECT * FROM $this->table";
        $stmt = $pdo->prepare($sql);
        return $this->performSQL($stmt, true);
    }

    /**
     * Met à jour certains champs d'un objet de données
     * @param $primaryKey
     * @param array $data
     * @return void
     */
    public function update($primaryKey, array $data) : void {
        $pdo = SQLAccessor::getConnection();
    
        $sql = "UPDATE $this->table SET ";
        foreach($data as $key => $value) {
            $sql .= "$key = :$key, ";
        }
        $sql = substr($sql, 0, -2);
        $sql .= " WHERE $this->primaryKey = :primaryKey";
    
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':primaryKey', $primaryKey, PDO::PARAM_INT);
    
        foreach($data as $key => $value) {
            if (is_resource($value)) {
                $stmt->bindValue(":$key", $value, PDO::PARAM_LOB);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
    
        $stmt->execute();
    }
    

    public function delete($primaryKey): void {

        $pdo = SQLAccessor::getConnection();

        $sql = "DELETE FROM $this->table WHERE $this->primaryKey = :primaryKey";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':primaryKey', $primaryKey);
        $stmt->execute();

    }

}