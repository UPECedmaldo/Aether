<?php

namespace Sae\Models\Repository;

use Sae\Models\DataObject\ADataObject;
use Sae\Models\DataObject\Region;

/**
 * Classe représentant un dépôt de régions
 */
class RegionRepository extends CodeNameRepository {

    public function __construct() {
        parent::__construct("region", "code_region", ["code_region", "nom_region"]);
    }

    protected function createFromArray(array $data): ?ADataObject {
        return new Region($data['code_region'], $data['nom_region']);
    }

}