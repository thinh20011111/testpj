<?php

namespace App\Services;

use function Symfony\Component\Translation\t;

class BaseService implements ServiceInterface
{
    public $repository;

    public function all()
    {
      return $this->repository->all();
    }

    public function find(int $id)
    {
       return $this->repository->find($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(array $data, $id)
    {
        return $this->repository->update($data, $id);
    }

    public function delete($id)
    {
       return $this->repository->delete($id);
    }

  public function searchAndPaginate($searchBy, $keyword, $perPage = 10){
    return $this->repository->searchAndPaginate($searchBy, $keyword, $perPage);
  }

}
