<?php

namespace Sae\Utils;

use DateTime;
use Exception;
use Sae\Models\Accessor\ClimateAccessor;
use Sae\Models\Accessor\SynopAccessor;
use Sae\Models\DataObject\Measure;
use Sae\Models\DataObject\MeasureType;
use Sae\Models\DataObject\Region;
use Sae\Models\Repository\MeasureRepository;
use Sae\Models\Repository\RegionRepository;
use Sae\Models\Repository\StationRepository;

class MeasureUtil {

    private function __construct() {}

    /**
     * Compresse un tableau de mesures en un nombre de points donné
     * @param Measure[] $measures Les mesures
     * @param int $points Le nombre de points
     * @return Measure[] Les mesures compressées
     */
    public static function compressMeasures(array $measures, int $points) : array {

        if(count($measures) <= $points)
            return $measures;

        // 1. Trier les mesures par ordre croissant
        $measures = self::sortByDate($measures);

        // 2. Vérifier si le nombre de points est supérieur au nombre de mesures
        if ($points >= count($measures)) {
            return $measures; // Si trop de points, retourner simplement toutes les mesures
        }

        // 3. Calculer les indices des points répartis de manière équilibrée
        $result = [];
        $totalMeasures = count($measures);

        // On ajoute toujours la première mesure
        $result[] = $measures[0];

        // Si il y a plus d'un point, on sélectionne les autres points de manière espacée
        $step = ($totalMeasures - 1) / ($points - 1); // Calcul du pas entre les points
        for ($i = 1; $i < $points - 1; $i++) {
            $index = round($i * $step);
            $result[] = $measures[$index];
        }

        // On ajoute toujours la dernière mesure
        $result[] = $measures[$totalMeasures - 1];

        return $result;
    }

    /**
     * Génère un identifiant unique pour une mesure
     * @param Measure $measure La mesure
     * @return string L'identifiant unique
     */
    public static function generateId(Measure $measure) : string {
        return $measure->getStation() . "_" . $measure->getType()->getCode() . "_" . $measure->getDate()->format('d-m-Y-H-i');
    }

    /**
     * Filtre les mesures par type
     * @param Measure[] $measures Les mesures
     * @param MeasureType $type Le type de mesure
     * @return Measure[] Les mesures filtrées
     */
    public static function filter(array $measures, MeasureType $type) : array {
        $filtered = [];
        foreach($measures as $measure)
            if(($measure->getType() == $type) || ($measure->getType() == $type->getCode()))
                $filtered[] = $measure;
        return $filtered;
    }

    /**
     * Trie les mesures par date
     * @param Measure[] $measures Les mesures
     * @return Measure[] Les mesures triées
     */
    public static function sortByDate(array $measures) : array {
        usort($measures, function(Measure $a, Measure $b) {
            return $a->getDate() <=> $b->getDate();
        });
        return $measures;
    }

    /**
     * Parse une date à partir d'une chaîne de caractères
     * @param string|DateTime|null $raw La chaîne de caractères
     * @param Measure|DateTime $firstDate La première mesure
     * @return DateTime La date
     */
    public static function parseFromDate(string|DateTime|null $raw, Measure|DateTime $firstDate = null) : DateTime {

        if(is_string($raw) || $raw == null) {
            try {
                if($raw == null)
                    throw new Exception();
                $date = new DateTime($raw);
            } catch (Exception) {
                $date = new DateTime();
                $date->modify("-1 month");
            }
        } else
            $date = $raw;

        $firstDate = $firstDate ?? self::getFirstEverMeasure();
        if($firstDate instanceof Measure && $date < $firstDate->getDate())
            $date = $firstDate->getDate();
        else if($firstDate instanceof DateTime && $date < $firstDate)
            $date = $firstDate;

        return $date;
    }

    /**
     * Parse une date à partir d'une chaîne de caractères
     * @param string|DateTime|null $raw La chaîne de caractères
     * @param Measure|DateTime $lastDate La dernière mesure
     * @return DateTime La date
     */
    public static function parseToDate(string|DateTime|null $raw, Measure|DateTime $lastDate = null) : DateTime {

        if(is_string($raw) || $raw == null) {
            try {
                if($raw == null)
                    throw new Exception();
                $date = new DateTime($raw);
            } catch (Exception) {
                $date = new DateTime();
            }
        } else
            $date = $raw;

        $lastDate = $lastDate ?? self::getLastEverMeasure();
        if($lastDate instanceof Measure && $date > $lastDate->getDate())
            $date = $lastDate->getDate();
        else if($lastDate instanceof DateTime && $date > $lastDate)
            $date = $lastDate;

        return $date;
    }

    /**
     * Récupère la première mesure de tous les stations
     * @return Measure|null La première mesure
     */
    public static function getFirstEverMeasure() : ?Measure {

        $measureRepository = new MeasureRepository();
        return $measureRepository->oldestMeasureDate();

        /*$first = null;
        $repository = new StationRepository();
        foreach ($repository->selectAll() as $station) {
            $measure = SynopAccessor::getFirstMeasure($station->getCode());
            if($measure != null && $first == null) {
                $first = $measure;
                break;
            }

            if($measure != null && $measure->getDate() < $first->getDate())
                $first = $measure;
        }*/

        //return $first;
    }

