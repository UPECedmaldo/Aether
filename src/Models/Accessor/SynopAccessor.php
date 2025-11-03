<?php

namespace Sae\Models\Accessor;

use DateTime;
use DateTimeZone;
use Exception;
use Sae\Models\DataObject\Measure;
use Sae\Models\DataObject\MeasureType;
use Sae\Utils\LabelUtil;
use Sae\Utils\MeasureUtil;

/**
 * Accesseur pour les données synop
 */
class SynopAccessor {

    // Date stuff

    private static string $baseUrl = "https://data.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm@public/records";

    // "Refined" stuff

    private static string $beginParam = "%3A\"";
    private static string $endParam = "\"";
    private static string $separatorParam = "%2F";

    /**
     * Transforme un paramètre pour qu'il devienne un filtre
     * @param string|array $values
     * @return string
     */
    public static function formatRefinedParams(string|array $values) : string {

        if(is_string($values))
            return self::$beginParam . $values . self::$endParam;

        $param = self::$beginParam;
        $param .= implode(self::$separatorParam, $values);
        $param .= self::$endParam;

        return $param;
    }

    /**
     * Crée un filtre pour un paramètre
     * @param string $key
     * @param string|array $values
     * @return string
     */
    public static function refineParams(string $key, string|array $values) : string {
        return "refine=" . $key . self::formatRefinedParams($values);
    }

    /**
     * Assemble les paramètres
     * @param string|array $params
     * @return string
     */
    public static function assembleParams(string|array $params) : string {
        if(is_string($params))
            return $params;
        return implode("&", $params);
    }

    /**
     * Crée une URL avec des paramètres
     * @param string $url
     * @param string|array $params
     * @return string
     */
    private static function parameteredUrl(string $url, string|array $params) : string {
        return $url . "?" . self::assembleParams($params);
    }

    /**
     * Crée une condition WHERE pour des dates
     * @param DateTime[] $dates
     * @param bool $year
     * @param bool $month
     * @param bool $day
     * @param bool $hour
     * @return string
     */
    public static function whereDates(array $dates, bool $year = true, bool $month = true, bool $day = true, bool $hour = false) : string {
        $whereConditions = [];

        $years = [];
        $months = [];
        $days = [];
        $hours = [];
        foreach ($dates as $date) {

            $conditions = [];
            if($year && !in_array($date->format('Y'), $years)) {
                $conditions[] = "YEAR(date) = '" . $date->format('Y') . "'";
                $years[] = $date->format('Y');
            }
            if($month && !in_array($date->format('m'), $months)) {
                $conditions[] = "MONTH(date) = '" . $date->format('m') . "'";
                $months[] = $date->format('m');
            }
            if($day && !in_array($date->format('d'), $days)) {
                $conditions[] = "DAY(date) = '" . $date->format('d') . "'";
                $days[] = $date->format('d');
            }
            if($hour && !in_array($date->format('H'), $hours)) {
                $conditions[] = "HOUR(date) = '" . $date->format('H') . "'";
                $hours[] = $date->format('H');
            }
            if (!empty($conditions)) {
                $whereConditions[] = implode(" AND ", $conditions);
            }
        }
        return implode(" OR ", $whereConditions);
    }

    // Usefull

    /**
     * Récupère les mesures d'une station entre deux dates
     * @param int|string $stationCode Code de la station
     * @param DateTime $from Date de début
     * @param DateTime $to Date de fin
     * @param int $points Nombre de points
     * @return Measure[] Mesures
     */
    public static function getMeasuresBetweenDate(int|string $stationCode, DateTime $from, DateTime $to, int $points = 10) : array {
        $dates = LabelUtil::generateDates($from, $to, $points);
        return self::getMeasuresInDates($stationCode, $dates);
    }


    /**
     * Récupère les mesures d'une station qui appartiennent à une liste de dates
     * @param int|string $stationCode Code de la station
     * @param DateTime[] $dates Dates
     * @return Measure[] Mesures
     */
    public static function getMeasuresInDates(int|string $stationCode, array $dates/*, bool $year = true, bool $month = true, bool $day = true, bool $hour = false*/) : array {

        $where = [];
        foreach ($dates as $date) {
            $day = $date->format('d');
            $month = $date->format('m');
            $year = $date->format('Y');

            $where[] = sprintf(
                "(DAY(date) = %s AND MONTH(date) = %s AND YEAR(date) = %s)",
                $day,
                $month,
                $year
            );
        }

        return self::getMeasuresWithWhere($stationCode, implode(" OR ", $where));
    }

