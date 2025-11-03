<?php

namespace Sae\Utils;

use Sae\Models\Http\Cookie;
use Sae\Models\Http\FlashMessage;
use Sae\Models\Http\Session;

class FormUtil {

    /**
     * Vérifie si les champs sont présents dans le tableau de données
     * @param array $data
     * @param array $fields
     * @param bool $flashMessages
     * @return bool
     */
    public static function checkFields(array $data, array $fields, bool $flashMessages = true) : bool {

        $missing = [];
        foreach($fields as $field) {
            if(!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }

        if(!empty($missing)) {

            if($flashMessages) {
                $missing = implode(', ', $missing);

                $flashMessage = new FlashMessage("error", "Veuillez remplir tous les champs: $missing");
                $flashMessage->save();
            }

            return false;
        }

        return true;
    }

    /**
     * Vérifie si les champs sont présents dans le tableau de données
     * @param array $data
     * @param array $fields
     * @param bool $flashMessages
     * @return bool
     */
    public static function findIn(string $key, bool $post = true, bool $get = true, bool $cookie = true, bool $session = true) : mixed {
        if($cookie && Cookie::has($key))
            return Cookie::get($key);
        if($session) {
            $session = Session::getInstance();
            if($session->has($key))
                return $session->get($key);
        }
        if($post && isset($_POST[$key]))
            return $_POST[$key];
        if($get && isset($_GET[$key]))
            return $_GET[$key];
        return null;
    }

}