<?php

namespace Sae\Controllers;

use Sae\Models\DataObject\WLibrary;
use Sae\Models\Http\Session;
use Sae\Models\Repository\FollowRepository;
use Sae\Models\Repository\UserRepository;
use Sae\Models\Repository\WLibraryRepository;

class ProfileController extends AController {

    public function __construct(){
        parent::__construct("profile");
    }

    public function index(array $path): bool {

        if(!isset($path[0]))
            return false;

        if(count($path) < 2)
            return false;

        if($path[1] == "follow") {
            $this->follow();
            return true;
        }

        return $this->show($path[1]);
    }

    private function show(string $profileSearch) : bool {

        $profileRepository = new UserRepository();
        $profile = $profileRepository->selectProfile($profileSearch);
        if($profile == null)
            return false;

        $user = $profileRepository->selectCurrent();

        $libraries = (new WLibraryRepository())->getVisibleFor($user?->getId(), $profile->getId());
        $followers = (new FollowRepository())->getFollowers($profile->getId());

        $this->loadView("profile", [
            "title" => "Profil de " . $profile->getUserName(),
            "profile" => $profile,
            "libraries" => $libraries,
            "followers" => $followers,
        ]);
        return true;
    }

    private function follow() : void {

        $targetId = $_POST['targetId'];
        if(!$this::requireLogin("/profile/" . $targetId))
            return;

        $userRepository = new UserRepository();
        $user = $userRepository->selectCurrent();
        if($user->getId() == $targetId) {
            $this->redirect("/profile/" . $targetId);
            return;
        }

        $followRepository = new FollowRepository();
        $following = $followRepository->isFollow($user->getId(), $targetId);
        if($following) {
            $followRepository->removeFollow($user->getId(), $targetId);
        } else {
            $followRepository->addFollow($user->getId(), $targetId);
        }

        $this->redirect("/profile/" . $targetId);
    }

}