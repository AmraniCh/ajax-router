<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxDispatcher\Internal\ControllerMethod;
use AmraniCh\AjaxDispatcher\Exception\LogicException;

class ControllerMethodTest extends TestCase
{
    public function test_call_where_class_is_string(): void
    {
        $controllerMethodMock = $this->getMockBuilder(ControllerMethod::class)
            ->setConstructorArgs(['fooClass', 'method'])
            ->onlyMethods([
                'isClassExists',
                'isMethodExists',
                'getClassInstance',
                'callUserFuncArray'
            ])
            ->getMock();

        $controllerMethodMock->expects($this->once())
            ->method('isClassExists')
            ->with('fooClass')
            ->willReturn(true);

        $controllerMethodMock->expects($this->once())
            ->method('isMethodExists')
            ->with('method')
            ->willReturn(true);

        $controllerMethodMock->expects($this->once())
            ->method('getClassInstance');

        $controllerMethodMock->expects($this->once())
            ->method('callUserFuncArray');

        $controllerMethodMock->call([]);
    }

    public function test_call_where_class_is_real_instance(): void
    {
        $controllerMethodMock = $this->getMockBuilder(ControllerMethod::class)
            ->setConstructorArgs([new stdClass(), 'method'])
            ->onlyMethods([
                'isMethodExists',
                'getClassInstance',
                'callUserFuncArray'
            ])
            ->getMock();

        $controllerMethodMock->expects($this->once())
            ->method('isMethodExists')
            ->with('method')
            ->willReturn(true);

        $controllerMethodMock->expects($this->once())
            ->method('getClassInstance');

        $controllerMethodMock->expects($this->once())
            ->method('callUserFuncArray');

        $controllerMethodMock->call([]);
    }

    public function test_call_where_class_not_exists(): void
    {
        $controllerMethodMock = $this->getMockBuilder(ControllerMethod::class)
            ->setConstructorArgs(['fooClass', 'method'])
            ->onlyMethods([
                'isClassExists',
            ])
            ->getMock();

        $controllerMethodMock->expects($this->once())
            ->method('isClassExists')
            ->with('fooClass')
            ->willReturn(false);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Controller class 'fooClass' not found.");
        $this->expectExceptionCode(500);

        $controllerMethodMock->call([]);
    }

    public function test_call_where_method_not_exists(): void
    {
        $controllerMethodMock = $this->getMockBuilder(ControllerMethod::class)
            ->setConstructorArgs(['fooClass', 'method'])
            ->onlyMethods([
                'isClassExists',
                'isMethodExists'
            ])
            ->getMock();

        $controllerMethodMock->expects($this->once())
            ->method('isClassExists')
            ->with('fooClass')
            ->willReturn(true);

        $controllerMethodMock->expects($this->once())
            ->method('isMethodExists')
            ->with('method')
            ->willReturn(false);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("The method 'method' not exist in controller 'fooClass'.");
        $this->expectExceptionCode(500);

        $controllerMethodMock->call([]);
    }
}
