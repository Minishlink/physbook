<?php
/**
 * Created by PhpStorm.
 * User: Louis
 * Date: 05/11/2015
 * Time: 23:02
 */

namespace PJM\AppBundle\Tests\Services;

use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class BaseTest for helper functions in unit tests
 * @package PJM\AppBundle\Tests\Services
 */
abstract class BaseTest extends KernelTestCase
{
    /**
     * Get a private method from a class
     *
     * @param string $name
     * @param string $className
     * @return \ReflectionMethod
     */
    protected static function getMethodFromClass($name, $className)
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Get a private property from a class
     *
     * @param string $name
     * @param string $className
     * @return \ReflectionProperty
     */
    protected static function getPropertyFromClass($name, $className)
    {
        $class = new ReflectionClass($className);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }
}
