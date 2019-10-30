<?php

namespace App\Transformers;

use App\Models\Challenger;
use League\Fractal\TransformerAbstract;

class ChallengerTransformer extends TransformerAbstract
{

    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Challenger $challenger)
    {
        return [
            'id' => $challenger->id,
            'robot_id' => $challenger->robot_id,
            'user_id' => $challenger->user_id,
            'battle_id' => $challenger->battle_id,
            'is_victorious' => $challenger->is_victorious,
            'is_initiator' => $challenger->is_initiator,
            'created_at' => (string) $challenger->created_at,
            'updated_at' => (string) $challenger->updated_at,
        ];
    }
}
