<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenger extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'challengers';

    /**
     * Get the Robot
     */
    public function robot()
    {
        return $this->hasOne('App\Models\Robot', 'id', 'robot_id');
    }
}
