<?php

namespace Tests\Transformers;

use Tests\TestCase;
use App\Models\User;
use App\Transformers\UserTransformer;

class UserTransformerTest extends TestCase
{
    public function testTransform()
    {
        $user = new User();
        $user->name = 'John Doe';
        $user->email = 'john@doe.com';
        $user->password = 'SECRET';
        $user->save();

        $expectedData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => (string) $user->created_at,
            'updated_at' => (string) $user->updated_at,
        ];

        $transformer = new UserTransformer();
        $transformedData = $transformer->transform($user);

        $this->assertEquals($transformedData, $expectedData);
    }
}
