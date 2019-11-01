<?php

namespace Tests\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Repositories\UserRepository;

class UserControllerTest extends TestCase
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

    public function testIndexValidationError()
    {
        $this->get("api/user?limit=wer2342&page=23222fsd@s2", []);
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
        $mockedRepository = \Mockery::mock(UserRepository::class);
        $mockedRepository->shouldReceive('paginate')->andThrow(new \Exception('Foo Bar'));
        $this->app->instance(UserRepository::class, $mockedRepository);

        $this->get("api/user", []);
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

    public function testIndexSuccess()
    {
        $user = $this->createUser('John');

        $expectedData = [
            'data' => [
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => (string) $user->created_at,
                    'updated_at' => (string) $user->updated_at,
                ]
            ]
        ];

        $this->get("api/user/", []);
        $this->seeStatusCode(200);
        $responseData = $this->response->getOriginalContent();

        $this->assertEquals($responseData, $expectedData);
    }

    public function testIndexSuccessWithPageAndLimit()
    {
        $user1 = $this->createUser('First');
        $user2 = $this->createUser('Second');
        $user3 = $this->createUser('Third');
        $user4 = $this->createUser('Fourth');

        $expectedData = [
            'data' => [
                [
                    'id' => $user3->id,
                    'name' => $user3->name,
                    'email' => $user3->email,
                    'created_at' => (string) $user3->created_at,
                    'updated_at' => (string) $user3->updated_at,
                ],
                [
                    'id' => $user4->id,
                    'name' => $user4->name,
                    'email' => $user4->email,
                    'created_at' => (string) $user4->created_at,
                    'updated_at' => (string) $user4->updated_at,
                ],
            ]
        ];

        $this->get("api/user?limit=2&page=2", []);
        $this->seeStatusCode(200);
        $responseData = $this->response->getOriginalContent();

        $this->assertEquals($responseData, $expectedData);
    }

    public function testUpdateValidationError()
    {
        $user = $this->createUser('golem');

        $this->actingAs($user);
        $this->patch("api/user/".$user->getKey(), ['name' => []]);
        $this->seeStatusCode(422);

        $responseData = $this->response->getOriginalContent();
        $expectedData = [
            'name' => ['The name must be a string.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testUpdateUserIdNotMatch()
    {
        $user = $this->createUser('golem');

        $this->actingAs($user);
        $this->patch("api/user/91234", ['name' => 'Cake']);
        $this->seeStatusCode(422);

        $responseData = $this->response->getOriginalContent();
        $expectedData = [
            'error' => [
                'http_code' => 422,
                'message' => 'User does not match authenticated user',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testUpdateCatchesException()
    {
        $mockedRepository = \Mockery::mock(UserRepository::class);
        $mockedRepository->shouldReceive('update')->andThrow(new \Exception('Foo Bar'));
        $this->app->instance(UserRepository::class, $mockedRepository);

        $user = $this->createUser('golem');

        $this->actingAs($user);
        $this->patch("api/user/".$user->getKey(), ['name' => 'Cake']);
        $this->seeStatusCode(409);

        $responseData = $this->response->getOriginalContent();
        $expectedData = [
            'error' => [
                'http_code' => 409,
                'message' => 'User update encountered an Unexpected Error',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testUpdateSuccess()
    {
        $newName = 'cake';
        $user = $this->createUser('golem');

        $this->actingAs($user);
        $this->patch("api/user/".$user->getKey(), ['name' => $newName]);
        $this->seeStatusCode(200);

        $responseData = $this->response->getOriginalContent();

        $user = $user->fresh();

        $expectedData = [
            'data' => [
                'id' => $user->id,
                'name' => $newName,
                'email' => $user->email,
                'created_at' => (string) $user->created_at,
                'updated_at' => (string) $user->updated_at,
            ],
        ];

        $this->assertEquals($user->name, $newName);
        $this->assertEquals($responseData, $expectedData);
    }
}
