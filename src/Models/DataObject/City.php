<?php

namespace Sae\Models\DataObject;

/**
 * Classe représentant une ville
 */
class City extends CodeNameDataObject {

    public function __construct($code, $name) {
        parent::__construct('city', $code, $name);
    }

    public function toArray(): array {
        return [
            'code_commune' => $this->code,
            'nom_commune' => $this->name
        ];
    }

}