<?php

namespace Sae\Models\Repository;

/**
 * Classe représentant d'objets qui ont un code et un nom
 */
abstract class CodeNameRepository extends ARepository {

    protected function __construct(string $table, string $primaryKey, array $columns) {
        parent::__construct($table, $primaryKey, $columns);
    }


}