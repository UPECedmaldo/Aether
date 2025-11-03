<?php

namespace Sae\Models\DataObject;

/**
 * Classe représentant une météothèque - WeatherLibrary -> WLibrary
 */
class WLibrary extends ADataObject {

    private int $id;
    private int $user;
    private string $name;
    private int $state;
    private string $creation;
    private string $color;

    public function __construct(int $id, int $user, string $name, $state, string $creation, string $color) {
        $this->id = $id;
        $this->user = $user;
        $this->name = $name;
        $this->state = $state;
        $this->creation = $creation;
        $this->color = $color;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUser(): int {
        return $this->user;
    }

    public function getName(): string {
        return $this->name;
    }

    public function isPublic() : bool {
        return $this->state == 1;
    }

    public function isPrivate() : bool {
        return $this->state == 0;
    }

    public function isShared() : bool { // Friends
        return $this->state == 2;
    }

    public function getState(): int {
        return $this->state;
    }

    public function getCreation(): string {
        return $this->creation;
    }

    public function getColor(): string {
        return $this->color;
    }

    public function toArray(): array {
        return [
            "id_meteotheque" => $this->id,
            "id_utilisateur" => $this->user,
            "nom" => $this->name,
            "creation" => $this->creation,
            "couleur" => $this->color
        ];
    }

}