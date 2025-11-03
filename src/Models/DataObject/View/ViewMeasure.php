<?php

namespace Sae\Models\DataObject\View;

use DateTime;
use Sae\Models\DataObject\Measure;
use Sae\Models\DataObject\MeasureType;
use Sae\Models\Repository\StationRepository;

class ViewMeasure extends Measure {

    private DateTime $addedAt;

    public function __construct(int $station, DateTime $date, MeasureType $type, float $value, DateTime $addedAt) {
        parent::__construct($station, $date, $type, $value, $value);
        $this->addedAt = $addedAt;
    }

    public function getAddedAt(): DateTime {
        return $this->addedAt;
    }

    /** Retourne la date d'ajout au format Y-m-d H:i:s
     * @return string
     */
    public function getFormattedAddedAt() : string {
        return $this->addedAt->format("Y-m-d H:i:s");
    }

    /** Retourne le nom de la station
     * @return string
     */
    public function getStationName() : string {
        $repository = new StationRepository();
        $station = $repository->select($this->station);
        if($station === null)
            $station = $repository->selectByCode($this->station);
        return $station->getName();
    }

    public function toArray(): array {
        $super = parent::toArray();
        $super["date_ajout"] = $this->addedAt->format("Y-m-d H:i:s");
        return $super;
    }

}