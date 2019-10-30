<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRobot extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_robots';
}
