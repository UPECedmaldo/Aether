<?php

namespace Sae\Controllers;

use DateTime;
use Sae\Models\DataObject\User;
use Sae\Models\Http\FlashMessage;
use Sae\Models\Http\Session;
use Sae\Models\Repository\HistoricRepository;
use Sae\Models\Repository\UserRepository;
use Sae\Utils\FormUtil;

/**
 * Contrôleur de l'authentification
 */
class AuthController extends AController {

    /**
     * Constructeur de la classe AuthController
     */
    public function __construct(){
        parent::__construct("auth");
    }

    /**
     * Gère les requêtes HTTP
     * @param array $path
     * @return bool
     */
    public function index(array $path): bool {
        if(!isset($path[1])) {
            //$this->loadView('login');
            $this->loadLoginView();
            return true;
        }

        $action = $path[1];
        if($action == 'login') {
            $this->loadLoginView();
            return true;
        } else if($action == 'logged') {
            $this->logged($_POST);
            return true;
        } else if($action == 'register') {
            $this->loadRegisterView();
            return true;
        } else if($action == 'registered') {
            $this->registered($_POST);
            return true;
        }  else if($action == 'settings') {
            $this->settings();
            return true;
        } else if($action == 'logout') {
            $this->logout();
            return true;
        } else if($action == 'update') {
            $this->update($_POST);
            return true;
        } else if($action == 'delete') {
            $this->delete();
            return true;
        }

        return false;
    }

    public static string $loginRedirect = 'login_redirect_url'; // Nom de la clé de redirection

    /**
     * Charge une vue mais si l'utilisateur est déjà connecté, affiche un message d'alerte
     * @param string $path
     * @param array $vars
     * @param bool $autoFlashMessages
     * @return void
     */
    protected function loadView(string $path, array $vars = [], bool $autoFlashMessages = true): void {

        $repository = new UserRepository();
        $user = $repository->selectCurrent();
        if($user != null) {

            $flashMessage = new FlashMessage("warning", 'Vous semblez être déjà connecté');
            $flashMessage->save();

        }

        parent::loadView($path, $vars, $autoFlashMessages);
    }

    /**
     * Connecte un utilisateur
     * @param $data
     * @return void
     */
    private function logged($data) : void {

        if(!FormUtil::checkFields($data, ['input', 'password'])) {
            $this->loadLoginView();
            return;
        }

        $input = $data['input'];
        $password = $data['password'];
        $password = hash('sha256', $password);

        $repository = new UserRepository();

        $exists = $repository->isRegistered($input, $password);
        if(!$exists) {

            $flashMessage = new FlashMessage("error", 'Identifiants incorrects');
            $flashMessage->save();

            self::redirect("/auth/login");
            return;
        }

        $user = $repository->selectByInput($input);
        if($user == null) {

            $flashMessage = new FlashMessage("error", 'Erreur lors de la récupération de l\'utilisateur');
            $flashMessage->save();

            self::redirect("/auth/login");
            return;
        }

        // Todo: last login

        $session = Session::getInstance();
        $repository->insertIntoSession($session, $user);

        $loginRedirect = $session->get(self::$loginRedirect);
        if($loginRedirect != null) {
            $session->remove(self::$loginRedirect);
            self::redirect($loginRedirect);
        } else {
            self::redirectFallBack();
        }

    }

    /**
     * Charge la vue de connexion
     * @return void
     */
    private function loadLoginView() : void {
        $vars = [
            'title' => 'Connexion',
            'styles' => ["auth/auth.css"]
        ];
        $this->loadView('login', $vars, autoFlashMessages: false);
    }

    /**
     * Enregistre un utilisateur
     * @param $data
     * @return void
     */
    private function registered($data) : void {

        if(!FormUtil::checkFields($data, ['pseudo', 'prenom', 'nom', 'email', 'password'])) {
            $this->loadRegisterView();
            return;
        }

        $repository = new UserRepository();

        $pseudo = $data['pseudo'];
        if($repository->isUsed(['pseudo' => $pseudo])) {

            $flashMessage = new FlashMessage("error", 'Ce pseudo est déjà utilisé');
            $flashMessage->save();

            self::redirect("/auth/register");
            return;
        }

        $email = $data['email'];
        if($repository->isUsed(['email' => $email])) {

            $flashMessage = new FlashMessage("error", 'Cet email est déjà utilisé');
            $flashMessage->save();

            self::redirect("/auth/register");
            return;
        }

        $name = $data['prenom'];
        $lastName = $data['nom'];
        $password = $data['password'];
        $password = hash('sha256', $password);

        $date = new DateTime();
        $user = new User(-1, $pseudo, $name, $lastName, $email, $password, $date->format('Y-m-d H:i:s'), $date->format('Y-m-d H:i:s'), 0);

        $repository->insert($user);

        $flashMessage = new FlashMessage("success", 'Inscription réussie');
        $flashMessage->save();

        $this->loadLoginView();
    }