    /**
     * Récupère la dernière mesure de tous les stations
     * @return Measure|null La dernière mesure
     */
    public static function getLastEverMeasure() : ?Measure {

        $last = null;
        $repository = new StationRepository();
        foreach ($repository->selectAll() as $station) {
            $measure = SynopAccessor::getLatestMeasure($station->getCode());
            if($measure != null && $last == null) {
                $last = $measure;
                break;
            }

            if($measure != null && $measure->getDate() > $last->getDate())
                $last = $measure;
        }

        return $last;
    }

    /**
     * Convertit une température de Kelvin à Celsius
     * @param float $kelvin La température en Kelvin
     * @return float La température en Celsius
     */
    public static function kelvinToCelsius(float $kelvin) : float {
        return $kelvin - 273.15;
    }

    /**
     * Convertit une température de Celsius à Kelvin
     * @param float $celsius La température en Celsius
     * @return float La température en Kelvin
     */
    public static function msToKmh(float $ms) : float {
        return $ms * 3.6;
    }

    /**
     * Convertit une vitesse de km/h à m/s
     * @param float $kmh La vitesse en km/h
     * @return float La vitesse en m/s
     */
    public static function kmhToMs(float $kmh) : float {
        return $kmh / 3.6;
    }

    /**
     * Convertit une température de Celsius à Kelvin
     * @param float $celsius La température en Celsius
     * @return float La température en Kelvin
     */
    public static function coloredTemperature($temperature, $minTemp = -1.5, $maxTemp = 1.5) {
        // Palette de couleurs basée sur l'image (valeurs RGB extraites approximativement)
        $colorPalette = [
            [0, 0, 255],    // Bleu (min température)
            [255, 255, 255], // Blanc (température moyenne)
            [255, 0, 0]     // Rouge (max température)
        ];

        // Normaliser la température entre 0 et 1
        $normalizedTemp = ($temperature - $minTemp) / ($maxTemp - $minTemp);
        $normalizedTemp = max(0, min(1, $normalizedTemp)); // Clamper entre 0 et 1

        // Trouver l'index des couleurs environnantes
        $paletteSize = count($colorPalette);
        $index = $normalizedTemp * ($paletteSize - 1);
        $lowIndex = floor($index);
        $highIndex = ceil($index);

        // Interpolation des couleurs
        $lowColor = $colorPalette[$lowIndex];
        $highColor = $colorPalette[$highIndex];
        $weight = $index - $lowIndex;

        $r = (int)(($highColor[0] - $lowColor[0]) * $weight + $lowColor[0]);
        $g = (int)(($highColor[1] - $lowColor[1]) * $weight + $lowColor[1]);
        $b = (int)(($highColor[2] - $lowColor[2]) * $weight + $lowColor[2]);

        // Retourner la couleur au format hexadécimal
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    /**
     * Calcule la température moyenne d'une région
     * @param Region $region La région
     * @param DateTime $from La date de début
     * @param DateTime $to La date de fin
     * @return float La température moyenne
     */
    public static function avgTemperature(Region $region, DateTime $from, DateTime $to) : float {

        $stationRepository = new StationRepository();
        $stations = $stationRepository->selectInRegion($region);
        if(empty($stations))
            return 0;

        foreach ($stations as $station) {

            $point = $stationRepository->coordinatesOf($station->getId());
            if($point == null)
                continue;

            $measures = ClimateAccessor::fetchTemperatures($point, $from, $to);
            if(empty($measures))
                continue;

            $sum = 0;
            foreach ($measures as $measure)
                $sum += $measure->getValue();

            return $sum / count($measures);
        }

        return 0;
    }

    /**
     * Calcule l'anomalie climatique d'une région
     * @param Region $region La région
     * @param DateTime $from La date de début
     * @param DateTime $to La date de fin
     * @return float L'anomalie climatique
     */
    public static function climateAnomaly(Region $region, DateTime $from, DateTime $to) : float {

        $stationRepository = new StationRepository();
        $stations = $stationRepository->selectInRegion($region);
        if (empty($stations)) {
            return 0;
        }

        foreach ($stations as $station) {
            $point = $stationRepository->coordinatesOf($station->getId());
            if ($point == null) {
                continue;
            }

            // Récupérer les températures mesurées entre les dates spécifiées
            $measures = ClimateAccessor::fetchTemperatures($point, $from, $to);
            if (empty($measures)) {
                continue;
            }

            // Calculer la moyenne des températures observées sur la période spécifiée
            $sumObserved = 0;
            foreach ($measures as $measure) {
                $sumObserved += $measure->getValue();
            }
            $observedAvg = $sumObserved / count($measures);

            $historicalFrom = (clone $from)->modify('-30 years');
            $historicalTo = (clone $to)->modify('-30 years');

            $historicalMeasures = ClimateAccessor::fetchTemperatures($point, $historicalFrom, $historicalTo);
            if (empty($historicalMeasures)) {
                continue;
            }

            $sumReference = 0;
            foreach ($historicalMeasures as $measure) {
                $sumReference += $measure->getValue();
            }
            $referenceAvg = $sumReference / count($historicalMeasures);

            return $observedAvg - $referenceAvg;
        }

        return 0;
    }

    /**
     * Convertit une valeur de mesure en fonction de son type
     * @param MeasureType $type Le type de mesure
     * @param float $value La valeur
     * @return float La valeur convertie
     */
    public static function convertValue(MeasureType $type, float $value) : float {
        // Make sur values are initialized in MeasureType
        MeasureType::init();

        if($type === MeasureType::$Temperature)
            $value = MeasureUtil::kelvinToCelsius($value);

        return $value;
    }

}