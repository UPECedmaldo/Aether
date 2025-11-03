<?php

namespace Sae\Models\DataObject;


/**
 * Classe représentant une région
 */
class Region extends CodeNameDataObject {

    public function __construct($code, $name) {
        parent::__construct('region', $code, $name);
    }

    public function toArray(): array {
        return [
            'code_region' => $this->code,
            'nom_region' => $this->name
        ];
    }

}