    /**
     * Charge la vue d'inscription
     * @return void
     */
    private function loadRegisterView() : void {
        $vars = [
            'title' => 'Inscription',
            'styles' => ["auth/auth.css"]
        ];
        $this->loadView('register', $vars, autoFlashMessages: false);
    }

    /**
     * Charge la vue des paramètres
     * @return void
     */
    private function settings() : void {

        if(!self::requireLogin("/auth/settings"))
            return;

        $userRepository = new UserRepository();
        $historicRepository = new HistoricRepository();

        $user = $userRepository->selectCurrent();
        $historics = $historicRepository->selectByUser($user->getId());

        $vars = [
            'title' => 'Paramètres',
            'styles' => ["auth/settings.css"],

            'historics' => $historics
        ];

        parent::loadView('settings', $vars, autoFlashMessages: false);
    }

    /**
     * Met à jour les informations d'un utilisateur
     * @param $data
     * @return void
     */
    private function update($data) : void  {
        if (!self::requireLogin("/auth/settings")) {
            return;
        }

        if (!FormUtil::checkFields($data, ['type'])) {
            self::redirect("/auth/settings");
            return;
        }

        $repository = new UserRepository();
        $user = $repository->selectCurrent();

        $type = $data['type'];
        switch ($type) {

            case "password":

                if (!FormUtil::checkFields($data, ['current_password', 'password'])) {
                    self::redirect("/auth/settings");
                    return;
                }

                $currentPassword = $data['current_password'];
                $password = $data['password'];

                $currentPassword = hash('sha256', $currentPassword);
                if($currentPassword != $user->getPassword()) {
                    $flashMessage = new FlashMessage("error", 'Mot de passe actuel incorrect');
                    $flashMessage->save();
                    self::redirect("/auth/settings");
                    return;
                }

                $passwordHash = hash('sha256', $password);
                if($passwordHash == $user->getPassword()) {
                    $flashMessage = new FlashMessage("error", 'Le nouveau mot de passe est identique à l\'ancien');
                    $flashMessage->save();
                    self::redirect("/auth/settings");
                    return;
                }

                $repository->update($user->getId(), ['mot_de_passe' => $passwordHash]);

                $flashMessage = new FlashMessage("success", 'Mot de passe modifié');
                $flashMessage->save();

                break;

            case "email":

                if (!FormUtil::checkFields($data, ['email'])) {
                    self::redirect("/auth/settings");
                    return;
                }

                $email = $data['email'];
                if($email == $user->getEmail()) {
                    $flashMessage = new FlashMessage("error", 'L\'adresse e-mail est identique à l\'ancienne');
                    $flashMessage->save();
                    self::redirect("/auth/settings");
                    return;
                }

                if($repository->isUsed(['email' => $email])) {
                    $flashMessage = new FlashMessage("error", 'Cette adresse e-mail est déjà utilisée');
                    $flashMessage->save();
                    self::redirect("/auth/settings");
                    return;
                }

                $repository->update($user->getId(), ['email' => $email]);

                $flashMessage = new FlashMessage("success", 'Adresse e-mail modifiée');
                $flashMessage->save();

                break;

            case "pseudo":

                if (!FormUtil::checkFields($data, ['pseudo'])) {
                    self::redirect("/auth/settings");
                    return;
                }

                $pseudo = $data['pseudo'];
                if($pseudo == $user->getPseudo()) {
                    $flashMessage = new FlashMessage("error", 'Le pseudonyme est identique à l\'ancien');
                    $flashMessage->save();
                    self::redirect("/auth/settings");
                    return;
                }

                if($repository->isUsed(['pseudo' => $pseudo])) {
                    $flashMessage = new FlashMessage("error", 'Ce pseudonyme est déjà utilisé');
                    $flashMessage->save();
                    self::redirect("/auth/settings");
                    return;
                }

                $repository->update($user->getId(), ['pseudo' => $pseudo]);

                $flashMessage = new FlashMessage("success", 'Pseudonyme modifié');
                $flashMessage->save();

                break;
            
            case "photo":
                if (!isset($_FILES['photo'])) {
                    self::redirect("/auth/settings");
                    return;
                }
                
                $photo = file_get_contents($_FILES['photo']['tmp_name']);
                $repository->update($user->getId(), ['photo_profil' => $photo]);
                
                $flashMessage = new FlashMessage("success", 'Photo de profil mise à jour');
                $flashMessage->save();
                break;
                
            default:
                self::redirect("/auth/settings");
                break;
        }

        $this->settings();
    }

    /**
     * Déconnecte un utilisateur
     * @return void
     */
    private function logout() : void {

        $session = Session::getInstance();
        $session->destroy();

        self::redirectFallBack();
    }

    /**
     * Supprime un utilisateur
     * @return void
     */
    private function delete() : void{

        if (!self::requireLogin("/auth/settings")) {
            return;
        }

        $repository = new UserRepository();
        $user = $repository->selectCurrent();

        $repository->delete($user->getId());

        $this->logout();
    }

}