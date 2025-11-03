<?php

namespace Sae\Models\DataObject;

use JsonSerializable;
use Sae\Models\Repository\CityRepository;
use Sae\Models\Repository\CodeNameRepository;
use Sae\Models\Repository\DepartmentRepository;
use Sae\Models\Repository\EpciRepository;
use Sae\Models\Repository\RegionRepository;
use Sae\Models\Repository\StationRepository;


/**
 * Classe représentant une station
 */
class Station extends ADataObject implements JsonSerializable {

    private int $id;

    private string $code;

    private $region;
    private $department;
    private $city;
    private $epci;

    public function __construct(int $id, string $code, $region, $department, $city, $epci) {
        $this->id = $id;
        $this->code = $code;
        $this->region = $region;
        $this->department = $department;
        $this->city = $city;
        $this->epci = $epci;
    }

    public function toArray(): array {
        return [
            "id_station" => $this->id,
            "code_station" => $this->code,
            "code_region" => $this->region,
            "code_departement" => $this->department,
            "code_commune" => $this->city,
            "code_epci" => $this->epci
        ];
    }

    public function getId(): int {
        return $this->id;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function getName(): string {
        return (new StationRepository())->nameOf($this->id);
    }

    public function getRegion(): mixed {
        return $this->region;
    }

    public function getRegionName() : string {
        return self::getNameOf(new RegionRepository(), $this->region);
    }

    public function getDepartment(): mixed {
        return $this->department;
    }

    public function getDepartmentName() : string {
        return self::getNameOf(new DepartmentRepository(), $this->department);
    }

    public function getCity(): mixed {
        return $this->city;
    }

    public function getCityName() : string {
        return self::getNameOf(new CityRepository(), $this->city);
    }

    public function getEpci(): int {
        return $this->epci;
    }

    public function getEpciName() : string {
        return self::getNameOf(new EpciRepository(), $this->epci);
    }

    /**
     * Retourne le nom d'un objet de données
     * @param CodeNameRepository $repository
     * @param int $code
     * @return string
     */
    private static function getNameOf(CodeNameRepository $repository, int $code) : string {

        if($code == null)
            return "Inconnu(e)";

        return $repository->select($code)->getName();
    }

    public function jsonSerialize() : array {
        return [
            "id" => $this->id,
            "code" => $this->code,
            "name" => $this->name,
            "region" => $this->getRegionName(),
            "department" => $this->getDepartmentName(),
            "city" => $this->getCityName(),
            "epci" => $this->getEpciName()
        ];
    }

}