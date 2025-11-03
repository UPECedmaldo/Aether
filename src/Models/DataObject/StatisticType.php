<?php

namespace Sae\Models\DataObject;

class StatisticType {
    public static array $types = [
        'raw' => 'Valeurs brutes',
        'mean' => 'Moyenne mobile',
        'std' => 'Écart-type',
        'cumulative' => 'Valeurs cumulées',
        'variation' => 'Variation'
    ];

    public static function getTypes(): array {
        return self::$types;
    }

    public static function isValid(string $type): bool {
        return array_key_exists($type, self::$types);
    }
}
