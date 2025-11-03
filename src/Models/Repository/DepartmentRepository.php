<?php

namespace Sae\Models\Repository;

use Sae\Models\DataObject\ADataObject;
use Sae\Models\DataObject\Department;

/**
 * Classe représentant un dépôt de départements
 */
class DepartmentRepository extends CodeNameRepository {

    public function __construct() {
        parent::__construct("departement", "code_departement", ["code_departement", "nom_departement"]);
    }

    protected function createFromArray(array $data): ?ADataObject {
        return new Department($data['code_departement'], $data['nom_departement']);
    }

}