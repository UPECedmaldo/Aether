<?php

namespace Sae\Models\Map;

use JsonSerializable;

/**
 * Classe représentant une carte interactive
 * ou l'on peut ajouter des marqueurs
 */
class MapView implements JsonSerializable {

    private static string $headers = <<<HTML
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" />
            <style>
                .leaflet-control-attribution {
                    display: none !important;
                }
            </style>
            <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
            <script src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>
            <script>
                const mapViewSettings = [];
            </script>
            HTML;

    private static string $footer = "<script src='/public/assets/scripts/mapview.js'></script>";

    private string $id;
    private MapViewPoint $center;
    private int $zoom = 6;
    private array $markers;

    public function __construct() {
        $this->id = uniqid();
        $this->center = new MapViewPoint(48.7779655456543, 2.3782076835632324);
        $this->markers = [];
    }

    public function draw(bool $inject = false) : void {

        if($inject)
            echo self::$headers;

        echo "<div id='" . $this->id . "' style='height: 100%;'></div>";

        $json = json_encode($this);
        $var = "map_" . $this->id;

        echo "<script>";
        echo "const $var = " . $json . ";";
        echo "\n";
        echo "mapViewSettings.push($var);";
        echo "</script>";

        if($inject)
            echo self::$footer;

    }

    public function getId(): string {
        return $this->id;
    }

    public function getCenter(): MapViewPoint {
        return $this->center;
    }

    public function getZoom(): int {
        return $this->zoom;
    }

    public function setZoom(int $zoom): void {
        $this->zoom = $zoom;
    }

    public function addMarker(MapViewMarker $marker) : void {
        $this->markers[] = $marker;
    }

    public function getMarkers(): array {
        return $this->markers;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'center' => $this->center,
            'zoom' => $this->zoom,
            'markers' => $this->markers
        ];
    }
}