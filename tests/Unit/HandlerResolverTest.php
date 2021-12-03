<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxDispatcher\Handler\Handler;
use AmraniCh\AjaxDispatcher\HandlerResolver;
use AmraniCh\AjaxDispatcher\Exception\LogicException;
use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;
use AmraniCh\AjaxDispatcher\Exception\UnexpectedValueException;

class HandlerResolverTest extends TestCase
{
    public function test_resolve_With_String_Handler_Type(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue', 'getType'])
            ->getMock();

        $handlerMock->expects($this->once())
            ->method('getValue')
            ->willReturn('PostController@getPosts');

        $handlerMock->expects($this->once())
            ->method('getType')
            ->willReturn('string');

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolveString'])
            ->getMock();

        $handlerResolverMock->expects($this->once())
            ->method('resolveString')
            ->with('PostController@getPosts')
            ->willReturn(function() {
            });

        $this->assertInstanceOf(\Closure::class, $handlerResolverMock->resolve($handlerMock));
    }

    public function test_resolve_With_Closure_Handler_Type(): void
    {
        $closure = function() {
        };

        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue', 'getType'])
            ->getMock();

        $handlerMock->expects($this->once())
            ->method('getValue')
            ->willReturn($closure);

        $handlerMock->expects($this->once())
            ->method('getType')
            ->willReturn('callable');

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolveCallable'])
            ->getMock();

        $handlerResolverMock->expects($this->once())
            ->method('resolveCallable')
            ->with($closure)
            ->willReturn($closure);

        $this->assertInstanceOf(\Closure::class, $handlerResolverMock->resolve($handlerMock));
    }

    public function test_resolve_With_Callable_array_Handler_Type(): void
    {
        $class = $this->getMockBuilder(stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $callable = [get_class($class), 'foo'];

        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue', 'getType'])
            ->getMock();

        $handlerMock->expects($this->once())
            ->method('getValue')
            ->willReturn($callable);

        $handlerMock->expects($this->once())
            ->method('getType')
            ->willReturn('callable');

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolveCallable'])
            ->getMock();

        $handlerResolverMock->expects($this->once())
            ->method('resolveCallable')
            ->with($callable)
            ->willReturn(function() {
            });

        $this->assertIsCallable($handlerResolverMock->resolve($handlerMock));
    }

    public function test_resolve_With_Invalid_Handler_Type(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue', 'getType'])
            ->getMock();

        $handlerMock->expects($this->once())
            ->method('getValue')
            ->willReturn(['CommentsController@getCommentByID']);

        $handlerMock->expects($this->once())
            ->method('getType')
            ->willReturn('array');

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(500);

        $this->assertIsCallable($handlerResolverMock->resolve($handlerMock));
    }

    public function test_getCallableMethod_Where_Controller_Class_Not_Exists(): void
    {
        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRegisteredControllerByName'])
            ->getMock();

        $handlerResolverMock->expects($this->once())
            ->method('getRegisteredControllerByName')
            ->willReturn(null);

        $this->expectException(LogicException::class);
        $this->expectExceptionCode(500);

        $method = $this->getReflectedMethod('getCallableMethod');

        $method->invoke($handlerResolverMock, 'MyController@foo');
    }

    public function test_getCallableMethod_Where_Controller_Class_Exists(): void
    {
        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRegisteredControllerByName'])
            ->getMock();

        $handlerResolverMock->expects($this->once())
            ->method('getRegisteredControllerByName')
            ->with('App\MyController')
            ->willReturn('MyController');

        $method = $this->getReflectedMethod('getCallableMethod');

        $this->assertInstanceOf(\Closure::class, $method->invoke($handlerResolverMock, 'App\MyController@foo'));
    }

    public function test_getRegisteredControllerByName_Where_Controller_Is_Registered(): void
    {
        $requestMock = $this->getMockBuilder(\AmraniCh\AjaxDispatcher\Http\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controllers = [
            'App\Controller\FooController',
            'App\Controller\BarController',
        ];

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->setConstructorArgs([$requestMock, $controllers])
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
        $requestMock = $this->getMockBuilder(\AmraniCh\AjaxDispatcher\Http\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controllers = [
            'App\Controller\FooController',
        ];

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->setConstructorArgs([$requestMock, $controllers])
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
        $requestMock = $this->getMockBuilder(\AmraniCh\AjaxDispatcher\Http\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = $this->getReflectedMethod('getControllerName');

        $this->assertSame('FooController', $method->invoke($handlerResolverMock, 'App\Controller\FooController'));
    }

    public function test_getControllerName_With_Controller_Instance(): void
    {
        $requestMock = $this->getMockBuilder(\AmraniCh\AjaxDispatcher\Http\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = $this->getReflectedMethod('getControllerName');

        $this->assertSame('stdClass', $method->invoke($handlerResolverMock, new stdClass()));
    }

    /**
     * Gets accessible reflected method for private/protected methods.
     *
     * @param string $name
     *
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    protected function getReflectedMethod($name)
    {
        $reflectedClass = new ReflectionClass(HandlerResolver::class);

        $method = $reflectedClass->getMethod($name);

        $method->setAccessible(true);

        return $method;
    }
}