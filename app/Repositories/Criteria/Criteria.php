<?php 

namespace App\Repositories\Criteria;

use App\Contracts\Repositories\RepositoryContract as Repository;

abstract class Criteria 
{
    /**
     * Apply Criteria
     *
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public abstract function apply($model, Repository $repository);
}