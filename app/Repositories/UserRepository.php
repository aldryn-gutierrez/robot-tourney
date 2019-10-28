<?php 

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    /**
     * Specify Model name
     *
     * @return string
     */
    public function getModelName()
    {
        return User::class;
    }
}
