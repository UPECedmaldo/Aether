<?php

namespace Sae\Models\Graph;

use JsonSerializable;
use ReflectionClass;

/**
 * Classe représentant un jeu de données pour un graphique
 */
abstract class AGraphDataSet implements JsonSerializable {

    private string $label;
    private array $data;

    public function __construct(string $labels, array $data) {
        $this->label = $labels;
        $this->data = $data;
    }

    public function jsonSerialize(): mixed {
        return self::get_all_vars($this);
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function getData(): array {
        return $this->data;
    }

    private static function get_all_vars($object) {
        $reflection = new ReflectionClass($object);
        $props = [];
        do {
            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);
                $props[$property->getName()] = $property->getValue($object);
            }
            $reflection = $reflection->getParentClass();
        } while ($reflection);
        return $props;
    }

}