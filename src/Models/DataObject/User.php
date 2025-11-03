<?php

namespace Sae\Models\DataObject;

/**
 * Classe représentant un utilisateur
 */
class User extends ADataObject {

    private int $id;

    private string $pseudo;
    private string $name;
    private string $lastName;

    private string $email;
    private string $password;

    private string $creation, $lastLogin;

    private int $role;
    private ?string $profilePhoto;

    public function __construct(int $id, string $pseudo, string $name, string $lastName, string $email, string $password,
        string $creation, string $lastLogin, int $role = 0, ?string $profilePhoto = null) {
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->name = $name;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->creation = $creation;
        $this->lastLogin = $lastLogin;
        $this->role = $role;
        $this->profilePhoto = $profilePhoto;
    }

    public function toArray(): array {
        return [
            "id_utilisateur" => $this->id,
            "pseudo" => $this->pseudo,
            "prenom" => $this->name,
            "nom" => $this->lastName,
            "email" => $this->email,
            "mot_de_passe" => $this->password,
            "date_creation" => $this->creation,
            "date_connexion" => $this->lastLogin,
            "role" => $this->role,
            "photo_profil" => $this->profilePhoto
        ];
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getPseudo(): string {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): void {
        $this->pseudo = $pseudo;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void {
        $this->lastName = $lastName;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getCreation(): string {
        return $this->creation;
    }

    public function setCreation(string $creation): void {
        $this->creation = $creation;
    }

    public function getLastLogin(): string {
        return $this->lastLogin;
    }

    public function setLastLogin(string $lastLogin): void {
        $this->lastLogin = $lastLogin;
    }
    /**
     * Obtient le rôle de l'utilisateur
     * @return int
     */
    public function getRole(): int {
        return $this->role;
    }

    public function setRole(int $role): void {
        $this->role = $role;
    }

    public function getProfilePhoto(): ?string {
        return $this->profilePhoto;
    }

    public function setProfilePhoto(?string $profilePhoto): void {
        $this->profilePhoto = $profilePhoto;
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     * @return bool
     */
    public function isAdmin(): bool {
        return $this->role === 1;
    }

}