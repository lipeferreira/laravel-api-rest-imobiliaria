<?php

namespace App\Repository;

class RealStateRepository extends AbstractRepository
{
    private $location;

    public function setLocation(array $data) : self
    {
        $this->location = $data;
        return $this;
    }

    public function getResult()
    {
        $location = $this->location;
        return $this->model->whereHas('adress', function($query) use($location){
            $query->where('state_id', $location['state'])->where('city_id', $location['city']);
        });
    }
}
