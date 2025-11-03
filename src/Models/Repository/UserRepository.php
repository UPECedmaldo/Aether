<?php

namespace Sae\Models\Repository;

use Sae\Models\Accessor\SQLAccessor;
use Sae\Models\DataObject\Profile;
use Sae\Models\DataObject\User;
use Sae\Models\Http\Session;
use PDO;
use PDOException;

/**
 * Classe représentant un dépôt d'utilisateurs
 */
class UserRepository extends ARepository {

    public static string $cookieName = "user_id";

    public function __construct() {
        parent::__construct("utilisateur", "id_utilisateur", [
            "id_utilisateur", "pseudo", "prenom", "nom", "email",
            "mot_de_passe", "date_creation", "date_connexion", "role", "photo_profil"
        ]);
    }

    protected function createFromArray(array $data): ?User {
        return new User(
            $data['id_utilisateur'],
            $data['pseudo'],
            $data['prenom'],
            $data['nom'],
            $data['email'],
            $data['mot_de_passe'],
            $data['date_creation'],
            $data['date_connexion'],
            $data['role'] ?? 0,
            $data['photo_profil'] ?? null
        );
    }

    /**
     * Vérifie si un utilisateur est enregistré selon un pseudo ou un email et un mot de passe
     * @param string $input
     * @param string $password
     * @return bool
     */
    public function isRegistered(string $input, string $password) : bool {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM utilisateur WHERE (pseudo = :input OR email = :input) AND mot_de_passe = :password";

        $statement = $pdo->prepare($sql);
        $statement->execute([
            'input' => $input,
            'password' => $password
        ]);

        $result = $statement->fetch();
        return (bool) $result;
    }

    /**
     * Retourne un utilisateur selon un pseudo ou un email
     * @param string $input
     * @return User|null
     */
    public function selectByInput(string $input) : ?User {

        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM utilisateur WHERE pseudo = :input OR email = :input";

        $prepared = $pdo->prepare($sql);
        $prepared->execute([
            'input' => $input
        ]);

        return $this->performSQL($prepared);
    }

    /**
     * Vérifie si des colonnes sont utilisées
     * Nom, émail, pseudo, etc.
     * @param array $columns
     * @param string $validator
     * @return bool
     */
    public function isUsed(array $columns, string $validator = "AND") : bool {

        $pdo = SQLAccessor::getConnection();

        $sql = "SELECT * FROM utilisateur WHERE ";
        $i = 0;
        foreach($columns as $column => $value) {
            $sql .= $column . " = :" . $column;
            if($i < count($columns) - 1)
                $sql .= " " . $validator . " ";
            $i++;
        }

        $statement = $pdo->prepare($sql);
        $statement->execute($columns);

        $result = $statement->fetch();
        return (bool) $result;
    }

    /**
     * Insère un utilisateur dans la base de données
     * @param User $user
     */
    public function insert(User $user) : void {

        $pdo = SQLAccessor::getConnection();
        $sql = "INSERT INTO utilisateur (pseudo, prenom, nom, email, mot_de_passe, date_connexion) VALUES (:pseudo, :prenom, :nom, :email, :password, :connection)";

        $statement = $pdo->prepare($sql);
        $statement->execute([
            'pseudo' => $user->getPseudo(),
            'prenom' => $user->getName(),
            'nom' => $user->getLastName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'connection' => $user->getLastLogin()
        ]);

    }

