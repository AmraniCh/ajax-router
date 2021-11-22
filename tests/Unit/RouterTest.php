<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxDispatcher\Router;
use AmraniCh\AjaxDispatcher\Http\Request;
use AmraniCh\AjaxDispatcher\Handler\Handler;
use AmraniCh\AjaxDispatcher\HandlerResolver;
use AmraniCh\AjaxDispatcher\Handler\HandlerCollection;
use AmraniCh\AjaxDispatcher\Exception\BadRequestException;
use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;
use AmraniCh\AjaxDispatcher\Exception\HandlerNotFoundException;
use AmraniCh\AjaxDispatcher\Exception\MethodNotAllowedException;

class RouterTest extends TestCase
{
    public function test_setRequest(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($routerMock, $routerMock->setRequest($requestMock));
        $this->assertSame($requestMock, $routerMock->getRequest());
    }

    public function test_setHandlerName_With_Invalid_Parameter_Type(): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("An AJAX handler name must be of type string, 'array' type given.");
        $this->expectExceptionCode(500);

        $routerMock->setHandlerName(['handler']);
    }

    public function test_setHandlerName(): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($routerMock, $routerMock->setHandlerName('handler'));
        $this->assertSame('handler', $routerMock->getHandlerName());
    }

    public function test_setHandlerCollection(): void
    {
        $handlerCollectionMock = $this->getMockBuilder(HandlerCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($routerMock, $routerMock->setHandlerCollection($handlerCollectionMock));
        $this->assertSame($handlerCollectionMock, $routerMock->getHandlerCollection());
    }

    public function test_registerControllers(): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $controllers = [
            'FooController',
            'BarController',
        ];

        $routerMock->registerControllers($controllers);

        $this->assertSame($controllers, $routerMock->getControllers());
    }

    public function test_run_Where_Handler_Name_Not_Found_In_Request_Variables(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getVariables'])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getVariables')
            ->willReturn([
                'firstname' => 'John',
                'lastname' => 'Doe',
            ]);

        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $routerMock->setRequest($requestMock);
        $routerMock->setHandlerName('function');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage("the handler name 'function' not found in request variables.");
        $this->expectExceptionCode(400);

        $routerMock->run();
    }

    public function test_run_Where_Handler_AJAX_Request_Method_Is_Wrong(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getVariables', 'getMethod'])
            ->getMock();

        $requestMock->expects($this->atLeastOnce())
            ->method('getVariables')
            ->willReturn([
                'userID' => 5454,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'function' => 'updateUser'
            ]);

        $requestMock->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('POST');

        $handlerCollectionMock = $this->getMockBuilder(HandlerCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHandlers'])
            ->getMock();

        $handlerCollectionMock->expects($this->once())
            ->method('getHandlers')
            ->willReturn([
                Handler::get('getUsers', 'UserController@getAllUsers'),
                Handler::post('addUser', 'UserController@addUser'),
                Handler::put('updateUser', 'UserController@updateUser'),
                Handler::delete('removeUser', 'UserController@removeUser')
            ]);

        $routerMock = $this->getMockBuilder(Router::class)
            ->setConstructorArgs([$requestMock, 'function', $handlerCollectionMock])
            ->onlyMethods([])
            ->getMock();

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionCode(405);

        $routerMock->run();
    }

    public function test_run(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getVariables', 'getMethod'])
            ->getMock();

        $requestMock->expects($this->atLeastOnce())
            ->method('getVariables')
            ->willReturn([
                'userID' => 5454,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'function' => 'updateUser'
            ]);

        $requestMock->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('PUT');

        $handlerCollectionMock = $this->getMockBuilder(HandlerCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHandlers'])
            ->getMock();

        $handlerCollectionMock->expects($this->once())
            ->method('getHandlers')
            ->willReturn([
                Handler::get('getUsers', 'UserController@getAllUsers'),
                Handler::post('addUser', 'UserController@addUser'),
                Handler::put('updateUser', 'UserController@updateUser'),
                Handler::delete('removeUser', 'UserController@removeUser')
            ]);

        $routerMock = $this->getMockBuilder(Router::class)
            ->setConstructorArgs([$requestMock, 'function', $handlerCollectionMock])
            ->onlyMethods(['createHandlerResolverClass'])
            ->getMock();

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolve'])
            ->getMock();

        $closure = function() {};

        $handlerResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn($closure);

        $routerMock->expects($this->once())
            ->method('createHandlerResolverClass')
            ->willReturn($handlerResolverMock);

        $this->assertInstanceOf(\Closure::class, $routerMock->run());
    }

    public function test_run_Where_Handler_Not_Found(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getVariables', 'getMethod'])
            ->getMock();

        $requestMock->expects($this->atLeastOnce())
            ->method('getVariables')
            ->willReturn([
                'function' => 'getPosts'
            ]);

        $requestMock->expects($this->never())
            ->method('getMethod');

        $handlerCollectionMock = $this->getMockBuilder(HandlerCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHandlers'])
            ->getMock();

        $handlerCollectionMock->expects($this->once())
            ->method('getHandlers')
            ->willReturn([
                Handler::get('getUsers', 'UserController@getAllUsers'),
                Handler::post('addUser', 'UserController@addUser'),
                Handler::put('updateUser', 'UserController@updateUser'),
                Handler::delete('removeUser', 'UserController@removeUser')
            ]);

        $routerMock = $this->getMockBuilder(Router::class)
            ->setConstructorArgs([$requestMock, 'function', $handlerCollectionMock])
            ->onlyMethods([])
            ->getMock();

        $this->expectException(HandlerNotFoundException::class);
        $this->expectExceptionMessage('No handler found for this AJAX request.');
        $this->expectExceptionCode(400);

        $routerMock->run();
    }
}