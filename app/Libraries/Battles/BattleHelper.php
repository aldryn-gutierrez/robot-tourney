<?php

namespace App\Libraries\Battles;

class BattleHelper {
    /**
     * Returns the robot winning the tournament
     */
    public function holdTournament($robots)
    {
        $reigningRobot = $robots->shift();
        foreach ($robots as $robot) {
            if ($robot->getAttackPoints() > $reigningRobot->getAttackPoints()) {
                $reigningRobot = $robot;
            }
        }

        return $reigningRobot;
    }
}
