<?php

namespace App\Repositories\Criteria;

use App\Repositories\Criteria\Criteria;
use App\Contracts\Repositories\RepositoryContract as Repository;

class BetweenCriteria extends Criteria
{
    protected $field;

    protected $values;

    public function __construct(string $field, array $values)
    {
        $this->field = $field;
        $this->values = $values;
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
        $model = $model->whereBetween($repository->getTableName().'.'.$this->field, $this->values);

        return $model;
    }
}
