<?php

namespace Sae\Models\DataObject;

use DateTime;

class FriendShip extends ADataObject {

    private int $fromId;
    private int $toId;
    private DateTime $since;

    public function __construct(int $fromId, int $toId, DateTime $since) {
        $this->fromId = $fromId;
        $this->toId = $toId;
        $this->since = $since;
    }

    public function toArray(): array {
        return [
            'id_utilisateur_1' => $this->fromId,
            'id_utilisateur_2 ' => $this->toId,
            'date_ajout' => $this->since->format('Y-m-d H:i:s')
        ];
    }

    public function getFromId(): int {
        return $this->fromId;
    }

    public function getToId(): int {
        return $this->toId;
    }

    public function getSince(): DateTime {
        return $this->since;
    }

}