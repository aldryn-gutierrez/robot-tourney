<?php

namespace App\Repositories\Criteria;

use App\Repositories\Criteria\Criteria;
use App\Contracts\Repositories\RepositoryContract as Repository;

class IncludeCriteria extends Criteria
{
    protected $includes;

    public function __construct($includes)
    {
        $this->includes = $includes;
    }

    /**
     * Apply Criteria
     *
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->with($this->includes);
    }
}