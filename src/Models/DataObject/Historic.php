<?php

namespace Sae\Models\DataObject;

use DateTime;
use Sae\Models\Repository\StationRepository;

/**
 * Représente un élément de l'historique
 * Une station visualisée, une mesure observée, etc.
 */
class Historic extends ADataObject {

    private int $id;
    private int $user;
    private string $type;
    private $reference;
    private DateTime $date;

    public function __construct(int $id, int $user, string $type, $reference, DateTime $date) {
        $this->id = $id;
        $this->user = $user;
        $this->type = $type;
        $this->reference = $reference;
        $this->date = $date;
    }

    public function toArray(): array {
        return [
            'id_recent' => $this->id,
            'id_utilisateur' => $this->user,
            'type' => $this->type,
            'reference_id' => $this->reference,
            'date_ajout' => $this->date->format('Y-m-d H:i:s')
        ];
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUser(): int {
        return $this->user;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getReference() {
        return $this->reference;
    }

    public function getDate(): DateTime {
        return $this->date;
    }

    public function getFormattedDate() : string{
        return $this->date->format('d/m/Y H:i:s');
    }

    public function getReferenceName() : string {

        if($this->type != "station")
            return "Pas encore implémenté";

        $stationRepository = new StationRepository();
        $station = $stationRepository->select($this->reference);
        return $station->getName();
    }

}