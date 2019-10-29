<?php

namespace App\Repositories;

use App\Contracts\Repositories\CriteriaContract;
use App\Contracts\Repositories\RepositoryContract;
use App\Exceptions\RepositoryException;
use App\Repositories\Criteria\Criteria;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryContract, CriteriaContract
{
    protected $model;

    protected $tableName;

    protected $criteria;

    protected $skipCriteria = false;

    public function __construct()
    {
        $this->makeModel();
        $this->criteria = collect();
    }

    /**
     * Specify Model name
     *
     * @return string
     */
    abstract function getModelName();

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $modelName = $this->getModelName();
        if (!class_exists($modelName)) {
            throw new RepositoryException("$modelName is an invalid class");
        }

        $model = new $modelName;
        if (!$model instanceof Model) {

            throw new RepositoryException(
                "Class {$this->getModelName()} must be an instance of Illuminate\\Database\\Eloquent\\Model"
            );
        }

        $this->tableName = $model->getTable();
        return $this->model = $model;
    }

    /**
     * @param  array $columns
     * @return mixed
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * @return mixed
     */
    public function get()
    {
        $this->applyCriteria();
        return $this->model->get();
    }

    /**
     * @param  array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $this->makeModel();

        foreach ($data as $key => $value) {
            $this->model->$key = $value;
        }

        $this->model->save();

        return $this->model;
    }

    /**
     * @param  array $data
     * @param  $id
     * @param  string $attribute
     * @return mixed
     */
    public function update(array $data, $id, $attribute = "id", $returnFreshModel = true)
    {
        $this->model->where($attribute, '=', $id)->update($data);

        return $returnFreshModel ? $this->model->find($id) : null;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $this->resetScope();

        return $this->model->destroy($id);
    }

    public function deleteByCriteria()
    {
        $this->applyCriteria();
        return $this->model->delete();
    }

    /**
     * @return mixed
     **/
    public function first()
    {
        $this->applyCriteria();
        return $this->model->first();
    }

    public function chunk($count, callable $callback)
    {
        $this->applyCriteria();
        return $this->model->chunk($count, $callback);
    }

    /**
     * @return int
     **/
    public function count()
    {
        $this->applyCriteria();
        return $this->model->count();
    }

    public function insert(array $data)
    {
        return $this->model->insert($data);
    }

    public function existsByFields($fieldsAndValues, $id = null)
    {
        $model = $this->model;

        foreach ($fieldsAndValues as $field => $value) {
            if (is_array($value)) {
                $model = $model->whereIn($field, $value);
            } else {
                $model = $model->where($field, $value);
            }
        }

        if (!is_null($id)) {
            $model = $model->where('id', '!=', $id);
        }

        return $model->exists();
    }

    /**
     * @param  int     $limit
     * @param  int     $page
     * @param  array   $columns
     * @return mixed
     */
    public function paginate(
        $limit = 100,
        $page = 1,
        array $columns = ['*']
    ) {
        $this->applyCriteria();
        return $this->model->simplePaginate($limit, $columns, 'page', $page)->items();
    }

    /**
     * Reinitialize Repository Scope
     *
     * @return $this
     */
    public function resetScope() {
        $this->skipCriteria(false);
        $this->criteria = collect();
        $this->model = $this->makeModel();

        return $this;
    }

    /**
     * Sets flag whether Criteria must be considered
     *
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true){
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * Get Criteria
     *
     * @return mixed
     */
    public function getCriteria() {
        return $this->criteria;
    }

    /**
     * Get Model With Criteria
     *
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria) {
        $this->model = $criteria->apply($this->model, $this);
        return $this;
    }

    /**
     * Insert new Criteria
     *
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria) {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * Apply Criteria to Model
     *
     * @return $this
     */
    public function applyCriteria() {
        if ($this->skipCriteria === true) {
            return $this;
        }

        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof Criteria) {
                $this->model = $criteria->apply($this->model, $this);
            }
        }

        return $this;
    }

    /**
     * Return Repository Related Table
     *
     * @return $tableName
     **/
    public function getTableName()
    {
        return $this->tableName;
    }
}
