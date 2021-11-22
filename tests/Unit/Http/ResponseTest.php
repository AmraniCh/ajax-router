<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use AmraniCh\AjaxDispatcher\Http\Response;

class ResponseTest extends TestCase
{
    public function test_getStatusCode(): void
    {
        $responeMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $responeMock->setStatusCode(500);

        $this->assertSame(500, $responeMock->getStatusCode());
    }

    public function test_setStatusCode(): void
    {
        $responeMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($responeMock, $responeMock->setStatusCode(400));
        $this->assertSame(400, $responeMock->getStatusCode());
    }

    public function test_getBody(): void
    {
        $responeMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $body = 'Something created successfully!';

        $responeMock->setBody($body);

        $this->assertSame($body, $responeMock->getBody());
    }

    public function test_setBody(): void
    {
        $responeMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $body = 'Something created successfully!';

        $this->assertSame($responeMock, $responeMock->setBody($body));
        $this->assertSame($body, $responeMock->getBody());
    }

    public function test_getHeaders(): void
    {
        $responeMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $headers = [
            'X_CUSTOM_HEADER_1' => 'value',
            'X_CUSTOM_HEADER_2' => 'value'
        ];

        $responeMock->setHeaders($headers);

        $this->assertSame($headers, $responeMock->getHeaders());
    }

    public function test_addHeader(): void
    {
        $responeMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($responeMock, $responeMock->addHeader('X_CUSTOM_HEADER_1', 'value'));
        $this->assertArrayHasKey('X_CUSTOM_HEADER_1', $responeMock->getHeaders());
        $this->assertSame('value', $responeMock->getHeaders()['X_CUSTOM_HEADER_1']);
    }

    public function test_send(): void
    {
        $responeMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setResponseCode', 'sendRawHeaders'])
            ->getMock();

        $responeMock->expects($this->once())
            ->method('setResponseCode');

        $responeMock->expects($this->once())
            ->method('sendRawHeaders');

        $body = "hello world!";

        $responeMock->setBody($body);

        $this->expectOutputString($body);
        $this->assertSame($responeMock, $responeMock->send());
    }

    public function test_sendJson(): void
    {
        $responeMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setResponseCode', 'sendRawHeaders'])
            ->getMock();

        $responeMock->expects($this->once())
            ->method('setResponseCode');

        $responeMock->expects($this->once())
            ->method('sendRawHeaders');

        $body = [
            'id'        => '32',
            'firstname' => 'john',
            'lastname'  => 'doe'
        ];

        $responeMock->setBody($body);

        $this->expectOutputString(json_encode($body));
        $this->assertSame($responeMock, $responeMock->sendJson());

        $this->assertArrayHasKey('Content-type', $responeMock->getHeaders());
        $this->assertSame('application/json', $responeMock->getHeaders()['Content-type']);
    }

    public function test_json_static(): void
    {
        $body    = 'hello world!';
        $code    = 200;
        $headers = [
            'X-Header-one' => 'value 1',
            'X-Header-two' => 'value 2',
        ];

        $response = Response::json($body, $code, $headers);

        $this->assertSame(json_encode($body), $response->getBody());
        $this->assertSame($code, $response->getStatusCode());

        $this->assertArrayHasKey('X-Header-one', $response->getHeaders());
        $this->assertArrayHasKey('X-Header-two', $response->getHeaders());

        $this->assertSame('value 1', $headers['X-Header-one']);
        $this->assertSame('value 2', $headers['X-Header-two']);
    }

    public function test_raw_static(): void
    {
        $body    = 'hello world!';
        $code    = 200;
        $headers = [
            'X-Header-one' => 'value 1',
            'X-Header-two' => 'value 2',
        ];

        $response = Response::raw($body, $code, $headers);

        $this->assertSame($body, $response->getBody());
        $this->assertSame($code, $response->getStatusCode());

        $this->assertArrayHasKey('X-Header-one', $response->getHeaders());
        $this->assertArrayHasKey('X-Header-two', $response->getHeaders());

        $this->assertSame('value 1', $headers['X-Header-one']);
        $this->assertSame('value 2', $headers['X-Header-two']);
    }
}
