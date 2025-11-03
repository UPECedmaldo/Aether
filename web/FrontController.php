<?php

use Sae\Controllers\AdminController;
use Sae\Controllers\AuthController;
use Sae\Controllers\GameController;
use Sae\Controllers\GraphController;
use Sae\Controllers\IndexController;
use Sae\Controllers\ProfileController;
use Sae\Controllers\WLibraryController;
use Sae\Controllers\StationController;


require_once __DIR__ . '/../src/Lib/Psr4AutoloaderClass.php';

# Autoload classes
$loader = new Psr4AutoloaderClass();
$loader->addNamespace("Sae", __DIR__ . "/../src");
$loader->register();

$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = str_replace("/web/FrontController.php", "", $requestUri); // Retire le chemin vers le FrontController

$parsedUrl = parse_url($requestUri);
$requestUri = $parsedUrl['path'] ?? $requestUri;

$segments = explode('/', trim($requestUri, '/'));
if(empty($segments[0])) { // Si aucun segment n'est présent, on redirige vers la page d'accueil
    $segments[0] = "index";
}

$controllers = [];
$controllers["index"] = new IndexController();
$controllers["auth"] = new AuthController();
$controllers["profile"] = new ProfileController();
$controllers["station"] = new StationController();
$controllers["graph"] = new GraphController();
$controllers["library"] = new WLibraryController();
$controllers["game"] = new GameController();
$controllers["admin"] = new AdminController();

$handler = $controllers[$segments[0]] ?? null;
if($handler == null) { // Si le contrôleur n'existe pas, on redirige vers une erreur 404
    error404($requestUri);
    return;
}

$handled = $handler->index($segments);
if(!$handled) {
    error404($requestUri);
    return;
}

/**
 * Affiche une erreur 404
 * @param string $url
 */
function error404(string $url) : void {
    http_response_code(404);
    $controller = new IndexController();
    $controller->error404();
}