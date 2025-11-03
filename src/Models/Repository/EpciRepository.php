<?php

namespace Sae\Models\Repository;

use Sae\Models\DataObject\ADataObject;
use Sae\Models\DataObject\Epci;

/**
 * Classe représentant un dépôt d'EPCI
 */
class EpciRepository extends CodeNameRepository {

    public function __construct() {
        parent::__construct("epci", "code_epci", ["code_epci", "nom_epci"]);
    }

    protected function createFromArray(array $data): ?ADataObject {
        return new Epci($data['code_epci'], $data['nom_epci']);
    }

}