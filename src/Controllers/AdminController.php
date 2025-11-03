<?php

namespace Sae\Controllers;

use Sae\Models\Http\FlashMessage;
use Sae\Models\Repository\UserRepository;

/**
 * Contrôleur de l'administration
 */
class AdminController extends AController {

    /**
     * Constructeur de la classe AdminController
     */
    public function __construct(){
        parent::__construct("admin");
    }

    /**
     * Vérifie si l'utilisateur actuel est admin et redirige sinon
     * @return bool True si l'utilisateur est admin, False sinon
     */
    private function requireAdmin(): bool {
        if(!self::requireLogin("/auth/login")) {
            return false;
        }

        $userRepository = new UserRepository();
        $user = $userRepository->selectCurrent();

        if(!$user->isAdmin()) {
            $message = new FlashMessage("error", "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
            $message->save();
            self::redirect("/");
            return false;
        }

        return true;
    }

    /**
     * Gère les requêtes HTTP
     * @param array $path
     * @return bool
     */
    public function index(array $path): bool {

        if(!$this->requireAdmin()) {
            return false;
        }

        if(!isset($path[1]) || $path[1] === "") {
            $this->dashboard();
            return true;
        }

        $action = $path[1];
        if($action === "users") {
            $this->users();
            return true;
        } else if($action === "updateUser") {
            $this->updateUser($_POST);
            return true;
        } else if($action === "deleteUser") {
            $this->deleteUser($_POST);
            return true;
        } else if($action === "details") {
            if(isset($path[2]) && is_numeric($path[2])) {
                $this->details(intval($path[2]));
                return true;
            }
            self::redirect("/admin/users");
            return true;
        } else if($action === "update") {
            if(isset($path[2]) && is_numeric($path[2])) {
                $this->updateForm(intval($path[2]));
                return true;
            } else if(isset($_POST['userId'])) {
                $this->processUpdate($_POST);
                return true;
            }
            self::redirect("/admin/users");
            return true;
        }

        return false;
    }

    /**
     * Affiche le tableau de bord d'administration
     */
    private function dashboard(): void {
        $vars = [
            'title' => 'Administration - Tableau de bord',
            'styles' => ["admin/admin.css"]
        ];

        parent::loadView('dashboard', $vars);
    }

    /**
     * Affiche la page de gestion des utilisateurs
     */
    private function users(): void {
        $userRepository = new UserRepository();
        $users = $userRepository->selectAllForAdmin();

        $vars = [
            'title' => 'Administration - Utilisateurs',
            'styles' => ["admin/admin.css"],
            'users' => $users
        ];
        parent::loadView('users', $vars);
    }

    /**
     * Met à jour un utilisateur
     * @param array $data Les données POST
     */
    private function updateUser(array $data): void {
        if(!isset($data['userId']) || !isset($data['action'])) {
            $message = new FlashMessage("error", "Requête invalide.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        $userRepository = new UserRepository();
        $userId = intval($data['userId']);
        $action = $data['action'];

        // Vérifie que l'utilisateur existe
        $user = $userRepository->select($userId);
        if(!$user) {
            $message = new FlashMessage("error", "L'utilisateur n'existe pas.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        // Empêche la modification de son propre compte pour éviter de se bloquer
        $currentUser = $userRepository->selectCurrent();
        if($userId === $currentUser->getId()) {
            $message = new FlashMessage("error", "Vous ne pouvez pas modifier votre propre compte via cette interface.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        // Empêche la modification d'un autre compte administrateur
        if($user->isAdmin()) {
            $message = new FlashMessage("error", "Vous ne pouvez pas modifier un autre compte administrateur.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        if($action === "changeRole") {
            // Les utilisateurs standards ne peuvent jamais devenir admin via l'interface
            // Seule la rétrogradation est possible (d'admin à utilisateur)
            $role = 0;

            $userRepository->update($userId, ['role' => $role]);

            $message = new FlashMessage("success", "L'utilisateur a été rétrogradé au statut standard.");
            $message->save();
        }

        self::redirect("/admin/users");
    }

    /**
     * Supprime un utilisateur
     * @param array $data Les données POST
     */
    private function deleteUser(array $data): void {
        if(!isset($data['userId'])) {
            $message = new FlashMessage("error", "Requête invalide.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        $userRepository = new UserRepository();
        $userId = intval($data['userId']);

        // Vérifie que l'utilisateur existe
        $user = $userRepository->select($userId);
        if(!$user) {
            $message = new FlashMessage("error", "L'utilisateur n'existe pas.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        // Empêche la suppression de son propre compte pour éviter de se bloquer
        $currentUser = $userRepository->selectCurrent();
        if($userId === $currentUser->getId()) {
            $message = new FlashMessage("error", "Vous ne pouvez pas supprimer votre propre compte via cette interface.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        // Empêche la suppression d'un autre compte administrateur
        if($user->isAdmin()) {
            $message = new FlashMessage("error", "Vous ne pouvez pas supprimer un autre compte administrateur.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        $userRepository->delete($userId);

        $message = new FlashMessage("success", "L'utilisateur a été supprimé avec succès.");
        $message->save();
        self::redirect("/admin/users");
    }

    /**
     * Affiche les détails d'un utilisateur
     * @param int $userId ID de l'utilisateur
     */
    private function details(int $userId): void {
        $userRepository = new UserRepository();
        $user = $userRepository->select($userId);

        if(!$user) {
            $message = new FlashMessage("error", "L'utilisateur n'existe pas.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        $vars = [
            'title' => 'Détails de l\'utilisateur',
            'styles' => ["admin/admin.css"],
            'user' => $user
        ];

        $this->loadView('details', $vars);
    }

    /**
     * Affiche le formulaire de mise à jour d'un utilisateur
     * @param int $userId ID de l'utilisateur
     */
    private function updateForm(int $userId): void {
        $userRepository = new UserRepository();
        $user = $userRepository->select($userId);

        if(!$user) {
            $message = new FlashMessage("error", "L'utilisateur n'existe pas.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        // Un administrateur ne peut pas modifier un autre administrateur
        $currentUser = $userRepository->selectCurrent();
        if($user->isAdmin() && $userId !== $currentUser->getId()) {
            $message = new FlashMessage("error", "Vous ne pouvez pas modifier un autre compte administrateur.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        $vars = [
            'title' => 'Modifier l\'utilisateur',
            'styles' => ["admin/admin.css"],
            'user' => $user
        ];

        $this->loadView('update', $vars);
    }

    /**
     * Traite la mise à jour d'un utilisateur
     * @param array $data Données du formulaire
     */
    private function processUpdate(array $data): void {
        if(!isset($data['userId'])) {
            $message = new FlashMessage("error", "Requête invalide.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        $userId = intval($data['userId']);
        $userRepository = new UserRepository();
        $user = $userRepository->select($userId);

        if(!$user) {
            $message = new FlashMessage("error", "L'utilisateur n'existe pas.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        // Un administrateur ne peut pas modifier un autre administrateur
        $currentUser = $userRepository->selectCurrent();
        if($user->isAdmin() && $userId !== $currentUser->getId()) {
            $message = new FlashMessage("error", "Vous ne pouvez pas modifier un autre compte administrateur.");
            $message->save();
            self::redirect("/admin/users");
            return;
        }

        // Préparer les données à mettre à jour en utilisant les noms de colonnes exacts de la base de données
        $updateData = [];

        // Ne pas permettre de changer certaines données sensibles comme le mot de passe
        if(isset($data['pseudo']) && !empty($data['pseudo'])) {
            $updateData['pseudo'] = $data['pseudo'];
        }

        if(isset($data['prenom']) && !empty($data['prenom'])) {
            $updateData['prenom'] = $data['prenom'];
        }

        if(isset($data['nom']) && !empty($data['nom'])) {
            $updateData['nom'] = $data['nom'];
        }

        if(isset($data['email']) && !empty($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        // S'assurer qu'un utilisateur ne puisse pas devenir admin
        if(isset($data['role']) && $currentUser->isAdmin()) {
            // Seuls les admins peuvent changer les rôles, et on s'assure qu'ils
            // ne peuvent pas promouvoir d'utilisateurs au rang d'admin
            $role = intval($data['role']);
            if($role === 0) {
                $updateData['role'] = 0;
            }
        }

        if(empty($updateData)) {
            $message = new FlashMessage("error", "Aucune donnée à mettre à jour.");
            $message->save();
            self::redirect("/admin/update/" . $userId);
            return;
        }

        // Debug: Vérifier les données avant mise à jour
        // echo "<pre>Données à mettre à jour: "; print_r($updateData); echo "</pre>"; exit;

        $success = $userRepository->update($userId, $updateData);

        if(!$success) {
            $message = new FlashMessage("error", "Une erreur s'est produite lors de la mise à jour.");
            $message->save();
            self::redirect("/admin/update/" . $userId);
            return;
        }

        $message = new FlashMessage("success", "L'utilisateur a été mis à jour avec succès.");
        $message->save();
        self::redirect("/admin/details/" . $userId);
    }
}