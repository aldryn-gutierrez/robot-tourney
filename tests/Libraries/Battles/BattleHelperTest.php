<?php

namespace Tests\Transformers;

use Tests\TestCase;
use App\Libraries\Battles\BattleHelper;
use App\Models\Robot;
use App\Models\User;
use App\Models\UserRobot;

class BattleHelperTest extends TestCase
{
    private function createRobot($name, $robotName, $power, $speed, $weight)
    {
        $user = new User();
        $user->name = $name;
        $user->email = $name.'@email.com';
        $user->password = 'SECRET';
        $user->save();

        $robot = new Robot();
        $robot->name = $robotName;
        $robot->power = $power;
        $robot->speed = $speed;
        $robot->weight = $weight;
        $robot->save();

        $userRobot = new UserRobot();
        $userRobot->robot_id = $robot->getKey();
        $userRobot->user_id = $user->getKey();
        $userRobot->save();

        return $robot;
    }

    public function holdTournamentProvider()
    {
        return [
            'robot1-has-higher-attack' => ['20.2', '10.2', '5.2', '1.2', '2.2', '15.2', 0],
            'robot2-has-higher-attack' => ['2.2', '1.2', '5.2', '40.2', '20.2', '15.2', 1],
            'both-robot-same-attack' => ['20.2', '10.2', '5.2', '20.2', '10.2', '5.2', 0],
        ];
    }

    /**
     * @dataProvider holdTournamentProvider
     */
    public function testHoldTournament(
        $robotPower1, 
        $robotSpeed1, 
        $robotWeight1,
        $robotPower2, 
        $robotSpeed2, 
        $robotWeight2,
        $winningRobotIndex
    ) {
        $robot1 = $this->createRobot('John', 'Leviathan', $robotPower1, $robotSpeed1, $robotWeight1);
        $robot2 = $this->createRobot('Mark', 'Shiva', $robotPower2, $robotSpeed2, $robotWeight2);

        $robots = collect([$robot1, $robot2]); 
        $expectedWinner = $robots[$winningRobotIndex];

        $battleHelper = new BattleHelper();
        $winningRobot = $battleHelper->holdTournament($robots);

        $this->assertEquals($expectedWinner->getKey(), $winningRobot->getKey());
    }
}