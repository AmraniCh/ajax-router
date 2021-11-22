<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxDispatcher\Exception\BadRequestException;
use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;
use AmraniCh\AjaxDispatcher\Exception\LogicException;
use AmraniCh\AjaxDispatcher\Http\Request;

class RequestTest extends TestCase
{
    public function test__get_where_request_variable_exists(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setVariables([
            'id'        => 64,
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ]);

        $this->assertSame(64, $requestMock->id);
        $this->assertSame('John', $requestMock->firstname);
        $this->assertSame('Doe', $requestMock->lastname);
    }

    public function test__get_where_request_variable_not_exists(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("The parameter 'id' not exists in the request variables.");
        $this->expectExceptionCode(500);

        $requestMock->id;
    }

    public function test_getServer(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $server = ['REQUEST_METHOD' => 'GET'];

        $requestMock->setServer($server);

        $this->assertSame($server, $requestMock->getServer());
    }

    public function test_setServer(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $server = ['REQUEST_METHOD' => 'GET'];

        $this->assertSame($requestMock, $requestMock->setServer($server));
        $this->assertSame($server, $requestMock->getServer($server));
    }

    public function test_getMethod(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setMethod('GET');

        $this->assertSame('GET', $requestMock->getMethod());
    }

    public function test_setMethod_With_Valid_Http_Request_Method()
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($requestMock, $requestMock->setMethod('DELETE'));
        $this->assertSame('DELETE', $requestMock->getMethod());
    }

    public function test_setMethod_With_Invalid_Http_Request_Method()
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->expectException(InvalidArgumentException::class);
        $this->expectDeprecationMessage("HTTP request method 'ADD' not supported.");
        $this->expectExceptionCode(500);

        $requestMock->setMethod('ADD');
    }

    public function test_getHeaders(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $headers = ['X_CUSTOM_HEADER' => 'some useful info'];

        $requestMock->setHeaders($headers);

        $this->assertSame($headers, $requestMock->getHeaders());
    }

    public function test_setHeaders(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($requestMock, $requestMock->setHeaders(['X_CUSTOM_HEADER' => 'some useful info']));
        $this->assertSame('some useful info', $requestMock->getHeaderValue('X_CUSTOM_HEADER'));
    }

    public function test_getVariables(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $variables = [
            'id'        => 64,
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ];

        $requestMock->setVariables($variables);

        $this->assertSame($variables, $requestMock->getVariables());
    }

    public function test_setVariables(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $variables = [
            'id'        => 64,
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ];

        $this->assertSame($requestMock, $requestMock->setVariables($variables));
        $this->assertSame($variables, $requestMock->getVariables());
    }

    public function test_throwIfNotAJAXRequest_with_AJAX_request(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setHeaders([
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]);

        $this->assertSame($requestMock, $requestMock->throwIfNotAJAXRequest());
    }

    public function test_throwIfNotAJAXRequest_with_regular_HTTP_request(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setServer([
            'REQUEST_METHOD' => 'GET'
        ]);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('AjaxDispatcher Accept only AJAX requests.');
        $this->expectExceptionCode(400);

        $requestMock->throwIfNotAJAXRequest();
    }

    public function test_isAjaxRequest_with_AJAX_request(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setHeaders([
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]);

        $this->assertTrue($requestMock->isAjaxRequest());
    }

    public function test_isAjaxRequest_with_regular_HTTP_request(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setServer([
            'REQUEST_METHOD' => 'GET'
        ]);

        $this->assertFalse($requestMock->isAjaxRequest());
    }

    public function test_getHeaderValue_where_header_exists(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setHeaders(['X_CUSTOM_HEADER' => 'some useful info']);

        $this->assertSame('some useful info', $requestMock->getHeaderValue('X_CUSTOM_HEADER'));
    }

    public function test_getHeaderValue_where_header_not_exists(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertNull($requestMock->getHeaderValue('X_CUSTOM_HEADER'));
    }

    public function test_getQueryString_where_exists(): void
    {
        $query = "id=32&firstname=john&lastname=doe";

        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setServer([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING'   => $query
        ]);

        $this->assertSame($query, $requestMock->getQueryString());
    }

    public function test_getQueryString_where_not_exists(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setServer([
            'REQUEST_METHOD' => 'GET'
        ]);

        $this->assertNull($requestMock->getQueryString());
    }

    public function test_extractVariables_with_get_method_with_json_string_in_query_string(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock
            ->setMethod('GET')
            ->setHeaders(['HTTP_CONTENT_TYPE' => 'application/json'])
            ->setQuery('{"id":64,"firstname":"John","lastname":"Doe"}');

        $method = $this->getReflectedMethod('extractVariables');

        $this->assertSame([
            'id'        => 64,
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ], $method->invoke($requestMock));
    }

    public function test_extractVariables_with_get_method_with_regular_query_string(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock
            ->setMethod('GET')
            ->setQuery('id=32&firstname=john&lastname=doe');

        $method = $this->getReflectedMethod('extractVariables');

        $this->assertSame([
            'id'        => '32',
            'firstname' => 'john',
            'lastname'  => 'doe'
        ], $method->invoke($requestMock));
    }

    public function test_extractVariables_with_post_method_where_request_body_is_empty(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock
            ->setMethod('POST')
            ->setBody('');

        $method = $this->getReflectedMethod('extractVariables');

        $this->assertSame([], $method->invoke($requestMock));
    }

    public function test_extractVariables_with_post_method_with_json_string_request_body(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock
            ->setMethod('POST')
            ->setHeaders(['HTTP_CONTENT_TYPE' => 'application/json'])
            ->setBody('{"id":64,"firstname":"John","lastname":"Doe"}');

        $method = $this->getReflectedMethod('extractVariables');

        $this->assertSame([
            'id'        => 64,
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ], $method->invoke($requestMock));
    }

    public function test_extractVariables_with_post_method_with_regular_string_request_body(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock
            ->setMethod('POST')
            ->setBody('id=32&firstname=john&lastname=doe');

        $method = $this->getReflectedMethod('extractVariables');

        $this->assertSame([
            'id'        => '32',
            'firstname' => 'john',
            'lastname'  => 'doe'
        ], $method->invoke($requestMock));
    }

    //public function test_getRequestBody_w

    public function test_getAllHeaders(): void
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $requestMock->setServer([
            'HTTP_HOST'          => 'localhost',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_CONNECTION'    => 'keep-alive',
            'SERVER_NAME'        => 'localhost',
            'SERVER_PROTOCOL'    => 'HTTP/1.1',
            'REQUEST_METHOD'     => 'GET'
        ]);

        $method = $this->getReflectedMethod('getAllHeaders');

        $this->assertSame([
            'HTTP_HOST'          => 'localhost',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_CONNECTION'    => 'keep-alive'
        ], $method->invoke($requestMock));
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
        $reflectedClass = new ReflectionClass(Request::class);

        $method = $reflectedClass->getMethod($name);

        $method->setAccessible(true);

        return $method;
    }
}
