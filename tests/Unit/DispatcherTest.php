<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxRouter\Dispatcher;
use AmraniCh\AjaxRouter\Router;

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
}
