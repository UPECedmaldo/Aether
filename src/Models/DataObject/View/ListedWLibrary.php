<?php

namespace Sae\Models\DataObject\View;

use Sae\Models\DataObject\WLibrary;

class ListedWLibrary extends WLibrary {

    private string $userName;
    private array $stations, $measures;

    public function __construct(int $id, int $user, string $name, $state, string $creation, string $color, string $userName, array $stations, array $measures) {
        parent::__construct($id, $user, $name, $state, $creation, $color);
        $this->userName = $userName;
        $this->stations = $stations;
        $this->measures = $measures;
    }

    public function getUserName(): string {
        return $this->userName;
    }

    public function getStations(): array {
        return $this->stations;
    }

    /** Retourne le nombre de stations
     * @return int
     */
    public function getStationCount() : int {
        return count($this->stations);
    }

    public function getMeasures(): array {
        return $this->measures;
    }

    /** Retourne le nombre de mesures
     * @return int
     */
    public function getMeasureCount() : int {
        return count($this->measures);
    }

}