<?php

namespace Tests\Transformers;

use Tests\TestCase;
use App\Transformers\PlainArraySerializer;

class PlainArraySerializerTest extends TestCase
{
    public function serializeArrayDataProvider()
    {
        $data = ['name' => 'John Doe'];

        return [
            'withFalseResourceKey' => [
                false,
                $data,
                $data
            ],
            'withDefaultResourceKeyDefault' => [
                '',
                $data,
                ['data' => $data]
            ],
            'withDefaultResourceKeyCustom' => [
                'userInfo',
                $data,
                ['userInfo' => $data]
            ],
        ];
    }

    /**
     * @dataProvider serializeArrayDataProvider
     */
    public function testSerializeArray($resourceKey, $data, $expecedSerializedData)
    {
        $method = self::getMethod('serializeArray', PlainArraySerializer::class);
        $serializer = new PlainArraySerializer();
        $serializedData = $method->invokeArgs($serializer, [$resourceKey, $data]);

        $this->assertEquals($expecedSerializedData, $serializedData);
    }

    public function testCollection()
    {
        $serializer = \Mockery::mock(PlainArraySerializer::class)->makePartial();
        $serializer->shouldAllowMockingProtectedMethods();
        $serializer->shouldReceive('serializeArray')->once();

        $serializer->collection(false, []);
    }

    public function testItem()
    {
        $serializer = \Mockery::mock(PlainArraySerializer::class)->makePartial();
        $serializer->shouldAllowMockingProtectedMethods();
        $serializer->shouldReceive('serializeArray')->once();

        $serializer->item(false, []);
    }
}
