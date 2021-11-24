<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxDispatcher\Router;
use AmraniCh\AjaxDispatcher\Dispatcher;
use AmraniCh\AjaxDispatcher\Http\Request;
use AmraniCh\AjaxDispatcher\Http\Response;

class DispatcherTest extends TestCase
{
    public function test_setRouter(): void
    {
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($dispatcherMock, $dispatcherMock->setRouter($routerMock));
        $this->assertSame($routerMock, $dispatcherMock->getRouter());
    }

    public function test_onException(): void
    {
        $dispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $onException = function() {};

        $this->assertSame($dispatcherMock, $dispatcherMock->onException($onException));
        $this->assertSame($onException, $dispatcherMock->getOnExceptionCallback());
    }

    public function test_clearBuffer(): void
    {
        $dispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($dispatcherMock, $dispatcherMock->cleanBuffer());
    }

    public function test_dispatch_Where_Resolvable_Handler_Result_Is_Instance_Of_Response(): void
    {
        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();

        $responseMock->expects($this->once())
            ->method('send');

        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $closure = function() {};

        $routerMock->expects($this->once())
            ->method('run')
            ->willReturn($closure);

        $dispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['handleException'])
            ->getMock();

        $dispatcherMock->expects($this->once())
            ->method('handleException')
            ->with($closure)
            ->willReturn($responseMock);

        $dispatcherMock->setRouter($routerMock);

        $this->assertSame($dispatcherMock, $dispatcherMock->dispatch());
    }

    public function test_dispatch_Where_Clean_Buffer_Enabled(): void
    {
        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();

        $responseMock->expects($this->once())
            ->method('send');

        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $closure = function() {};

        $routerMock->expects($this->once())
            ->method('run')
            ->willReturn($closure);

        $dispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'handleException',
                'getBufferLevels',
                'eraseBuffer'
            ])
            ->getMock();

        $dispatcherMock->expects($this->once())
            ->method('handleException')
            ->with($closure)
            ->willReturn($responseMock);

        $dispatcherMock->expects($this->once())
            ->method('getBufferLevels')
            ->willReturn(1);

        $dispatcherMock->expects($this->once())
            ->method('eraseBuffer');

        $dispatcherMock->setRouter($routerMock);

        $dispatcherMock->cleanBuffer();

        $this->assertSame($dispatcherMock, $dispatcherMock->dispatch());
    }
}
