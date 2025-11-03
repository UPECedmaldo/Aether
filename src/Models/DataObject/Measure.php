<?php

namespace Sae\Models\DataObject;

use DateTime;
use JsonSerializable;


/**
 * Classe représentant une mesure
 */
class Measure extends ADataObject implements JsonSerializable {

    private string $id;
    protected int $station;
    private DateTime $date;

    private MeasureType $type;
    private float $originalValue;
    private float $value;

    public function __construct(int $station, DateTime $date, MeasureType $type, float $originalValue, float $value) {
        $this->id = "_";
        $this->station = $station;
        $this->date = $date;
        $this->type = $type;
        $this->originalValue = $originalValue;
        $this->value = $value;
    }

    public function toArray(): array {
        return [
            'id_mesure' => $this->id,
            'id_station' => $this->station,
            'type' => $this->type->getCode(),
            'valeur' => $this->value,
            'date_ajout' => $this->getFormattedDate()
        ];
    }

    public function setId(string $id): void  {
        $this->id = $id;
    }

    public function getId(): string {
        return $this->id;
    }

    /** Return the station code
     * @return int
     */
    public function getStation(): int {
        return $this->station;
    }

    public function getType(): MeasureType {
        return $this->type;
    }

    public function getOriginalValue(): float {
        return $this->originalValue;
    }

    public function getValue(): float {
        return $this->value;
    }

    /** Retourne la valeur arrondie à 2 chiffres après la virgule
     * @return float
     */
    public function getRoundedValue() : float {
        return round($this->value, 2);
    }

    /** Retourne la valeur formatée avec l'unité
     * @return string
     */
    public function getFormattedValue(): string {
        return $this->getRoundedValue() . ' ' . $this->type->getUnit();
    }

    public function getDate(): DateTime {
        return $this->date;
    }

    /** Retourne la date formatée
     * @return string
     */
    public function getFormattedDate(): string {
        return $this->date->format('d/m/Y H:i');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'station' => $this->station,
            'date' => $this->getFormattedDate(),
            'type' => $this->type->getCode(),
            'value' => $this->value
        ];
    }

}