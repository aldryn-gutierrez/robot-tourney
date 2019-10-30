<?php

namespace App\Transformers;

use App\Models\Battle;
use League\Fractal\TransformerAbstract;

class BattleTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'challengers',
    ];

    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Battle $battle)
    {
        return [
            'id' => $battle->id,
            'location' => $battle->location,
            'created_at' => (string) $battle->created_at,
            'updated_at' => (string) $battle->updated_at,
        ];
    }

    /**
     * Include Challengers
     *
     * @param  App\Model\Battle battle
     * @return \League\Fractal\Resource\Collection
     */
    public function includeChallengers(Battle $battle)
    {
        return $this->collection($battle->challengers, new ChallengerTransformer(), false);
    }
}
