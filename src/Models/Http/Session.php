<?php

namespace Sae\Models\Http;

use Exception;

/**
 * Classe permettant de gérer les sessions
 */
class Session {

    private static ?Session $instance = null;

    private function __construct() {
        if(!session_start())
            throw new Exception("Http failed to start.");
    }

    public static function getInstance() : Session {
        if(!isset(self::$instance))
            self::$instance = new Session();
        return self::$instance;
    }

    public function has(string $key) : bool {
        return isset($_SESSION[$key]);
    }

    public function set(string $key, $value) : void {
        $_SESSION[$key] = $value;
    }

    public function get(string $key) : mixed {
        return self::has($key) ? $_SESSION[$key] : null;
    }

    public function remove(string $key) : void {
        unset($_SESSION[$key]);
    }

    public function destroy() : void {
        session_unset();
        session_destroy();

        self::$instance = null;
    }

}