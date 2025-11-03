<?php

namespace Sae\Models\DataObject;

class GraphType {
    private string $code;
    private string $name;

    public static array $types = [
        'line' => 'Ligne',
        'histogram' => 'Histogramme',
        'boxplot' => 'Boîte à moustaches',
        'radar' => 'Radar',
        'bar' => 'Barre'
    ];

    public static function getTypes(): array {
        return self::$types;
    }

    public static function isValid(string $type): bool {
        return array_key_exists($type, self::$types);
    }
}
