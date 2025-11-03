<?php

namespace Sae\Models\DataObject;

use JsonSerializable;


/**
 * Classe représentant un type de mesure
 */
class MeasureType implements JsonSerializable {

    public static MeasureType $Temperature;
    public static MeasureType $Precipitation;
    public static MeasureType $SeaLevelPressure;
    public static MeasureType $WindSpeed;
    public static MeasureType $Visibility;
    public static MeasureType $CloudCover;
    public static MeasureType $StationPressure;
    public static MeasureType $GeopotentialHeight;
    public static MeasureType $SnowHeight;
    private static array $values;

    private string $code;
    private string $name;
    private string $description;

    private string $unit;
    private string $color;

    private function __construct(string $code, string $name, string $description, string $unit, string $color) {
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
        $this->unit = $unit;
        $this->color = $color;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getUnit(): string {
        return $this->unit;
    }

    public function getColor(): string {
        return $this->color;
    }

    /**
     * Retourne la liste des valeurs possibles
     * @return MeasureType[]
     */
    public static function values(): array {
        if(!isset(self::$values))
            self::init();
        return self::$values;
    }


    /**
     * Retourne le type de mesure correspondant au code
     * @param string $code Code du type de mesure
     * @return MeasureType|null
     */
    public static function getByCode(string $code) : ?MeasureType {
        if(!isset(self::$values))
            self::init();
        foreach(self::$values as $value)
            if($value->getCode() === $code)
                return $value;
        return null;
    }


    /**
     * Retourne le type de mesure correspondant au nom
     * @param string $name Nom du type de mesure
     * @return MeasureType|null
     */
    public static function getByName(string $name): ?MeasureType {
        if(!isset(self::$values))
            self::init();
        foreach(self::$values as $value)
            if($value->getName() === $name)
                return $value;
        return null;
    }

    /**
     * Initialise les valeurs possibles
     * @return void
     */
    public static function init() : void {

        if(isset(self::$values))
            return;

        self::$Temperature = new MeasureType("t", "Temperature", "Temperature de l'air", "°C", "#32a4a8");
        self::$Precipitation = new MeasureType("rr3", "Precipitation", "Precipitation sur 3 heures", "mm", "#ed0ec4");
        self::$SeaLevelPressure = new MeasureType("pmer", "Pression au niveau de la mer", "Pression au niveau de la mer", "Pa", "#3232a8");
        self::$WindSpeed = new MeasureType("ff", "Vitesse du vent", "Vitesse du vent", "m/s", "#e8bce0");
        self::$Visibility = new MeasureType("vv", "Visibilite horizontale", "Visibilite horizontale", "m", "#e4eb81");
        self::$CloudCover = new MeasureType("n", "Nebulosite totale", "Nebulosite totale", "%", "#7b37c4");
        self::$StationPressure = new MeasureType("pres", "Pression a la station", "Pression a la station", "Pa", "#c6a4eb");
        self::$GeopotentialHeight = new MeasureType("gp", "Hauteur du geopotentiel", "Hauteur du geopotentiel", "m", "#14f736");
        self::$SnowHeight = new MeasureType("sss", "Hauteur de la neige", "Hauteur de la neige", "cm", "#f7f7f7");

        self::$values = [
            self::$Temperature,
            self::$Precipitation,
            self::$SeaLevelPressure,
            self::$WindSpeed,
            self::$Visibility,
            self::$CloudCover,
            self::$StationPressure,
            self::$GeopotentialHeight,
            self::$SnowHeight
        ];
    }

    public function jsonSerialize(): array {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'color' => $this->color
        ];
    }
}

// Codes de l'api
/*
 * private $mesures = [
        'temperature' => 't',          // Température
        'precipitation' => 'rr3',      // Précipitations sur 3 heures
        'pression_mer' => 'pmer',      // Pression au niveau de la mer
        'vitesse_vent' => 'ff',        // Vitesse du vent
        'visibilite' => 'vv',         // Visibilité horizontale
        'nebulosite' => 'n',          // Nébulosité totale
        'pression_station' => 'pres',  // Pression à la station
        'geopotentiel' => 'gp',        // Hauteur du géopotentiel
        'hauteur_neige' => 'sss'       // Hauteur de la neige
    ];
 * enum MeasureType: string {
    case Temperature = "t";
    case Precipitation = "rr3";
    case SeaLevelPressure = "pmer";
    case WindSpeed = "ff";
    case Visibility = "vv";
    case CloudCover = "n";
    case StationPressure = "pres";
    case GeopotentialHeight = "gp";
    case SnowHeight = "sss";
}*/