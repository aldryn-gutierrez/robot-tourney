<?php 

namespace App\Repositories;

use App\Models\UserRobot;

class UserRobotRepository extends BaseRepository
{
    /**
     * Specify Model name
     *
     * @return string
     */
    public function getModelName()
    {
        return UserRobot::class;
    }
}
