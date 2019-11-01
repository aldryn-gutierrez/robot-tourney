<?php

namespace Tests;

use Laravel\Lumen\Testing\TestCase as LumenTestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\WithoutMiddleware;

abstract class TestCase extends LumenTestCase
{
    use DatabaseMigrations, WithoutMiddleware;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    protected static function getMethod($methodName, $className)
    {
        $class = new \ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
