<?php 

namespace App\Contracts\Repositories;

use App\Repositories\Criteria\Criteria;

interface CriteriaContract {

    /**
     * Sets flag whether Criteria must be considered 
     *
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true);

    /**
     * Get Criteria 
     *
     * @return mixed
     */
    public function getCriteria();

    /**
     * Get Model With Criteria 
     *
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria);

    /**
     * Insert new Criteria
     *
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria);

    /**
     * Apply Criteria to Model
     *
     * @return $this
     */
    public function applyCriteria();
}