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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'speed' => 'float',
        'weight' => 'float',
        'power' => 'float',
    ];

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

    /**
     * Get the damage this robot can incur.
     */
    public function getAttackPoints()
    {
        return $this->power + $this->weight + $this->speed;
    }

    /**
     * Get all the Challenger Records related to Robot
     */
    public function challengers()
    {
        return $this->hasMany('App\Models\Challenger', 'robot_id', 'id');
    }
}
