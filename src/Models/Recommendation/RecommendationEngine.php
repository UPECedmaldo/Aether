<?php

namespace Sae\Models\Recommendation;

use Exception;
use PDO;
use PDOException;
use Sae\Models\Accessor\SQLAccessor;
use Sae\Models\DataObject\View\ListedWLibrary;
use Sae\Models\DataObject\WLibrary;
use Sae\Models\Repository\FollowRepository;
use Sae\Models\Repository\HistoricRepository;
use Sae\Models\Repository\UserRepository;
use Sae\Models\Repository\WLibraryRepository;

/**
 * Moteur de recommandation pour les météothèques
 */
class RecommendationEngine {

    /**
     * Vérifie si une table existe dans la base de données
     *
     * @param string $tableName Nom de la table à vérifier
     * @return bool True si la table existe, false sinon
     */
    private static function tableExists(string $tableName): bool {
        $pdo = SQLAccessor::getConnection();
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE :tableName");
            $stmt->execute(['tableName' => $tableName]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtient des recommandations de météothèques pour un utilisateur donné
     *
     * @param int $userId ID de l'utilisateur
     * @param int $limit Nombre maximum de recommandations
     * @return ListedWLibrary[] Tableau de météothèques recommandées
     */
    public static function getRecommendations(int $userId, int $limit = 5): array {
        try {
            $recommendations = [];
            $scores = [];

            // Récupération des météothèques déjà possédées par l'utilisateur (pour les exclure)
            $wLibraryRepo = new WLibraryRepository();
            $userLibraries = $wLibraryRepo->selectByUser($userId);
            $userLibraryIds = array_map(fn($lib) => $lib->getId(), $userLibraries);

            // Recommandations basées sur la popularité (fonctionne toujours)
            $popularityBasedRecommendations = self::getPopularityBasedRecommendations();
            foreach ($popularityBasedRecommendations as $id => $score) {
                if (!in_array($id, $userLibraryIds)) {
                    $scores[$id] = ($scores[$id] ?? 0) + $score;
                }
            }

            // Si la table historique existe, ajouter des recommandations basées sur l'historique
            if (self::tableExists('historique')) {
                $historicBasedRecommendations = self::getHistoricBasedRecommendations($userId);
                foreach ($historicBasedRecommendations as $id => $score) {
                    if (!in_array($id, $userLibraryIds)) {
                        $scores[$id] = ($scores[$id] ?? 0) + $score;
                    }
                }
            }

            // Si la table follower existe, ajouter des recommandations basées sur les amis
            if (self::tableExists('follower')) {
                $friendBasedRecommendations = self::getFriendBasedRecommendations($userId);
                foreach ($friendBasedRecommendations as $id => $score) {
                    if (!in_array($id, $userLibraryIds)) {
                        $scores[$id] = ($scores[$id] ?? 0) + $score;
                    }
                }
            }

            // Trier par score décroissant et prendre les $limit premiers
            arsort($scores);
            $recommendationIds = array_slice(array_keys($scores), 0, $limit);

            // Récupérer les météothèques complètes
            foreach ($recommendationIds as $id) {
                $library = $wLibraryRepo->select($id);
                if ($library && $library->isPublic()) {
                    $recommendations[] = $wLibraryRepo->convertToView($library);
                }
            }

            return $recommendations;
        } catch (Exception $e) {
            // En cas d'erreur critique, retourner un tableau vide
            return [];
        }
    }

    /**
     * Calcule des recommandations basées sur l'historique de l'utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return array Tableau associatif [id_meteotheque => score]
     */
    private static function getHistoricBasedRecommendations(int $userId): array {
        try {
            $scores = [];
            $pdo = SQLAccessor::getConnection();

            // Récupérer les stations récemment consultées par l'utilisateur
            $historicRepo = new HistoricRepository();
            $userHistory = $historicRepo->selectByUser($userId);

            // Récupérer les IDs de stations consultées
            $stationIds = [];
            foreach ($userHistory as $historic) {
                if ($historic->getType() === 'station') {
                    $stationIds[] = $historic->getReference();
                }
            }

            // Si l'historique est vide, pas de recommandation possible
            if (empty($stationIds)) {
                return [];
            }

            // Trouver des météothèques publiques contenant ces stations
            $placeholders = implode(',', array_fill(0, count($stationIds), '?'));
            $query = "
                SELECT f.id_meteotheque, COUNT(*) as common_stations, m.etat
                FROM favoris f
                JOIN meteotheque m ON f.id_meteotheque = m.id_meteotheque
                WHERE f.type = 'station' 
                AND f.reference_id IN ($placeholders)
                AND m.etat = 1 -- public libraries only
                AND m.id_utilisateur != ?
                GROUP BY f.id_meteotheque
                ORDER BY common_stations DESC
            ";

            $params = array_merge($stationIds, [$userId]);
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            // Calculer les scores en fonction du nombre de stations communes
            while ($row = $stmt->fetch()) {
                $libraryId = $row['id_meteotheque'];
                $commonStations = $row['common_stations'];

                // Plus il y a de stations en commun, plus le score est élevé
                $scores[$libraryId] = $commonStations * 2.0;
            }

            return $scores;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Calcule des recommandations basées sur les amis de l'utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return array Tableau associatif [id_meteotheque => score]
     */
    private static function getFriendBasedRecommendations(int $userId): array {
        try {
            $scores = [];

            // Récupérer la liste des amis de l'utilisateur
            $followRepo = new FollowRepository();
            $friends = $followRepo->getFollowers($userId);
            $friendIds = array_column($friends, 'id_utilisateur');

            if (empty($friendIds)) {
                return [];
            }

            // Récupérer les météothèques publiques de chaque ami
            $wLibraryRepo = new WLibraryRepository();
            foreach ($friendIds as $friendId) {
                $libraries = $wLibraryRepo->selectByUser($friendId);

                foreach ($libraries as $library) {
                    if ($library->isPublic()) {
                        // Les météothèques des amis ont un score de base de 1.5
                        $scores[$library->getId()] = ($scores[$library->getId()] ?? 0) + 1.5;
                    }
                }
            }

            return $scores;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Calcule des recommandations basées sur la popularité générale
     *
     * @return array Tableau associatif [id_meteotheque => score]
     */
    private static function getPopularityBasedRecommendations(): array {
        try {
            $pdo = SQLAccessor::getConnection();
            $scores = [];

            // Météothèques les plus populaires (basées sur le nombre de références)
            $query = "
                SELECT m.id_meteotheque, COUNT(f.reference_id) as ref_count
                FROM meteotheque m
                LEFT JOIN favoris f ON m.id_meteotheque = f.id_meteotheque
                WHERE m.etat = 1 -- public libraries only
                GROUP BY m.id_meteotheque
                ORDER BY ref_count DESC
                LIMIT 10
            ";

            $stmt = $pdo->prepare($query);
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $libraryId = $row['id_meteotheque'];
                $refCount = $row['ref_count'];

                // Score basé sur le nombre de références
                $scores[$libraryId] = $refCount * 0.5;
            }

            return $scores;
        } catch (Exception $e) {
            return [];
        }
    }
}