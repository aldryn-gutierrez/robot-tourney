<?php

namespace App\Transformers;

use App\Models\Robot;
use League\Fractal\TransformerAbstract;

class RobotTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'user',
    ];

    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Robot $robot)
    {
        return [
            'id' => $robot->id,
            'name' => $robot->name,
            'weight' => (float) $robot->weight,
            'power' => (float) $robot->power,
            'speed' => (float) $robot->speed,
            'created_at' => (string) $robot->created_at,
            'updated_at' => (string) $robot->updated_at,
        ];
    }


    /**
     * Include User
     *
     * @param  App\Models\Robot  $robot
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(Robot $robot)
    {
        return $this->item($robot->user(), new UserTransformer(), false);
    }
}
