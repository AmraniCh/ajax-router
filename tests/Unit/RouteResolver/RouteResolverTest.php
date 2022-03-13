<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxRouter\Route;
use Psr\Http\Message\ServerRequestInterface;
use AmraniCh\AjaxRouter\RouteResolver\RouteResolver;
use AmraniCh\AjaxRouter\Exception\UnexpectedValueException;

class RouteResolverTest extends TestCase
{
    public function test_resolve_With_String_Route_Type(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $routeMock->expects($this->once())
            ->method('getValue')
            ->willReturn('PostController@getPosts');

        $handlerResolverMock = $this->getMockBuilder(RouteResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolveString'])
            ->getMock();

        $handlerResolverMock->expects($this->once())
            ->method('resolveString')
            ->with('PostController@getPosts')
            ->willReturn(function () {
            });

        $this->assertInstanceOf(\Closure::class, $handlerResolverMock->resolve($routeMock));
    }

    public function test_resolve_With_Array_Route_Type(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $routeMock->expects($this->once())
            ->method('getValue')
            ->willReturn([\stdClass::class, 'login']);

        $handlerResolverMock = $this->getMockBuilder(RouteResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolveArray'])
            ->getMock();

        $handlerResolverMock->expects($this->once())
            ->method('resolveArray')
            ->with([\stdClass::class, 'login'])
            ->willReturn(function () {});

        $this->assertInstanceOf(\Closure::class, $handlerResolverMock->resolve($routeMock));
    }

    public function test_resolve_With_Callable_array_Route_Type(): void
    {
        $class = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $callable = [get_class($class), 'foo'];

        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $routeMock->expects($this->once())
            ->method('getValue')
            ->willReturn($callable);

        $handlerResolverMock = $this->getMockBuilder(RouteResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolveArray'])
            ->getMock();

        $handlerResolverMock->expects($this->once())
            ->method('resolveArray')
            ->with($callable)
            ->willReturn(function () {
            });

        $this->assertIsCallable($handlerResolverMock->resolve($routeMock));
    }

    public function test_resolve_With_Invalid_Route_Type(): void
    {
        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $routeMock->expects($this->once())
            ->method('getValue')
            ->willReturn(new \stdClass());

        $handlerResolverMock = $this->getMockBuilder(RouteResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(500);

        $this->assertIsCallable($handlerResolverMock->resolve($routeMock));
    }

    public function test_getRegisteredControllerByName_Where_Controller_Is_Registered(): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controllers = [
            'App\Controller\FooController',
            'App\Controller\BarController',
        ];

        $handlerResolverMock = $this->getMockBuilder(RouteResolver::class)
            ->setConstructorArgs([$requestMock, [], $controllers])
            ->onlyMethods(['getControllerName'])
            ->getMock();

        $handlerResolverMock->expects($this->exactly(2))
            ->method('getControllerName')
            ->willReturnOnConsecutiveCalls(
                'FooController',
                'BarController'
            );

        $method = $this->getReflectedMethod('getRegisteredControllerByName');

        $this->assertSame('App\Controller\BarController', $method->invoke($handlerResolverMock, 'BarController'));
    }

    public function test_getRegisteredControllerByName_Where_Controller_Is_Not_Registered(): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controllers = [
            'App\Controller\FooController',
        ];

        $handlerResolverMock = $this->getMockBuilder(RouteResolver::class)
            ->setConstructorArgs([$requestMock, [], $controllers])
            ->onlyMethods(['getControllerName'])
            ->getMock();

        $handlerResolverMock->expects($this->once())
            ->method('getControllerName')
            ->willReturnOnConsecutiveCalls(
                'FooController',
            );

        $method = $this->getReflectedMethod('getRegisteredControllerByName');

        $this->assertNull($method->invoke($handlerResolverMock, 'BarController'));
    }

    public function test_getControllerName_With_Controller_Name(): void
    {
        $handlerResolverMock = $this->getMockBuilder(RouteResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = $this->getReflectedMethod('getControllerName');

        $this->assertSame('FooController', $method->invoke($handlerResolverMock, 'App\Controller\FooController'));
    }

    public function test_getControllerName_With_Controller_Instance(): void
    {
        $requestMock = $this->getMockBuilder(\AmraniCh\AjaxRouter\Http\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handlerResolverMock = $this->getMockBuilder(RouteResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = $this->getReflectedMethod('getControllerName');

        $this->assertSame('stdClass', $method->invoke($handlerResolverMock, new \stdClass()));
    }

    /**
     * Gets accessible reflected method for private/protected methods.
     *
     * @param string $name
     *
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    protected function getReflectedMethod(string $name): \ReflectionMethod
    {
        $reflectedClass = new \ReflectionClass(RouteResolver::class);

        $method = $reflectedClass->getMethod($name);

        $method->setAccessible(true);

        return $method;
    }
}