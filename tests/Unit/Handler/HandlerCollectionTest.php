<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;
use AmraniCh\AjaxDispatcher\Handler\Handler;
use AmraniCh\AjaxDispatcher\Handler\HandlerCollection;

class HandlerCollectionTest extends TestCase
{
    /**
     * @dataProvider handlerProvider
     */
    public function test_setHandlers($handlers): void
    {
        $handlerCollectionMock = $this->getMockBuilder(HandlerCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($handlerCollectionMock, $handlerCollectionMock->setHandlers($handlers));
    }

    public function handlerProvider()
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

    public function test_setHandlers_where_passing_invalid_handler(): void
    {
        $handlerCollectionMock = $this->getMockBuilder(HandlerCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);

        $handlerCollectionMock->setHandlers([
            Handler::get('getUsers', 'UserController@getAllUsers'),
            new \stdClass()
        ]);
    }

    public function test_GetHandlers()
    {
        $handlerCollectionMock = $this->getMockBuilder(HandlerCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $handlers = [
            Handler::get('getUsers', 'UserController@getAllUsers'),
            Handler::post('addUser', 'UserController@addUser'),
            Handler::put('updateUser', 'UserController@updateUser'),
            Handler::delete('removeUser', 'UserController@removeUser')
        ];

        $handlerCollectionMock->setHandlers($handlers);

        $this->assertSame($handlers, $handlerCollectionMock->getHandlers());
    }

    public function test_addHandler(): void
    {
        $handlerCollectionMock = $this->getMockBuilder(HandlerCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame(
            $handlerCollectionMock, 
            $handlerCollectionMock->addHandler(Handler::post('login', 'AuthController@login'))
        );

        $this->assertInstanceOf(Handler::class, $handlerCollectionMock->getHandlers()[0]);
    }
}