    /**
     * Récupère les mesures d'une station avec une condition WHERE
     * @param int|string $stationCode Code de la station
     * @param string $where Condition WHERE
     * @return Measure[] Mesures
     */
    public static function getMeasuresWithWhere(int|string $stationCode, string $where) : array {

        $stationCode = str_pad($stationCode, 5, "0", STR_PAD_LEFT);

        //echo $where;
        $params = [
            self::refineParams("numer_sta", $stationCode),
            //"where=" . rawurlencode(sprintf("date >= '%s' AND date <= '%s'", $from->format('Y-m-d\TH:i:s'), $to->format('Y-m-d\TH:i:s'))),
            "where=" . rawurlencode($where),
            "order_by=" . rawurlencode("date DESC"),
        ];

        return self::fetchMeasures($params, $stationCode, [], ["limit=100"], 0, true);
    }

    /**
     * Récupère la dernière mesure d'une station
     * @param int|string $stationCode Code de la station
     * @return Measure|null Mesure
     */
    public static function getLatestMeasure(int|string $stationCode) : ?Measure {

        $stationCode = str_pad($stationCode, 5, "0", STR_PAD_LEFT);

        $params = [
            self::refineParams("numer_sta", $stationCode),
            "limit=1",
            "order_by=" . rawurlencode("date DESC")
        ];

        $measures = self::fetchMeasures($params, $stationCode);
        if(!$measures || count($measures) == 0)
            return null;

        return $measures[0];
    }

    /**
     * Récupère la première mesure d'une station
     * @param int|string $stationCode Code de la station
     * @return Measure|null Mesure
     */
    public static function getFirstMeasure(int|string $stationCode) : ?Measure {

        $stationCode = str_pad($stationCode, 5, "0", STR_PAD_LEFT);

        $params = [
            self::refineParams("numer_sta", $stationCode),
            "limit=1",
            "order_by=" . rawurlencode("date ASC")
        ];

        $measures = self::fetchMeasures($params, $stationCode);
        if(!$measures || count($measures) == 0)
            return null;

        return $measures[0];
    }

    /**
     * Récupère les mesures d'une station
     * @param array $originalParams Paramètres
     * @param int|string $stationCode Code de la station
     * @param Measure[] $measures Mesures
     * @param array $offsetParams Paramètres d'offset
     * @param int $rows Nombre de lignes
     * @param bool $paginate Pagination
     * @return Measure[] Mesures
     */
    public static function fetchMeasures(array $originalParams, int|string $stationCode, array $measures = [], array $offsetParams = [], int $rows = 0, bool $paginate = false) : array {
        try {

            $merged = array_merge($originalParams, $offsetParams);
            $url = self::parameteredUrl(self::$baseUrl, $merged);

            $curl_session = curl_init();
            curl_setopt($curl_session, CURLOPT_URL, $url);
            curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($curl_session);

            # check status code
            $status = curl_getinfo($curl_session, CURLINFO_HTTP_CODE);
            curl_close($curl_session);

            if ($status != 200 && $status != 0) {
                echo "Erreur : " . $status . "<br>";
                echo "URL : " . $url . "<br>";
                echo "Response : " . $response . "<br>";
                return $measures;
            }

            //echo $response;

            $data = json_decode($response, true);
            $total = $data['total_count'] ?? 0;

            if ($total == 0)
                return $measures;

            $results = $data['results'] ?? null;
            if (!$results)
                return $measures;

            $rows = count($results) + $rows;

            foreach ($results as $row) {

                $rawDate = $row['date'] ?? null;
                if (!$rawDate)
                    continue; // Ignorer les enregistrements sans date

                $timeZone = new DateTimeZone('UTC');
                $date = new DateTime($rawDate, $timeZone);
                $date->setTimezone(new DateTimeZone('Europe/Paris'));

                foreach (MeasureType::values() as $measureType) {
                    $value = $row[$measureType->getCode()] ?? null;
                    if (!$value)
                        continue; // Ignorer si la valeur de la mesure est absente

                    $originalValue = $value;
                    $value = MeasureUtil::convertValue($measureType, $value);
                    $measure = new Measure($stationCode, $date, $measureType, $originalValue, $value);
                    $measure->setId(MeasureUtil::generateId($measure));

                    $measures[] = $measure;
                }

            }

            if ($rows < $total && $paginate) {

                $offset = $rows;
                $limit = 100;

                $offsetParams = [
                    "offset=" . $offset,
                    "limit=" . $limit,
                ];

                return self::fetchMeasures($originalParams, $stationCode, $measures, $offsetParams, $rows, $paginate);
            }

            return $measures;

        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage() . PHP_EOL;
            return $measures;
        }

    }


}