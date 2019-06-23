<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class TestAbstract extends TestCase
{
    /**
     * Test protected methods.
     *
     * @param $object
     * @param $methodName
     * @param array $parameters
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    protected function invokeProtectedMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    protected function setProtectedProperty($object, $property, $value)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }
}
