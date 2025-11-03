<?php

namespace Sae\Models\DataObject;

/**
 * Classe abstraite représentant un objet de données
 * qui possède un code et un nom
 * (commune, département, région, ...)
 */
abstract class CodeNameDataObject extends ADataObject {

    protected string $key;

    protected int $code;
    protected string $name;

    public function __construct(string $key, int $id, string $name) {
        $this->key = $key;
        $this->code = $id;
        $this->name = $name;
    }

    public function getCode(): int {
        return $this->code;
    }

    public function getName(): string {
        return $this->name;
    }

}