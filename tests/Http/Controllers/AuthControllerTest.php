<?php

namespace Tests\Http\Controllers;

use Tests\TestCase;
use Auth;
use App\Models\User;
use App\Repositories\UserRepository;

class AuthControllerTest extends TestCase
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

    public function testRegisterValidationError()
    {
        $this->post("api/register", []);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'name' => ['The name field is required.'],
            'email' => ['The email field is required.'],
            'password' => ['The password field is required.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testRegisterPasswordMismatch()
    {
        $this->post("api/register", [
            'name' => 'Romeo Juliet',
            'email' => 'romeo@juliet.com',
            'password' => 'secret',
            'password_confirmation' => 'secrets123',
        ]);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'password' => ['The password confirmation does not match.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testRegisterCatchesException()
    {
        $mockedRepository = \Mockery::mock(UserRepository::class);
        $mockedRepository->shouldReceive('create')->andThrow(new \Exception('Foo Bar'));
        $this->app->instance(UserRepository::class, $mockedRepository);

        $this->post("api/register", [
            'name' => 'Romeo Juliet',
            'email' => 'romeo@juliet.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);
        $this->seeStatusCode(409);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 409,
                'message' => 'Registering user encountered an Unexpected Error',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testRegisterSuccess()
    {
        $dataToCreate = [
            'name' => 'Romeo Juliet',
            'email' => 'romeo@juliet.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ];

        $user = User::where('email', $dataToCreate['email'])->first();
        $this->assertNull($user);

        $this->post("api/register", $dataToCreate);
        $this->seeStatusCode(201);
        $responseData = $this->response->getOriginalContent();

        $user = User::where('email', $dataToCreate['email'])->first();
        $this->assertNotNull($user);

        $expectedData = [
            'data' => [
                'id' => $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testLoginValidationError()
    {
        $this->post("api/login", []);
        $this->seeStatusCode(422);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'email' => ['The email field is required.'],
            'password' => ['The password field is required.'],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testLoginUnauthorized()
    {
        Auth::shouldReceive('attempt')->once()->andReturn(false);

        $this->post("api/login", ['email' => 'foo@foo.com', 'password' => 'BarBar']);
        $this->seeStatusCode(401);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 401,
                'message' => 'Unauthorized',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testLoginCatchesException()
    {
        Auth::shouldReceive('attempt')->once()->andThrow(new \Exception('Foo Bar'));

        $this->post("api/login", ['email' => 'foo@foo.com', 'password' => 'BarBar']);
        $this->seeStatusCode(409);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'error' => [
                'http_code' => 409,
                'message' => 'Login encountered an Unexpected Error',
            ],
        ];

        $this->assertEquals($responseData, $expectedData);
    }

    public function testLoginSuccess()
    {
        $fakeFactory = new class {        
            public function getTTL() { 
                return 90;
            }
        };
        
        $token = 'MYTOKEN';
        Auth::shouldReceive('attempt')->once()->andReturn($token);
        Auth::shouldReceive('factory')->once()->andReturn($fakeFactory);

        $this->post("api/login", ['email' => 'foo@foo.com', 'password' => 'BarBar']);
        $this->seeStatusCode(200);
        $responseData = $this->response->getOriginalContent();

        $expectedData = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (90 * 60),
        ];

        $this->assertEquals($responseData, $expectedData);
    }
}