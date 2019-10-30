<?php

namespace App\Repositories\Criteria;

use App\Repositories\Criteria\Criteria;
use App\Contracts\Repositories\RepositoryContract as Repository;

class OrderCriteria extends Criteria
{
    protected $field;

    protected $order;

    public function __construct($field, $order)
    {
        $this->field = $field;
        $this->order = $order;
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
        return $model->orderBy($this->field, $this->order);
    }
}
