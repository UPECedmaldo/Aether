<?php

namespace Sae\Models\DataObject;

/**
 * Classe représentant un département
 */
class Department extends CodeNameDataObject {

    public function __construct($code, $name) {
        parent::__construct('department', $code, $name);
    }

    public function toArray(): array {
        return [
            'code_departement' => $this->code,
            'nom_departement' => $this->name
        ];
    }

}