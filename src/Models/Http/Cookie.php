<?php

namespace Sae\Models\Http;

/**
 * Classe permettant de gérer les cookies
 */
class Cookie {

    private function __construct(){
    }

    public static function has(string $key) : bool {
        return isset($_COOKIE[$key]);
    }

    public static function set(string $key, $value, int $expire) : void {
        setcookie($key, serialize($value), time() + $expire, "/");
    }

    public static function get(string $key) {
        return self::has($key) ? unserialize($_COOKIE[$key]) : null;
    }

    public static function remove(string $key) : void {
        setcookie($key, "", 0, "/");
    }

}