    /**
     * Met à jour un utilisateur dans la base de données
     * @param int $userId ID de l'utilisateur
     * @param array $data Données à mettre à jour
     * @return void
     */
    public function update($userId, array $data): void {
        if(empty($data)) {
            return;
        }

        $pdo = SQLAccessor::getConnection();

        // Construire la requête SQL de mise à jour
        $sql = "UPDATE utilisateur SET ";
        $params = [];

        foreach($data as $column => $value) {
            $sql .= "$column = :$column, ";
            $params[":$column"] = $value;
        }

        // Supprimer la virgule finale
        $sql = rtrim($sql, ", ");

        // Ajouter la condition WHERE
        $sql .= " WHERE id_utilisateur = :id_utilisateur";
        $params[":id_utilisateur"] = $userId;

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } catch(PDOException $e) {
            // Journaliser l'erreur
            error_log("Erreur lors de la mise à jour de l'utilisateur: " . $e->getMessage());
        }
    }

    /**
     * Récupère un utilisateur selon un identifiant stocké dans une session
     * @param User $user
     */
    public function selectFromSession(Session $session) : ?User {

        if(!$session->has(self::$cookieName))
            return null;

        $id = $session->get(self::$cookieName);
        return $this->select($id);
    }

    /**
     * Récupère l'utilisateur actuellement connecté
     * @return User|null
     */
    public function selectCurrent() : ?User {
        $session = Session::getInstance();
        return $this->selectFromSession($session);
    }

    /**
     * Insère un utilisateur dans une session
     * @param Session $session
     * @param User $user
     */
    public function insertIntoSession(Session $session, User $user) : void {
        $session->set(self::$cookieName, $user->getId());
    }

    /**
     * Récupère tous les utilisateurs non administrateurs
     * @return array
     */
    public function selectAllNonAdmin(): array {
        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM utilisateur";

        $statement = $pdo->query($sql);
        return $this->performMultipleSQL($statement);
    }

    /**
     * Sélectionne tous les utilisateurs pour l'administration
     * @return array Liste de tous les utilisateurs
     */
    public function selectAllForAdmin(): array {
        $pdo = SQLAccessor::getConnection();
        $sql = "SELECT * FROM utilisateur ORDER BY id_utilisateur";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $users = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new User(
                $row['id_utilisateur'],
                $row['pseudo'],
                $row['prenom'],
                $row['nom'],
                $row['email'],
                $row['mot_de_passe'],
                $row['date_creation'],
                $row['date_connexion'],
                $row['role'],
                $row['photo_profil']
            );
            $users[] = $user;
        }

        return $users;
    }

    public function selectProfile(int|string $input) {

        $query = "SELECT * FROM utilisateur WHERE pseudo = :input OR id_utilisateur = :input";
        $statement = SQLAccessor::getConnection()->prepare($query);
        $statement->execute(['input' => $input]);
        $result = $statement->fetch();

        if(!$result)
            return null;

        $userId = $result["id_utilisateur"];
        $userPseudo = $result["pseudo"];

        $query = "SELECT COUNT(*) as count FROM meteotheque WHERE id_utilisateur = :id";
        $statement = SQLAccessor::getConnection()->prepare($query);
        $statement->execute(['id' => $userId]);
        $result = $statement->fetch();
        $libraryCount = $result["count"];

        $query = "SELECT COUNT(*) as count FROM follower WHERE id_utilisateur_2 = :id";
        $statement = SQLAccessor::getConnection()->prepare($query);
        $statement->execute(['id' => $userId]);
        $result = $statement->fetch();

        $followerCount = $result["count"];

        $avatar = $this->profilePictureUrl($userId);

        $profile = new Profile($userId, $avatar, $userPseudo, $followerCount, $libraryCount);
        return $profile;
    }

    public function profilePictureUrl(int|null|User $user)
    {
        if ($user == null) {
            return "/public/assets/images/default.png";
        }

        if (is_numeric($user)) {
            $user = $this->select($user);
            if (!$user) {
                return "/public/assets/images/default.png";
            }
            return $user && $user->getProfilePhoto() ? "data:image/png;base64," . base64_encode(
                    $user->getProfilePhoto()
                ) : "/public/assets/images/default.png";
        }

        return $user->getProfilePhoto() ? "data:image/png;base64," . base64_encode(
                $user->getProfilePhoto()
            ) : "/public/assets/images/default.png";
    }

}