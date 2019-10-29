<?php

namespace App\Repositories;

use App\Models\Robot;
use App\Repositories\Criteria\GenericCriteria;

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
}
