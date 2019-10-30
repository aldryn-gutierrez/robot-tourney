<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Battle extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'battles';

    /**
     * Get all the Challengers
     */
    public function challengers()
    {
        return $this->hasMany('App\Models\Challenger', 'battle_id', 'id');
    }
}
