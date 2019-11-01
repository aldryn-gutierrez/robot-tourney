<?php

namespace Tests\Transformers;

use Tests\TestCase;
use App\Models\Battle;
use App\Models\Challenger;
use App\Models\Robot;
use App\Models\User;
use App\Transformers\ChallengerTransformer;

class ChallengerTransformerTest extends TestCase
{
    public function testTransform()
    {
        $user = new User();
        $user->name = 'John Doe';
        $user->email = 'john@doe.com';
        $user->password = 'SECRET';
        $user->save();

        $battle = new Battle();
        $battle->location = 'Shinjuku';
        $battle->save();

        $robot = new Robot();
        $robot->name = 'Suezo';
        $robot->power = 1;
        $robot->speed = 2;
        $robot->weight = 1.25;
        $robot->save();

        $challenger = new Challenger();
        $challenger->robot_id = $robot->getKey();
        $challenger->user_id = $user->getKey();
        $challenger->battle_id = $battle->getKey();
        $challenger->is_victorious = true;
        $challenger->is_initiator = false;
        $challenger->save();

        $expectedData = [
            'id' => $challenger->id,
            'robot_id' => $challenger->robot_id,
            'user_id' => $challenger->user_id,
            'battle_id' => $challenger->battle_id,
            'is_victorious' => $challenger->is_victorious,
            'is_initiator' => $challenger->is_initiator,
            'created_at' => (string) $challenger->created_at,
            'updated_at' => (string) $challenger->updated_at,
        ];

        $transformer = new ChallengerTransformer();
        $transformedData = $transformer->transform($challenger);

        $this->assertEquals($transformedData, $expectedData);
    }
}
