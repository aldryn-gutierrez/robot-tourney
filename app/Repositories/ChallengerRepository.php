<?php

namespace App\Repositories;

use App\Models\Challenger;
use App\Repositories\Criteria\BetweenCriteria;
use App\Repositories\Criteria\GenericCriteria;

class ChallengerRepository extends BaseRepository
{
    /**
     * Specify Model name
     *
     * @return string
     */
    public function getModelName()
    {
        return Challenger::class;
    }

    public function countChallenges($robotId, $isInitiator = null, $startDate = null, $endDate = null)
    {
        $this->resetScope()->pushCriteria(new GenericCriteria(['robot_id' => $robotId]));

        if (!is_null($isInitiator)) {
            $this->pushCriteria(new GenericCriteria(['is_initiator' => $isInitiator]));
        }

        if (!is_null($startDate) && !is_null($endDate)) {
            $this->pushCriteria(new BetweenCriteria('created_at', [$startDate, $endDate]));
        }

        return $this->count();
    }
}
