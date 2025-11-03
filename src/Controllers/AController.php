<?php

namespace Sae\Controllers;

use Sae\Models\Http\FlashMessage;
use Sae\Models\Http\Session;
use Sae\Models\Repository\UserRepository;

/**
 * Classe abstraite AController
 */
abstract class AController {

    private string $viewPath;

    /**
     * Constructeur de la classe AController
     * @param string $viewPath Chemin vers les vues
     */
    public function __construct(string $viewPath) {
        $this->viewPath = $viewPath;
    }

    /**
     * Redirige l'utilisateur vers une page
     * @param string $url
     * @return void
     */
    protected static function redirect(string $url): void {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Redirige l'utilisateur vers la page de bienvenue
     * @return void
     */
    protected static function redirectFallBack() : void {
        self::redirect('/index/welcome');
    }

    /**
     * Vérifie si l'utilisateur est connecté
     * sinon le redirige vers la page de connexion
     * @param string $fallBackUrl
     * @return bool
     */
    protected static function requireLogin($fallBackUrl) : bool {
        $repository = new UserRepository();
        $user = $repository->selectCurrent();
        if($user == null) {
            $flashMessage = new FlashMessage("error", 'Vous devez être connecté pour accéder à cette page');
            $flashMessage->save();

            $session = Session::getInstance();
            $session->set(AuthController::$loginRedirect, $fallBackUrl);

            self::redirect('/auth/login');
            return false;
        }
        return true;
    }

    /**
     * Traite les requêtes si l'url contient son chemin
     * auth/login -> AuthController->login()
     * @param array $path
     * @return bool
     */
    public abstract function index(array $path): bool;

    /**
     * Charge une vue
     * @param string $path
     * @param array $vars
     * @param bool $autoFlashMessages
     * @return void
     */
    protected function loadView(string $path, array $vars = [], bool $autoFlashMessages = true): void {

        $userRepository = new UserRepository();
        $user = $userRepository->selectCurrent();

        $vars["user"] = $user;

        extract($vars);

        if(!isset($styles))
            $styles = [];

        $stylePath = '/public/assets/styles/';
        if(!is_array($styles))
            $styles = [$styles];

        $common = 'view.css';
        if(!in_array($common, $styles))
            array_unshift($styles, $common);

        for ($i = 0; $i < count($styles); $i++) {
            $style = $styles[$i];
            $style = self::processStyle($stylePath, $style);
            $styles[$i] = $style;
        }

        // Popuplate flash messages
        $flashMessages = FlashMessage::all();

        $blank = __DIR__ . '/../views/view.php';
        $body = __DIR__ . '/../views/' . $this->viewPath . '/' . $path . '.php';
        require_once $blank;
    }

    /**
     * Transforme un style en balise HTML
     * @param string $path
     * @param string $style
     */
    private static function processStyle($path, $style) : string {
        if(self::isHTML($style))
            return $style;

        return "<link rel='stylesheet' href='" . $path . $style . "'>";;
    }

    /**
     * Vérifie si une chaîne de caractères est du HTML
     * @param string $text
     * @return bool
     */
    private static function isHTML($text) : bool {
        $processed = htmlentities($text);
        return $processed != $text;
    }

}