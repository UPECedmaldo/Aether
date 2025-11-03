<?php

namespace Sae\Models\DataObject;

/**
 * Classe abstraite représentant un objet de données
 * qui peut être converti en tableau (pour SQL)
 */
abstract class ADataObject {

    /**
     * Convertit l'objet en tableau
     * @return array
     */
    public abstract function toArray(): array;

}