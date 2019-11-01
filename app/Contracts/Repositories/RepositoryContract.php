<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface RepositoryContract
{
    public function all(array $columns = ['*']);

    public function create(array $data);

    public function update(array $data, $id);

    public function delete($id);

    public function count();

    public function first();

    public function paginate($limit = 100, $page = 1, array $columns = ['*']);
}
