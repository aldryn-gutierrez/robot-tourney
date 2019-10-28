<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Robot extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'robots';

    /**
     * The users that belong to the robot.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_robots')
            ->using(UserRobot::class);
    }

    /**
     * The users that belong to the robot.
     */
    public function user()
    {
        return $this->users()->first();
    }
}
