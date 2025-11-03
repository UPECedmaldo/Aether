<?php

namespace Sae\Models\DataObject;

class Profile {

    private int $id;
    private string $userName;
    private string $avatar;
    private int $followerCount;
    private int $libraryCount;

    public function __construct(int $id, string $avatar, string $userName, int $followerCount, int $libraryCount) {
        $this->id = $id;
        $this->avatar = $avatar;
        $this->userName = $userName;
        $this->followerCount = $followerCount;
        $this->libraryCount = $libraryCount;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getAvatar(): string {
        return $this->avatar;
    }

    public function getUserName(): string {
        return $this->userName;
    }

    public function getFollowerCount(): int {
        return $this->followerCount;
    }

    public function getLibraryCount(): int {
        return $this->libraryCount;
    }

}