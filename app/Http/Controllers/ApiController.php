<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Transformers\PlainArraySerializer;
use Illuminate\Http\JsonResponse as Response;
use Illuminate\Support\Facades\Auth;
use League\Fractal;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class ApiController extends Controller
{
    protected $fractal;
    protected $statusCode = 200;

    public function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new PlainArraySerializer());
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    protected function respondWithItem(
        $item,
        $transformer,
        $includes = [],
        $statusCode = 200,
        $resourceKey = null,
        array $headers = []
    ) {
        if (!empty($includes)) {
            $this->fractal->parseIncludes($includes);
        }

        $resource = new Item($item, $transformer, $resourceKey);
        $dataFromResource = $this->fractal->createData($resource);

        return $this->respondWithArray($dataFromResource->toArray(), $headers, $statusCode);
    }

    protected function respondWithArray(array $array, array $headers = [], $statusCode = 200)
    {
        $this->setStatusCode($statusCode);

        return new Response($array, $this->statusCode, $headers);
    }

    protected function respondWithError($message, $httpCode = 400)
    {
        $this->setStatusCode($httpCode);

        $error = [
            'http_code' => $this->statusCode,
            'message' => $message,
        ];

        return $this->respondWithArray(
            ['error' => $error],
            [],
            $this->statusCode
        );
    }

    protected function respondWithCollection($collection, $transformer, $includes = [], $statusCode = 200, $metadata = null, $headers = [])
    {
        if (!empty($includes)) {
            $this->fractal->parseIncludes($includes);
        }

        $resource = new Collection($collection, $transformer);
        if (!empty($metadata)) {
            $resource->setMeta($metadata);
        }

        $dataFromResource = $this->fractal->createData($resource);

        return $this->respondWithArray($dataFromResource->toArray(), $headers, $statusCode);
    }

    protected function respondWithNoContent(array $headers = [], $statusCode = 204)
    {
        $this->setStatusCode($statusCode);

        return new Response(null, $this->statusCode, $headers);
    }

    protected function respondWithToken($token)
    {
        return $this->respondWithArray([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}
