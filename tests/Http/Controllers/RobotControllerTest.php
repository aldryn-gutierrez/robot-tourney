<?php

namespace Tests\Http\Controllers;

use Tests\TestCase;
use App\Libraries\Spreadsheets\SpoutSpreadsheetHelper;
use App\Models\Robot;
use App\Models\User;
use App\Models\UserRobot;
use App\Repositories\RobotRepository;
use Illuminate\Http\UploadedFile;

class RobotControllerTest extends TestCase
{
    protected function createUser($name)
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

    public function testIndexValidationError()
    {
        $this->get("api/robot?limit=wer2342&page=23222fsd@s2", []);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'limit' => ['The limit must be an integer.'],
            'page' => ['The page must be an integer.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testIndexCapturesException()
    {
        $mockedRepository = \Mockery::mock(RobotRepository::class);
        $mockedRepository->shouldReceive('paginate')->andThrow(new \Exception('Foo Bar'));
        $this->app->instance(RobotRepository::class, $mockedRepository);

        $this->get("api/robot", []);
        $this->seeStatusCode(409);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 409,
                'message' => 'Getting Robots encountered an Unexpected Error',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testIndexSuccess()
    {
        $robot = $this->createRobot('John', 'Golem');
        $user = $robot->user();

        $expectedData = [
            'data' => [
                [
                    'id' => $robot->id,
                    'name' => $robot->name,
                    'weight' => $robot->weight,
                    'power' => $robot->power,
                    'speed' => $robot->speed,
                    'created_at' => (string) $robot->created_at,
                    'updated_at' => (string) $robot->updated_at,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => (string) $user->created_at,
                        'updated_at' => (string) $user->updated_at,
                    ]
                ]
            ]
        ];

        $this->get("api/robot/", []);
        $this->seeStatusCode(200);
        $responseData = $this->response->getOriginalContent();

        $this->assertEquals($responseData, $expectedData);
    }

    public function testIndexSuccessWithPageAndLimit()
    {
        $robot1 = $this->createRobot('user1', 'robot1');
        $user1 = $robot1->user();

        $robot2 = $this->createRobot('user2', 'robot2');
        $user2 = $robot2->user();

        $robot3 = $this->createRobot('user3', 'robot3');
        $user3 = $robot3->user();

        $robot4 = $this->createRobot('user4', 'robot4');
        $user4 = $robot4->user();

        $expectedData = [
            'data' => [
                [
                    'id' => $robot3->id,
                    'name' => $robot3->name,
                    'weight' => $robot3->weight,
                    'power' => $robot3->power,
                    'speed' => $robot3->speed,
                    'created_at' => (string) $robot3->created_at,
                    'updated_at' => (string) $robot3->updated_at,
                    'user' => [
                        'id' => $user3->id,
                        'name' => $user3->name,
                        'email' => $user3->email,
                        'created_at' => (string) $user3->created_at,
                        'updated_at' => (string) $user3->updated_at,
                    ]
                    ],
                [
                    'id' => $robot4->id,
                    'name' => $robot4->name,
                    'weight' => $robot4->weight,
                    'power' => $robot4->power,
                    'speed' => $robot4->speed,
                    'created_at' => (string) $robot4->created_at,
                    'updated_at' => (string) $robot4->updated_at,
                    'user' => [
                        'id' => $user4->id,
                        'name' => $user4->name,
                        'email' => $user4->email,
                        'created_at' => (string) $user4->created_at,
                        'updated_at' => (string) $user4->updated_at,
                    ]
                ]
            ]
        ];

        $this->get("api/robot?limit=2&page=2", []);
        $this->seeStatusCode(200);
        $responseData = $this->response->getOriginalContent();

        $this->assertEquals($responseData, $expectedData);
    }

    public function testShowRobotNotFound()
    {
        $this->get("api/robot/3123", []);
        $this->seeStatusCode(404);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 404,
                'message' => 'Robot not found',
            ]
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testShowRobotCatchesException()
    {
        $mockedRepository = \Mockery::mock(RobotRepository::class);
        $mockedRepository->shouldReceive('first')->andThrow(new \Exception('Foo Bar'));
        $this->app->instance(RobotRepository::class, $mockedRepository);

        $robot = $this->createRobot('user1', 'robot1');

        $this->get("api/robot/".$robot->getKey(), []);
        $this->seeStatusCode(409);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 409,
                'message' => 'Showing Robot encountered an Unexpected Error',
            ]
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testShowRobotSuccess()
    {
        $robot = $this->createRobot('user1', 'robot1');
        $user = $robot->user();

        $this->get("api/robot/".$robot->getKey(), []);
        $this->seeStatusCode(200);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'data' => [
                'id' => $robot->id,
                'name' => $robot->name,
                'weight' => $robot->weight,
                'power' => $robot->power,
                'speed' => $robot->speed,
                'created_at' => (string) $robot->created_at,
                'updated_at' => (string) $robot->updated_at,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => (string) $user->created_at,
                    'updated_at' => (string) $user->updated_at,
                ]
            ]
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testStoreValidationError()
    {
        $this->post("api/robot", []);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'name' => ['The name field is required.'],
            'weight' => ['The weight field is required.'],
            'power' => ['The power field is required.'],
            'speed' => ['The speed field is required.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testStoreCatchesException()
    {
        $mockedRepository = \Mockery::mock(RobotRepository::class);
        $mockedRepository->shouldReceive('create')->andThrow(new \Exception('Foo Bar'));
        $this->app->instance(RobotRepository::class, $mockedRepository);

        $authenticatedUser = $this->createUser('Cashmere');
        $dataToCreate = [
            'name' => 'Phoenix',
            'weight' => 134.32,
            'power' => 2.33,
            'speed' => 2.31,
        ];

        $this->actingAs($authenticatedUser);
        $this->post("api/robot", $dataToCreate);
        $this->seeStatusCode(409);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 409,
                'message' => 'Robot creation encountered an Unexpected Error',
            ]
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testStoreSuccess()
    {
        $authenticatedUser = $this->createUser('Cashmere');
        $dataToCreate = [
            'name' => 'Phoenix',
            'weight' => 134.32,
            'power' => 2.33,
            'speed' => 2.31,
        ];

        $this->actingAs($authenticatedUser);
        $this->post("api/robot", $dataToCreate);
        $this->seeStatusCode(201);
        $responseData = $this->response->getOriginalContent();

        $robot = Robot::where('name', $dataToCreate['name'])->first();
        $user = $robot->user();

        $this->assertNotNull($robot);
        $this->assertEquals($authenticatedUser->getKey(), $user->getKey());

        $expectedData = [
            'data' => [
                'id' => $robot->id,
                'name' => $robot->name,
                'weight' => (float) $robot->weight,
                'power' => (float) $robot->power,
                'speed' => (float) $robot->speed,
                'created_at' => (string) $robot->created_at,
                'updated_at' => (string) $robot->updated_at,
            ]
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testStoreBySpreadsheetValidationError()
    {
        $this->post("api/robot/uploadSpreadsheet", []);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'robot_spreadsheet' => ['The robot spreadsheet field is required.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testStoreBySpreadsheetContainsMoreThanOneSheet()
    {
        $mockedHelper = \Mockery::mock(SpoutSpreadsheetHelper::class)->makePartial();
        $mockedHelper->shouldReceive('createReaderFromStream')->andReturn(true);
        $mockedHelper->shouldReceive('getSheets')->andReturn(new \ArrayObject([1, 2, 3]));
        $this->app->instance(SpoutSpreadsheetHelper::class, $mockedHelper);

        $this->post("api/robot/uploadSpreadsheet", [
            'robot_spreadsheet' => UploadedFile::fake()->create('document.csv', 99)
        ]);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 422,
                'message' => 'Spreadsheet contains more than one sheet, please combine in one sheet'
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }
}
