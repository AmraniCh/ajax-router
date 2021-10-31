<?php

require_once __DIR__ . '/PostsController.php';

use AmraniCh\AjaxDispatcher;
use AmraniCh\AjaxDispatcherException;
use PHPUnit\Framework\TestCase;

class AjaxDispatcherTest extends TestCase
{
    public function test_dispatch_With_Non_Exist_Key()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'key', [
                'GET' => ['getPosts' => 'PostsController@getPosts']
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn(['function' => 'getPosts']);

        $this->expectException(AjaxDispatcherException::class);
        $this->expectExceptionMessage("the key 'key' not found in request variables.");

        $dispatcher->dispatch();
    }

    public function test_dispatch_With_Invalid_HTTP_Request_Method()
    {
        $server = ['REQUEST_METHOD' => 'ADD'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => ['getPosts' => 'PostsController@getPosts']
            ]])
            ->onlyMethods(['checkScriptContext'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $this->expectException(AjaxDispatcherException::class);
        $this->expectExceptionMessage("Unknown HTTP request method 'ADD'.");

        $dispatcher->dispatch();
    }

    public function test_dispatch_With_Invalid_Handler_Type()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => ['getPosts' => 4096]
            ]])
            ->onlyMethods(['checkScriptContext'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $this->expectException(AjaxDispatcherException::class);
        $this->expectExceptionMessage('the type of \'getPosts\' handler value must be either a string/array/callable.');

        $dispatcher->dispatch();
    }

    public function test_dispatch_Where_Handler()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => ['getComments' => 'PostsController@getPosts']
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn(['function' => 'getPosts']);

        $this->expectException(AjaxDispatcherException::class);
        $this->expectExceptionMessage('No handler was found for this AJAX request.');

        $dispatcher->dispatch();
    }

    public function test_dispatch_With_Before_Returns_False()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => [
                    'getPosts' => 'PostsController@getPosts',
                ]
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn([
                'function' => 'getPosts',
                'token' => ''
            ]);

        $dispatcher->before(function ($params) {
            return $params->token === 'TOKEN';
        });

        $this->expectOutputString('');

        $dispatcher->dispatch();
    }

    public function test_dispatch_Success_With_String_Handler_Type()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => [
                    'getPosts' => 'PostsController@getPosts',
                ]
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn(['function' => 'getPosts']);

        $dispatcher->registerControllers([PostsController::class]);

        $this->expectOutputString('[{"id":1,"title":"Post 1 title","content":"Post 1 content"}]');

        $dispatcher->dispatch();
    }

    public function test_dispatch_Success_With_Array_Handler_Type()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => [
                    'getPostsByID' => ['PostsController@getPostsByID', 'id']
                ]
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn([
                'function' => 'getPostsByID',
                'id' => 1034
            ]);

        $dispatcher->registerControllers([PostsController::class]);

        $this->expectOutputString('{"id":1034,"title":"Post 1034 title","content":"Post 1034 content"}');

        $dispatcher->dispatch();
    }

    public function test_dispatch_Success_With_Callback_Handler_Type()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => [
                    'getPostsByID' => function ($id, $title) {
                        echo("searching for post with id equal to \"$id\" and the title is \"$title\"");
                    }
                ]
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn([
                'function' => 'getPostsByID',
                'id' => 1024,
                'title' => 'PHP is awesome!'
            ]);

        $dispatcher->registerControllers([PostsController::class]);

        $this->expectOutputString('searching for post with id equal to "1024" and the title is "PHP is awesome!"');

        $dispatcher->dispatch();
    }

    public function test_dispatch_With_Self_Controller_Instances()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => [
                    'getPosts' => 'PostsController@getPosts',
                ]
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn(['function' => 'getPosts']);

        $dispatcher->registerControllers([new PostsController]);

        $this->expectOutputString('[{"id":1,"title":"Post 1 title","content":"Post 1 content"}]');

        $dispatcher->dispatch();
    }

    public function test_dispatch_With_Before()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => [
                    'getPosts' => 'PostsController@getPosts',
                ]
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn(['function' => 'getPosts']);


        $dispatcher->before(function ($params) {
            if (!isset($params->token)) {
                throw new Exception('Token required.');
            }
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token required.');

        $dispatcher->dispatch();
    }

    public function test_dispatch_With_OnException()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => [
                    'getPosts' => 'PostsController@getPosts',
                ]
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn(['function' => 'getPosts']);

        $dispatcher->registerControllers([new PostsController]);

        $dispatcher->before(function ($params) {
            if (!isset($params->token)) {
                throw new Exception('Token required.');
            }
        });

        $dispatcher->onException(function ($ex) {
            echo json_encode(['error' => $ex->getMessage()]);
        });

        $this->expectOutputString('{"error":"Token required."}');

        $dispatcher->dispatch();
    }

    public function test_dispatch_With_String_Handler_Type_With_Non_Exists_Controller()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'GET' => [
                    'getPosts' => 'BlogsController@getPosts',
                ]
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn(['function' => 'getPosts']);

        $this->expectException(AjaxDispatcherException::class);
        $this->expectExceptionMessage("Controller class 'BlogsController' not found.");

        $dispatcher->dispatch();
    }

    public function test_dispatch_With_String_Handler_Type_With_Non_Exists_Controller_Method()
    {
        $server = ['REQUEST_METHOD' => 'POST'];

        $dispatcher = $this->getMockBuilder(AjaxDispatcher::class)
            ->setConstructorArgs([$server, 'function', [
                'POST' => [
                    'getPosts' => 'PostsController@getBlogs',
                ]
            ]])
            ->onlyMethods(['checkScriptContext', 'getRequestVariables'])
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('checkScriptContext');

        $dispatcher
            ->expects($this->once())
            ->method('getRequestVariables')
            ->willReturn(['function' => 'getPosts']);

        $dispatcher->registerControllers([PostsController::class]);

        $this->expectException(AjaxDispatcherException::class);
        $this->expectExceptionMessage("Controller method 'getBlogs' not exist in controller 'PostsController'.");

        $dispatcher->dispatch();
    }
}