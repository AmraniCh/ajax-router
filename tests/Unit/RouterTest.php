<?php

declare(strict_types=1);

namespace Tests\Unit\AjaxRouter\Router;

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxRouter\Route;
use AmraniCh\AjaxRouter\Router;
use Psr\Http\Message\ServerRequestInterface;
use AmraniCh\AjaxRouter\Exception\BadRequestException;
use AmraniCh\AjaxRouter\Exception\RouteNotFoundException;
use AmraniCh\AjaxRouter\Exception\InvalidArgumentException;
use AmraniCh\AjaxRouter\Exception\MethodNotAllowedException;

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
        $this->expectExceptionMessage("A route variable must be of type string, 'array' type given.");
        $this->expectExceptionCode(500);

        $routerMock->setRouteVariable(['handler']);
    }

    public function test_setHandlerName(): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($routerMock, $routerMock->setRouteVariable('handler'));
        $this->assertSame('handler', $routerMock->getRouteVariable());
    }

    /**
     * @dataProvider routeProvider
     */
    public function test_setHandlers($handlers): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($routerMock, $routerMock->setRoutes($handlers));
        $this->assertSame($handlers, $routerMock->getRoutes());
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

    public function getPSR7GetRequestAdapterMocked($params = [])
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
            ->willReturn($params ?: [
                'function' => 'updateUser',
                'userID' => '5454',
                'firstname' => 'John',
                'lastname' => 'Doe',
            ]);

        return $requestMock;
    }

    public function test_run_Where_Route_Parameter_Not_Found_In_Request_Variables(): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $routerMock
            ->setRequest($this->getPSR7GetRequestAdapterMocked())
            ->setRouteVariable('key');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage("The route parameter 'key' not found in request variables.");
        $this->expectExceptionCode(400);

        $routerMock->run();
    }

    public function test_run_Where_Route_Name_Not_Found_In_Request_Variables(): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $routerMock
            ->setRequest($this->getPSR7GetRequestAdapterMocked(['route' => '']))
            ->setRouteVariable('route');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage("Route name not given.");
        $this->expectExceptionCode(400);

        $routerMock->run();
    }

    /**
     * @dataProvider routeProvider
     */
    public function test_run_Where_Route_AJAX_Request_Method_Is_Wrong($handlers): void
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
                'function' => 'addUser',
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
     * @dataProvider routeProvider
     */
    public function test_run_Where_Route_Not_Found($handlers): void
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

        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('No Route found for this request.');
        $this->expectExceptionCode(400);

        $routerMock->run();
    }

    public function routeProvider(): array
    {
        return [
            [
                [
                    Route::get('getUsers', 'UserController@getAllUsers'),
                    Route::post('addUser', 'UserController@addUser'),
                ],
            ],
        ];
    }
}