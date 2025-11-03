<?php

namespace Sae\Models\Repository;

use DateTime;
use Sae\Models\Accessor\SQLAccessor;
use Sae\Models\DataObject\Historic;

class HistoricRepository extends ARepository {

    public function __construct() {
        parent::__construct('historique', 'id_recent', ["id_recent", "id_utilisateur", "type", "reference_id", "date_ajout"]);
    }

    protected function createFromArray(array $data): Historic {
        return new Historic($data['id_recent'], $data['id_utilisateur'], $data['type'], $data['reference_id'], new DateTime($data['date_ajout']));
    }

    /**
     * Ajouter un élément à l'historique de l'utilisateur courant
     * @param string $type
     * @param mixed $reference
     */
    public function addToCurrentUser(string $type, mixed $reference): void {

        $userRepository = new UserRepository();
        $user = $userRepository->selectCurrent();

        if($user == null)
            return;

        $historic = new Historic(-1, $user->getId(), $type, $reference, new DateTime());
        $this->insert($historic);

    }

    /**
     * Insérer un nouvel élément dans l'historique
     * @param Historic $historic
     */
    public function insert(Historic $historic): void {

        $pdo = SQLAccessor::getConnection();
        $sql = "INSERT INTO historique (id_utilisateur, type, reference_id) VALUES (:id_utilisateur, :type, :reference_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_utilisateur' => $historic->getUser(),
            'type' => $historic->getType(),
            'reference_id' => $historic->getReference()
        ]);

    }

    /**
     * Sélectionner les éléments de l'historique d'un utilisateur
     * @param int $userId
     * @return Historic[]
     */
    public function selectByUser(int $userId) : array {
        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM historique WHERE id_utilisateur = :id_utilisateur";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_utilisateur' => $userId]);
        $results = $this->performSQL($stmt, list: true);

        // Trier par date décroissante
        usort($results, function($a, $b) {
            return $b->getDate() <=> $a->getDate();
        });

        return $results;
    }


}