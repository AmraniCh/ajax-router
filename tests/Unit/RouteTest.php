<?php

declare(strict_types=1);

namespace Tests\Unit\AjaxRouter\Router;

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxRouter\Route;
use AmraniCh\AjaxRouter\Exception\InvalidArgumentException;

class RouteTest extends TestCase
{
    public function test_setMethods_with_supported_methods(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $methods = ['GET', 'POST'];

        $this->assertSame($routeMock, $routeMock->setMethods($methods));
        $this->assertSame($methods, $routeMock->getMethods());
    }

    public function test_setMethods_with_unsupported_method(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $methods = ['GET', 'REMOVE', 'PUT'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);

        $routeMock->setMethods($methods);
    }

    public function test_getMethods(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $methods = ['GET', 'POST'];

        $routeMock->setMethods($methods);

        $this->assertSame($methods, $routeMock->getMethods());
    }

    public function test_setName(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $routeMock->setName('getComments');

        $this->assertSame('getComments', $routeMock->getName());
    }

    public function test_setName_with_invalid_argument_type(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);

        $routeMock->setName(['getComments']);
    }

    public function test_setValue_with_string_value(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($routeMock, $routeMock->setValue('CommmentController@addComment'));
        $this->assertSame('CommmentController@addComment', $routeMock->getValue());
    }

    public function test_setValue_with_callable_value(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($routeMock, $routeMock->setValue(function () {
        }));
        $this->assertInstanceOf(\Closure::class, $routeMock->getValue());
    }

    public function test_setValue_with_invalid_value_argument_type(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);

        $routeMock->setValue(new \stdClass);
    }

    public function test_get_static(): void
    {
        $methods = ['GET'];
        $name = 'getPosts';
        $value = 'PostController@getPosts';

        $route = Route::get($name, $value);

        $this->assertSame($methods, $route->getMethods());
        $this->assertSame($name, $route->getName());
        $this->assertSame($name, $route->getName());
    }

    public function test_post_static(): void
    {
        $methods = ['POST'];
        $name = 'userLogin';
        $value = 'UserController@login';

        $route = Route::post($name, $value);

        $this->assertSame($methods, $route->getMethods());
        $this->assertSame($name, $route->getName());
        $this->assertSame($name, $route->getName());
    }

    public function test_many_static(): void
    {
        $methods = ['GET', 'POST'];
        $name = 'getUser';
        $value = 'UserController@getUserByID';

        $route = Route::many($methods, $name, $value);

        $this->assertSame($methods, $route->getMethods());
        $this->assertSame($name, $route->getName());
        $this->assertSame($name, $route->getName());
    }
}
