<?php

namespace Sae\Utils;

use DateTime;

class LabelUtil {

    public static array $days = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
    public static array $months = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

    private function __construct() {}

    /**
     * Génère un tableau de dates entre deux dates
     * @param DateTime $from Date de début
     * @param DateTime $to Date de fin
     * @param int $points Nombre de points à générer
     * @return DateTime[] Tableau de dates
     */
    public static function generateDates(DateTime $from, DateTime $to, int $points): array {
        $dates = [];

        $fromTimestamp = $from->getTimestamp();
        $toTimestamp = $to->getTimestamp();
        $totalSeconds = $toTimestamp - $fromTimestamp;

        $step = $totalSeconds / max(1, ($points - 1));

        for ($i = 0; $i < $points; $i++) {
            $timestamp = $fromTimestamp + ($i * $step);
            $dates[] = (new DateTime())->setTimestamp((int)$timestamp);
        }

        return $dates;
    }

    /**
     * Génère un tableau de labels de dates entre deux dates
     * @param DateTime $from Date de début
     * @param DateTime $to Date de fin
     * @param int $points Nombre de points à générer
     * @return string[] Tableau de labels
     */
    public static function generateDateLabels(DateTime $from, DateTime $to, int $points): array {
        $labels = [];

        $fromTimestamp = $from->getTimestamp();
        $toTimestamp = $to->getTimestamp();
        $totalSeconds = $toTimestamp - $fromTimestamp;

        $interval = $to->diff($from);
        if ($interval->y >= 1) {
            $format = "Y-m"; // Plus d'un an : mois et année
        } elseif ($interval->m >= 1) {
            $format = "Y-m-d"; // Plus d'un mois : jours
        } elseif ($interval->d >= 1) {
            $format = "Y-m-d H:i"; // Plus d'un jour : heures et minutes
        } else {
            $format = "Y-m-d H:i:s"; // Moins d'un jour : secondes
        }

        $step = $totalSeconds / ($points - 1);

        for ($i = 0; $i < $points; $i++) {
            $timestamp = $fromTimestamp + ($i * $step);
            $labels[] = (new DateTime())->setTimestamp((int)$timestamp)->format($format);
        }

        return $labels;
    }

}