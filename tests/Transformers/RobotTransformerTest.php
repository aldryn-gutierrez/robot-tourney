<?php

namespace Tests\Transformers;

use Tests\TestCase;
use App\Models\Robot;
use App\Models\User;
use App\Models\UserRobot;
use App\Transformers\RobotTransformer;

class RobotTransformerTest extends TestCase
{
    private function createRobot()
    {
        $user = new User();
        $user->name = 'John Doe';
        $user->email = 'john@doe.com';
        $user->password = 'SECRET';
        $user->save();

        $robot = new Robot();
        $robot->name = 'Suezo';
        $robot->power = 1;
        $robot->speed = 2;
        $robot->weight = 1.25;
        $robot->save();

        $userRobot = new UserRobot();
        $userRobot->robot_id = $robot->getKey();
        $userRobot->user_id = $user->getKey();
        $userRobot->save();

        return $robot;
    }

    public function testTransform()
    {
        $robot = $this->createRobot();

        $expectedData = [
            'id' => $robot->id,
            'name' => $robot->name,
            'weight' => $robot->weight,
            'power' => $robot->power,
            'speed' => $robot->speed,
            'created_at' => (string) $robot->created_at,
            'updated_at' => (string) $robot->updated_at,
        ];

        $transformer = new RobotTransformer();
        $transformedData = $transformer->transform($robot);

        $this->assertEquals($transformedData, $expectedData);
    }

    public function testIncludeUser()
    {
        $robot = $this->createRobot();

        $transformer = new RobotTransformer();
        $item = $transformer->includeUser($robot);

        $this->assertInstanceOf(\League\Fractal\Resource\Item::class, $item);
        $this->assertEquals($robot->user(), $item->getData());
    }
}
