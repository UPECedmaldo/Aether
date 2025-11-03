<?php

namespace Sae\Models\Repository;

use Sae\Models\DataObject\ADataObject;
use Sae\Models\DataObject\City;

/**
 * Classe représentant un dépôt de villes
 */
class CityRepository extends CodeNameRepository {

    public function __construct() {
        parent::__construct("commune", "code_commune", ["code_commune", "nom_commune"]);
    }

    protected function createFromArray(array $data): ?ADataObject {
        return new City($data['code_commune'], $data['nom_commune']);
    }

}