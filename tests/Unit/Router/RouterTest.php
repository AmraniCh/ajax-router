<?php

declare(strict_types=1);

namespace Tests\Unit\AjaxDispatcher\Router;

use AmraniCh\AjaxDispatcher\Exception\BadRequestException;
use AmraniCh\AjaxDispatcher\Exception\HandlerNotFoundException;
use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;
use AmraniCh\AjaxDispatcher\Exception\MethodNotAllowedException;
use AmraniCh\AjaxDispatcher\Handler\Handler;
use AmraniCh\AjaxDispatcher\Router\HandlerResolver;
use AmraniCh\AjaxDispatcher\Router\Router;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RouterTest extends TestCase
{
    public function test_setRequest(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);

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

    /**
     * @dataProvider handlerProvider
     */
    public function test_setHandlers($handlers): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($routerMock, $routerMock->setHandlers($handlers));
        $this->assertSame($handlers, $routerMock->getHandlers());
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

    public function getPSR7RequestAdapterMocked()
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'getQueryParams'
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn('GET');

        $requestMock->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([
                'function' => 'updateUser',
                'userID' => '5454',
                'firstname' => 'John',
                'lastname' => 'Doe',
            ]);

        return $requestMock;
    }

    public function test_run_Where_Handler_Name_Not_Found_In_Request_Variables(): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $routerMock->setRequest($this->getPSR7RequestAdapterMocked());
        $routerMock->setHandlerName('key');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage("the handler name 'key' not found in request variables.");
        $this->expectExceptionCode(400);

        $routerMock->run();
    }

    /**
     * @dataProvider handlerProvider
     */
    public function test_run_Where_Handler_AJAX_Request_Method_Is_Wrong($handlers): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'getQueryParams'
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('GET');

        $requestMock->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([
                'function' => 'updateUser',
                'userID' => '5454',
                'firstname' => 'John',
                'lastname' => 'Doe',
            ]);

        $routerMock = $this->getMockBuilder(Router::class)
            ->setConstructorArgs([$requestMock, 'function', $handlers])
            ->onlyMethods([])
            ->getMock();

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionCode(405);

        $routerMock->run();
    }

    /**
     * @dataProvider handlerProvider
     */
    public function test_run($handlers): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods(['getMethod', 'getQueryParams'])
            ->getMockForAbstractClass();

        $requestMock->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('GET');

        $requestMock->expects($this->atLeastOnce())
            ->method('getQueryParams')
            ->willReturn([
                'function' => 'getUsers'
            ]);

        $routerMock = $this->getMockBuilder(Router::class)
            ->setConstructorArgs([$requestMock, 'function', $handlers])
            ->onlyMethods(['createHandlerResolverClass'])
            ->getMock();

        $handlerResolverMock = $this->getMockBuilder(HandlerResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolve'])
            ->getMock();

        $closure = function () {
        };

        $handlerResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn($closure);

        $routerMock->expects($this->once())
            ->method('createHandlerResolverClass')
            ->willReturn($handlerResolverMock);

        $this->assertInstanceOf(\Closure::class, $routerMock->run());
    }

    /**
     * @dataProvider handlerProvider
     */
    public function test_run_Where_Handler_Not_Found($handlers): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods(['getMethod', 'getQueryParams'])
            ->getMockForAbstractClass();

        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn('GET');

        $requestMock->expects($this->atLeastOnce())
            ->method('getQueryParams')
            ->willReturn(['function' => 'wrong']);

        $routerMock = $this->getMockBuilder(Router::class)
            ->setConstructorArgs([$requestMock, 'function', $handlers])
            ->onlyMethods([])
            ->getMock();

        $this->expectException(HandlerNotFoundException::class);
        $this->expectExceptionMessage('No handler found for this AJAX request.');
        $this->expectExceptionCode(400);

        $routerMock->run();
    }

    public function handlerProvider(): array
    {
        return [
            [
                [
                    Handler::get('getUsers', 'UserController@getAllUsers'),
                    Handler::post('addUser', 'UserController@addUser'),
                    Handler::put('updateUser', 'UserController@updateUser'),
                    Handler::delete('removeUser', 'UserController@removeUser')
                ],
            ],
        ];
    }
}