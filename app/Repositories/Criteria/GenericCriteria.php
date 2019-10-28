<?php

namespace App\Repositories\Criteria;

use App\Repositories\Criteria\Criteria;
use App\Contracts\Repositories\RepositoryContract as Repository;

class GenericCriteria extends Criteria
{
    protected $fieldsAndValues;

    public function __construct($fieldsAndValues)
    {
        $this->fieldsAndValues = $fieldsAndValues;
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
        foreach ($this->fieldsAndValues as $field => $value) {
            if (is_array($value)) {
                $model = $model->whereIn($repository->getTableName().'.'.$field, $value);
            } else {
                $model = $model->where($repository->getTableName().'.'.$field, $value);
            }
        }

        return $model;
    }
}