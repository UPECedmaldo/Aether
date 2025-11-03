<?php

namespace Sae\Models\Http;

use Exception;

/**
 * Classe permettant de gérer les messages flash
 * Quand on veut afficher un message à l'utilisateur après une redirection
 * et qui ne doit être affiché qu'une seule fois
 */
class FlashMessage {

    private static string $key = "flash_messages";
    private static array $types = ["success", "info", "warning", "error"];

    private string $type;
    private string $message;

    /**
     * @throws Exception
     */
    public function __construct(string $type, string $message) {
        if(!in_array($type, self::$types))
            throw new Exception("Invalid type: {$type}");

        $this->type = $type;
        $this->message = $message;
    }

    public static function add(FlashMessage $message) : void {

        $session = Session::getInstance();
        $messages = $session->has(self::$key) ? $session->get(self::$key) : [];

        $messages[] = $message;

        $session->set(self::$key, $messages);
    }

    public static function all(bool $destroy = true) : array {

        $session = Session::getInstance();
        $messages = $session->has(self::$key) ? $session->get(self::$key) : [];

        if($destroy)
            $session->remove(self::$key);

        return $messages;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function save() {
        self::add($this);
    }

    public function draw() : void {
        echo "<div class='alert alert-{$this->type}'>{$this->message}</div>";
    }

}