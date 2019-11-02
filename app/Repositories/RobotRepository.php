<?php

namespace App\Repositories;

use App\Models\Robot;
use App\Repositories\Criteria\GenericCriteria;
use DB;

class RobotRepository extends BaseRepository
{
    /**
     * Specify Model name
     *
     * @return string
     */
    public function getModelName()
    {
        return Robot::class;
    }

    public function findByIdAndUserId($id, $userId)
    {
        return $this->model->leftJoin('users_robots', 'robots.id', '=', 'users_robots.robot_id')
            ->where('robots.id', $id)
            ->where('users_robots.user_id', $userId)
            ->first();
    }

    public function delete($id)
    {
        $userRobotRepository = new UserRobotRepository();
        $userRobotRepository->pushCriteria(new GenericCriteria(['robot_id' => $id]))->deleteByCriteria();

        return parent::delete($id);
    }

    public function getLeaderboard($page, $limit)
    {
        return DB::table($this->tableName.' as r')
            ->select(
                DB::raw('
                    r.id,
                    r.name,
                    COUNT(c.battle_id) AS battle_count,
                    COUNT(CASE WHEN c.is_victorious = 1 THEN 1 END) as winning_count,
                    COUNT(CASE WHEN c.is_victorious = 0 THEN 1 END) as losing_count
                ')
            )
            ->leftJoin('challengers as c', 'r.id', '=', 'c.robot_id')
            ->groupBy('r.id', 'r.name')
            ->orderByRaw("`battle_count` DESC, `winning_count` DESC, `losing_count` DESC")
            ->simplePaginate($limit, ['*'], 'page', $page)
            ->items();
    }
}
