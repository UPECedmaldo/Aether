<?php

namespace Sae\Models\France;

use JsonSerializable;

/**
 * Classe représentant une carte de France
 */
class FranceMap implements JsonSerializable {

    private static string $headers = <<<HTML
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="/public/assets/scripts/france.js"></script>
        <link rel="stylesheet" href="/public/assets/styles/index/france.css">
        <script>
            const franceMaps = [];
        </script>
        HTML;

    private string $id;
    private array $parts = [];

    public function __construct() {
        $this->id = uniqid();
    }

    public function draw(bool $inject = false) : void {
        if($inject)
            echo self::$headers;

        $json = json_encode($this);
        $var = "franceMap_" . $this->id;
        echo "<canvas id='$this->id' width='800' height='600'></canvas>";
        echo "<script>";
        echo "const $var = " . $json . ";";
        echo "franceMaps.push($var);";
        echo "</script>";
    }

    public function addPart(FranceMapPart $part) : void {
        $this->parts[$part->getRegion()] = $part;
    }

    public function getParts() : array {
        return $this->parts;
    }


    public function jsonSerialize(): array {
        return [
            "id" => $this->id,
            "parts" => $this->parts
        ];
    }

}