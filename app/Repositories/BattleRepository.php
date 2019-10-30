<?php

namespace App\Repositories;

use App\Models\Battle;

class BattleRepository extends BaseRepository
{
    /**
     * Specify Model name
     *
     * @return string
     */
    public function getModelName()
    {
        return Battle::class;
    }
}
