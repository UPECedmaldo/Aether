<?php

namespace Sae\Controllers;

use DateTime;
use Sae\Models\Accessor\SynopAccessor;
use Sae\Models\DataObject\MeasureType;
use Sae\Models\Repository\StationRepository;

/**
 * Contrôleur pour le jeu Higher or Lower
 */
class GameController extends AController {

    /**
     * Constructeur du contrôleur
     */
    public function __construct() {
        parent::__construct("game");
    }

    /**
     * Méthode principale du contrôleur
     * @param array $path Chemin de l'URL
     * @return bool Indique si le chemin a été traité
     */
    public function index(array $path): bool {
        if (!isset($path[1]) || $path[1] === '') {
            $this->showGame();
            return true;
        } else if ($path[1] === 'getStationData') {
            $this->getStationData();
            return true;
        }
        return false;
    }

    /**
     * Affiche la page du jeu
     */
    private function showGame(): void {
        $this->loadView('higher-lower', [
            'title' => 'Higher or Lower - Le jeu météo',
            'styles' => ['game/higher-lower.css']
        ]);
    }

    /**
     * Récupère les données d'une station aléatoire
     */
    private function getStationData(): void {
        header('Content-Type: application/json');

        $repository = new StationRepository();
        $stations = $repository->selectAll();

        // Filtrer les stations sans données
        $validStations = [];
        foreach ($stations as $station) {
            $point = $repository->coordinatesOf($station->getId());
            if ($point !== null) {
                $validStations[] = $station;
            }
        }

        if (empty($validStations)) {
            echo json_encode(['error' => 'Aucune station disponible']);
            return;
        }

        // Sélectionner une station aléatoire
        $randomStation = $validStations[array_rand($validStations)];

        // Récupérer les dernières mesures
        $today = new DateTime();
        $yesterday = (clone $today)->modify('-1 day');

        $measures = SynopAccessor::getMeasuresBetweenDate(
            $randomStation->getCode(),
            $yesterday,
            $today,
            1
        );

        // Filtrer pour obtenir la température
        $temperature = null;
        foreach ($measures as $measure) {
            if ($measure->getType() === MeasureType::$Temperature) {
                $temperature = $measure->getValue();
                break;
            }
        }

        // Préparer la réponse
        $response = [
            'station' => [
                'id' => $randomStation->getId(),
                'name' => $randomStation->getName(),
                'city' => $randomStation->getCityName(),
                'region' => $randomStation->getRegionName()
            ],
            'temperature' => round($temperature, 1),
            'timestamp' => time()
        ];

        echo json_encode($response);
    }
}