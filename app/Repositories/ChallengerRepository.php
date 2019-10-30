<?php

namespace App\Repositories;

use App\Models\Challenger;
use App\Repositories\Criteria\BetweenCriteria;
use App\Repositories\Criteria\GenericCriteria;
use DB;

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

    public function getLeaderboard($page, $limit)
    {
        return DB::table($this->tableName.' as c')
            ->select(
                DB::raw('
                    c.robot_id,
                    r.name,
                    COUNT(c.battle_id) AS battle_count,
                    COUNT(CASE WHEN c.is_victorious = 1 THEN 1 END) as winning_count,
                    COUNT(CASE WHEN c.is_victorious = 0 THEN 1 END) as losing_count
                ')
            )
            ->leftJoin('robots as r', 'r.id', '=', 'c.robot_id')
            ->groupBy('c.robot_id', 'r.name')
            ->orderByRaw("`battle_count` DESC, `winning_count` DESC, `losing_count` DESC")
            ->simplePaginate($limit, ['*'], 'page', $page)
            ->items();
    }
}
