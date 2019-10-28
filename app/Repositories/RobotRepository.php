<?php 

namespace App\Repositories;

use App\Models\Robot;

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
}
