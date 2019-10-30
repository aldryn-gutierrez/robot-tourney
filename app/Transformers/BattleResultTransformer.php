<?php

namespace App\Transformers;

use App\Models\Battle;
use League\Fractal\TransformerAbstract;

class BattleResultTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Battle $battle)
    {
        $challengers = $battle->challengers;

        $winningRobot = ($challengers->where('is_victorious', true)->first())->robot;
        $defeatedRobot = ($challengers->where('is_victorious', false)->first())->robot;

        return [
            'id' => $battle->id,
            'location' => $battle->location,
            'winning_robot' => [
                'id' => $winningRobot->id,
                'name' => $winningRobot->name,
                'weight' => $winningRobot->weight,
                'power' => $winningRobot->power,
                'speed' => $winningRobot->speed,
            ],
            'defeated_robot' => [
                'id' => $defeatedRobot->id,
                'name' => $defeatedRobot->name,
                'weight' => $defeatedRobot->weight,
                'power' => $defeatedRobot->power,
                'speed' => $defeatedRobot->speed,
            ],
            'created_at' => (string) $battle->created_at,
            'updated_at' => (string) $battle->updated_at,
        ];
    }
}
