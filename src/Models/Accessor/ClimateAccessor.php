<?php

namespace Sae\Models\Accessor;

use DateTime;
use Sae\Models\DataObject\Measure;
use Sae\Models\DataObject\MeasureType;
use Sae\Models\Map\MapViewPoint;
use Sae\Utils\MeasureUtil;

/**
 * Accesseur pour les données climatiques
 */
class ClimateAccessor {

    private static string $baseUrl = "https://climate-api.open-meteo.com/v1/";

    //https://climate-api.open-meteo.com/v1/climate?latitude=52.52&longitude=13.41&start_date=2010-01-01&end_date=2025-01-17&timezone=Europe%2FBerlin&daily=temperature_2m_max

    /**
     * Récupère les températures d'un point sur une période donnée
     * @param MapViewPoint $point Point
     * @param DateTime $from Date de début
     * @param DateTime $to Date de fin
     * @return Measure[] Mesures
     */
    public static function fetchTemperatures(MapViewPoint $point, DateTime $from, DateTime $to) {
        $url = self::$baseUrl . "climate?latitude=" . $point->getLatitude() . "&longitude=" . $point->getLongitude() . "&start_date=" . $from->format('Y-m-d') . "&end_date=" . $to->format('Y-m-d') . "&timezone=Europe/Berlin&daily=temperature_2m_max";
        #$url = rawurlencode($url);

        $jsonObject = json_decode(file_get_contents($url), true);

        $daily = $jsonObject["daily"];
        $dates = $daily["time"];
        $temperatures = $daily["temperature_2m_max"];
        $amount = min(count($dates), count($temperatures));

        $measures = [];
        for ($i = 0; $i < $amount; $i++) {

            $date = new DateTime($dates[$i]);
            $temperature = $temperatures[$i];

            $measure = new Measure(-1, $date, MeasureType::$Temperature, $temperature, $temperature);
            $measure->setId(MeasureUtil::generateId($measure));
            $measures[] = $measure;
        }

        return $measures;
    }

}