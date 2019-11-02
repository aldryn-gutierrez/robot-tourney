<?php

namespace Tests\Http\Controllers;

use Tests\TestCase;
use Auth;
use App\Models\Battle;
use App\Models\Challenger;
use App\Models\Robot;
use App\Models\User;
use App\Models\UserRobot;
use App\Repositories\BattleRepository;
use App\Repositories\ChallengerRepository;
use App\Repositories\RobotRepository;

class BattleControllerTest extends TestCase
{
    private function createUser($name)
    {
        $user = new User();
        $user->name = $name;
        $user->email = $name.'@email.com';
        $user->password = 'SECRET';
        $user->save();

        return $user;
    }

    private function createRobot($userName, $robotName)
    {
        $user = $this->createUser($userName);

        $robot = new Robot();
        $robot->name = $robotName;
        $robot->power = 1.35;
        $robot->speed = 2.44;
        $robot->weight = 1.25;
        $robot->save();

        $userRobot = new UserRobot();
        $userRobot->robot_id = $robot->getKey();
        $userRobot->user_id = $user->getKey();
        $userRobot->save();

        return $robot;
    }

    public function testFightValidationError()
    {
        $this->post("api/battle/fight", []);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'location' => ['The location field is required.'],
            'robot_id' => ['The robot id field is required.'],
            'opponent_robot_id' => ['The opponent robot id field is required.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testFightRobotDoesNotBelongToUser()
    {
        $robot = $this->createRobot('John', 'Mochi');
        $user = $robot->user();

        $opponentRobot = $this->createRobot('Jack', 'Pixie');
        $anotherRobot = $this->createRobot('Mary', 'Suezo');

        $this->actingAs($user);
        $this->post("api/battle/fight", [
            'location' => 'Shinjuku',
            'robot_id' => $anotherRobot->getKey(),
            'opponent_robot_id' => $opponentRobot->getKey(),
        ]);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 422,
                'message' => 'Robot selected does not belong to you',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testFightRobotReachedMaxBattleLimit()
    {
        $mockedRepository = \Mockery::mock(ChallengerRepository::class);
        $mockedRepository->shouldReceive('countChallenges')->andReturn(10);
        $this->app->instance(ChallengerRepository::class, $mockedRepository);

        $robot = $this->createRobot('John', 'Mochi');
        $user = $robot->user();

        $opponentRobot = $this->createRobot('Jack', 'Pixie');

        $this->actingAs($user);
        $this->post("api/battle/fight", [
            'location' => 'Shinjuku',
            'robot_id' => $robot->getKey(),
            'opponent_robot_id' => $opponentRobot->getKey(),
        ]);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 422,
                'message' => 'Robot has fought 5 battles already. Please try tomorrow!',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testFightOpponentRobotReachedMaxBattleLimit()
    {
        $mockedRepository = \Mockery::mock(ChallengerRepository::class);
        $mockedRepository->shouldReceive('countChallenges')->andReturn(0, 2);
        $this->app->instance(ChallengerRepository::class, $mockedRepository);

        $robot = $this->createRobot('John', 'Mochi');
        $user = $robot->user();

        $opponentRobot = $this->createRobot('Jack', 'Pixie');

        $this->actingAs($user);
        $this->post("api/battle/fight", [
            'location' => 'Shinjuku',
            'robot_id' => $robot->getKey(),
            'opponent_robot_id' => $opponentRobot->getKey(),
        ]);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 422,
                'message' => 'Opponent Robot has already been challenged for today!',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testFightCatchesException()
    {
        $mockedRepository = \Mockery::mock(ChallengerRepository::class);
        $mockedRepository->shouldReceive('insert')->andThrow(new \Exception('Foo Bar'));
        $this->app->instance(ChallengerRepository::class, $mockedRepository);

        $robot = $this->createRobot('John', 'Mochi');
        $user = $robot->user();

        $opponentRobot = $this->createRobot('Jack', 'Pixie');

        $this->actingAs($user);
        $this->post("api/battle/fight", [
            'location' => 'Shinjuku',
            'robot_id' => $robot->getKey(),
            'opponent_robot_id' => $opponentRobot->getKey(),
        ]);
        $this->seeStatusCode(409);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 409,
                'message' => 'Robot Fight encountered an Unexpected Error',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testFightSuccess()
    {
        $robot = $this->createRobot('John', 'Mochi');
        $user = $robot->user();

        $opponentRobot = $this->createRobot('Jack', 'Pixie');

        $dataToCreate = [
            'location' => 'Shinjuku',
            'robot_id' => $robot->getKey(),
            'opponent_robot_id' => $opponentRobot->getKey(),
        ];

        $this->actingAs($user);
        $this->post("api/battle/fight", $dataToCreate);
        $this->seeStatusCode(200);
        $responseData = $this->response->getOriginalContent();

        $battle = Battle::where('location', $dataToCreate['location'])->orderBy('id', 'desc')->first();
        $challengers = $battle->challengers;
        $firstChallenger = $challengers[0];
        $secondChallenger = $challengers[1];

        $this->assertEquals(2, $challengers->count());
        $this->assertEquals($robot->getKey(), $firstChallenger->robot_id);
        $this->assertEquals($opponentRobot->getKey(), $secondChallenger->robot_id);

        $expectedData = [
            'data' => [
                'id' => $battle->getKey(),
                'location' => $battle->location,
                'created_at' => (string) $battle->created_at,
                'updated_at' => (string) $battle->updated_at,
                'challengers' => [
                    [
                        'id' => $firstChallenger->getKey(),
                        'robot_id' => $firstChallenger->robot_id,
                        'user_id' => $firstChallenger->user_id,
                        'battle_id' => $firstChallenger->battle_id,
                        'is_victorious' => $firstChallenger->is_victorious,
                        'is_initiator' => $firstChallenger->is_initiator,
                        'created_at' => (string) $firstChallenger->created_at,
                        'updated_at' => (string) $firstChallenger->updated_at,
                    ],
                    [
                        'id' => $secondChallenger->getKey(),
                        'robot_id' => $secondChallenger->robot_id,
                        'user_id' => $secondChallenger->user_id,
                        'battle_id' => $secondChallenger->battle_id,
                        'is_victorious' => $secondChallenger->is_victorious,
                        'is_initiator' => $secondChallenger->is_initiator,
                        'created_at' => (string) $secondChallenger->created_at,
                        'updated_at' => (string) $secondChallenger->updated_at,
                    ],
                ]
            ]
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    



    public function testResultsValidationError()
    {
        $this->get("api/battle/results?limit=wer2342&page=23222fsd@s2", []);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'limit' => ['The limit must be an integer.'],
            'page' => ['The page must be an integer.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testResultsCapturesException()
    {
        $mockedRepository = \Mockery::mock(BattleRepository::class);
        $mockedRepository->shouldReceive('paginate')->andThrow(new \Exception('Foo Bar'));
        $this->app->instance(BattleRepository::class, $mockedRepository);

        $this->get("api/battle/results", []);
        $this->seeStatusCode(409);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 409,
                'message' => 'Battle Results encountered an Unexpected Error',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    private function createChallenger($userId, $robotId, $battleId, $isVictorious, $isInitiator)
    {
        $challenger = new Challenger();
        $challenger->user_id = $userId;
        $challenger->robot_id = $robotId;
        $challenger->battle_id = $battleId;
        $challenger->is_victorious = $isVictorious;
        $challenger->is_initiator = $isInitiator;
        $challenger->save();

        return $challenger;
    }
    
    private function createBattle($location, $name, $opponentName)
    {
        $battle = new Battle();
        $battle->location = $location;
        $battle->save();

        $robot1 = $this->createRobot($name, $name.'Robot');
        $user1 = $robot1->user();
        $challenger1 = $this->createChallenger(
            $user1->getKey(),
            $robot1->getKey(),
            $battle->getKey(),
            1,
            1
        );

        $robot2 = $this->createRobot($opponentName, $opponentName.'Robot');
        $user2 = $robot2->user();
        $challenger2 = $this->createChallenger(
            $user2->getKey(),
            $robot2->getKey(),
            $battle->getKey(),
            0,
            0
        );

        return $battle;
    }

    public function testResultsSuccess()
    {
        $battle1 = $this->createBattle('Okinawa', 'Dog', 'Cat');
        $challengers1 = $battle1->challengers;
        $robot1 = $challengers1[0]->robot;
        $robot2 = $challengers1[1]->robot;

        $battle2 = $this->createBattle('Kyoto', 'Gundam', 'Beyblade');
        $challengers2 = $battle2->challengers;
        $robot3 = $challengers2[0]->robot;
        $robot4 = $challengers2[1]->robot;
        
        $this->get("api/battle/results", []);
        $this->seeStatusCode(200);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'data' => [
                [
                    'id' => $battle2->getKey(),
                    'location' => $battle2->location,
                    'winning_robot' => [
                        'id' => $robot3->getKey(),
                        'name' => $robot3->name,
                        'weight' => $robot3->weight,
                        'power' => $robot3->power,
                        'speed' => $robot3->speed,
                    ],
                    'defeated_robot' => [
                        'id' => $robot4->getKey(),
                        'name' => $robot4->name,
                        'weight' => $robot4->weight,
                        'power' => $robot4->power,
                        'speed' => $robot4->speed,
                    ],
                    'created_at' => (string) $battle2->created_at,
                    'updated_at' => (string) $battle2->updated_at,
                ],

                [
                    'id' => $battle1->getKey(),
                    'location' => $battle1->location,
                    'winning_robot' => [
                        'id' => $robot1->getKey(),
                        'name' => $robot1->name,
                        'weight' => $robot1->weight,
                        'power' => $robot1->power,
                        'speed' => $robot1->speed,
                    ],
                    'defeated_robot' => [
                        'id' => $robot2->getKey(),
                        'name' => $robot2->name,
                        'weight' => $robot2->weight,
                        'power' => $robot2->power,
                        'speed' => $robot2->speed,
                    ],
                    'created_at' => (string) $battle1->created_at,
                    'updated_at' => (string) $battle1->updated_at,
                ]
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testLeaderboardValidationError()
    {
        $this->get("api/battle/leaderboard?limit=wer2342&page=23222fsd@s2", []);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'limit' => ['The limit must be an integer.'],
            'page' => ['The page must be an integer.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testLeaderboardCapturesException()
    {
        $mockedRepository = \Mockery::mock(RobotRepository::class);
        $mockedRepository->shouldReceive('getLeaderboard')->andThrow(new \Exception('Foo Bar'));
        $this->app->instance(RobotRepository::class, $mockedRepository);

        $this->get("api/battle/leaderboard", []);
        $this->seeStatusCode(409);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 409,
                'message' => 'Battle Results encountered an Unexpected Error',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testLeaderboardSuccess()
    {
        $battle = $this->createBattle('Okinawa', 'Dog', 'Cat');
        $challengers = $battle->challengers;
        $robot1 = $challengers[0]->robot;
        $robot2 = $challengers[1]->robot;

        $robot3 = $this->createRobot('Joe', 'Fish');

        $this->get("api/battle/leaderboard", []);
        $this->seeStatusCode(200);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'data' => [
                [
                    'id' => $robot1->getKey(),
                    'name' => $robot1->name,
                    'battle_count' => 1,
                    'winning_count' => 1,
                    'losing_count' => 0,
                ],
                [
                    'id' => $robot2->getKey(),
                    'name' => $robot2->name,
                    'battle_count' => 1,
                    'winning_count' => 0,
                    'losing_count' => 1,
                ],
                [
                    'id' => $robot3->getKey(),
                    'name' => $robot3->name,
                    'battle_count' => 0,
                    'winning_count' => 0,
                    'losing_count' => 0,
                ],
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }
}
