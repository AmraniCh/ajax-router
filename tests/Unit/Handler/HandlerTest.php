<?php

declare(strict_types=1);

use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;
use AmraniCh\AjaxDispatcher\Handler\Handler;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function test_setMethods_with_supported_methods(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $methods = ['GET', 'DELETE', 'PUT'];

        $this->assertSame($handlerMock, $handlerMock->setMethods($methods));
        $this->assertSame($methods, $handlerMock->getMethods());
    }

    public function test_setMethods_with_unsupported_method(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $methods = ['GET', 'REMOVE', 'PUT'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);

        $handlerMock->setMethods($methods);
    }

    public function test_getMethods(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $methods = ['GET', 'POST', 'PUT', 'DELETE'];

        $handlerMock->setMethods($methods);

        $this->assertSame($methods, $handlerMock->getMethods());
    }

    public function test_setName(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $handlerMock->setName('getComments');

        $this->assertSame('getComments', $handlerMock->getName());
    }

    public function test_setName_with_invalid_argument_type(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);

        $handlerMock->setName(['getComments']);
    }

    public function test_setValue_with_string_value(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($handlerMock, $handlerMock->setValue('CommmentController@addComment'));
        $this->assertSame('CommmentController@addComment', $handlerMock->getValue());
    }

    public function test_setValue_with_callable_value(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($handlerMock, $handlerMock->setValue(function () {}));
        $this->assertInstanceOf(\Closure::class, $handlerMock->getValue());
    }

    public function test_setValue_with_invalid_value_argument_type(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);

        $handlerMock->setValue(['CommmentController@addComment']);
    }

    public function test_getType_with_callable(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $handlerMock->setValue(function () {});

        $this->assertSame('callable', $handlerMock->getType());
    }

    public function test_getType_with_string(): void
    {
        $handlerMock = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $handlerMock->setValue('CommmentController@addComment');

        $this->assertSame('string', $handlerMock->getType());
    }

    public function test_get_static(): void
    {
        $methods = ['GET'];
        $name    = 'getPosts';
        $value   = 'PostController@getPosts';

        $handler = Handler::get($name, $value);

        $this->assertSame($methods, $handler->getMethods());
        $this->assertSame($name, $handler->getName());
        $this->assertSame($name, $handler->getName());
        $this->assertSame('string', $handler->getType());
    }

    public function test_post_static(): void
    {
        $methods = ['POST'];
        $name    = 'userLogin';
        $value   = 'UserController@login';

        $handler = Handler::post($name, $value);

        $this->assertSame($methods, $handler->getMethods());
        $this->assertSame($name, $handler->getName());
        $this->assertSame($name, $handler->getName());
        $this->assertSame('string', $handler->getType());
    }

    public function test_put_static(): void
    {
        $methods = ['PUT'];
        $name    = 'updateUser';
        $value   = 'UserController@updateUser';

        $handler = Handler::put($name, $value);

        $this->assertSame($methods, $handler->getMethods());
        $this->assertSame($name, $handler->getName());
        $this->assertSame($name, $handler->getName());
        $this->assertSame('string', $handler->getType());
    }

    public function test_delete_static(): void
    {
        $methods = ['PUT'];
        $name    = 'deleteUser';
        $value   = 'UserController@deleteUser';

        $handler = Handler::put($name, $value);

        $this->assertSame($methods, $handler->getMethods());
        $this->assertSame($name, $handler->getName());
        $this->assertSame($name, $handler->getName());
        $this->assertSame('string', $handler->getType());
    }

    public function test_many_static(): void
    {
        $methods = ['GET', 'POST'];
        $name    = 'getUser';
        $value   = 'UserController@getUserByID';

        $handler = Handler::many($methods, $name, $value);

        $this->assertSame($methods, $handler->getMethods());
        $this->assertSame($name, $handler->getName());
        $this->assertSame($name, $handler->getName());
        $this->assertSame('string', $handler->getType());
    }
}
