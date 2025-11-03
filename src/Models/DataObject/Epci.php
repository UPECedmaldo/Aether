<?php

namespace Sae\Models\DataObject;

/**
 * Classe représentant un EPCI
 */
class Epci extends CodeNameDataObject {

    public function __construct($code, $name) {
        parent::__construct('epci', $code, $name);
    }

    public function toArray(): array {
        return [
            'code_epci' => $this->code,
            'nom_epci' => $this->name
        ];
    }

}