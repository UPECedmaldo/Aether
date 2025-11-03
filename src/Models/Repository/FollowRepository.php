<?php

namespace Sae\Models\Repository;

use PDO;
use Sae\Models\Accessor\SQLAccessor;

class FollowRepository {

    public function isFollow(int $userId, int $targetId) : bool {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM follower WHERE id_utilisateur_1 = :userId AND id_utilisateur_2 = :targetId";

        $statement = $pdo->prepare($sql);
        $statement->execute([
            'userId' => $userId,
            'targetId' => $targetId
        ]);

        return (bool) $statement->fetch();

    }

    public function addFollow(int $userId, int $targetId) : bool {
        $pdo = SQLAccessor::getConnection();
        $sql = "INSERT INTO follower (id_utilisateur_1, id_utilisateur_2) VALUES (:userId, :targetId)";

        $statement = $pdo->prepare($sql);
        return $statement->execute([
            'userId' => $userId,
            'targetId' => $targetId
        ]);
    }

    public function removeFollow(int $userId, int $targetId) : bool {
        $pdo = SQLAccessor::getConnection();
        $sql = "DELETE FROM follower WHERE id_utilisateur_1 = :userId AND id_utilisateur_2 = :targetId";

        $statement = $pdo->prepare($sql);
        return $statement->execute([
            'userId' => $userId,
            'targetId' => $targetId
        ]);
    }

    public function areFriends(int $userId, int $targetId) : bool {
        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM follower WHERE (id_utilisateur_1 = :userId AND id_utilisateur_2 = :targetId) OR (id_utilisateur_1 = :targetId AND id_utilisateur_2 = :userId)";

        $statement = $pdo->prepare($sql);
        $statement->execute([
            'userId' => $userId,
            'targetId' => $targetId
        ]);

        return (bool) $statement->fetch();
    }

    public function getFollowers(int $userId) : array {
        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT u.* 
            FROM follower f 
            JOIN utilisateur u ON f.id_utilisateur_1 = u.id_utilisateur
            WHERE f.id_utilisateur_2 = :userId";

        $statement = $pdo->prepare($sql);
        $statement->execute([
            'userId' => $userId
        ]);

        $userRepository = new UserRepository();
        $followers = [];
        while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $followers[] = $userRepository->select($row['id_utilisateur']);
        }

        return $followers;
    }


}