<?php

namespace Tests\Unit\AjaxDispatcher\Internal;

use AmraniCh\AjaxDispatcher\Internal\PSR7RequestAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class PSR7RequestAdapterTest extends TestCase
{
    public function test_getVariables_With_JSON_Response_Content_Type(): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'hasHeader',
                'getHeaderLine',
                'getBody',
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn('POST');

        $requestMock->expects($this->once())
            ->method('hasHeader')
            ->with('Content-Type')
            ->willReturn(true);

        $requestMock->expects($this->once())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json;');

        $streamMock = $this->getMockBuilder(StreamInterface::class)
            ->onlyMethods(['getContents'])
            ->getMockForAbstractClass();

        $streamMock->expects($this->once())
            ->method('getContents')
            ->willReturn('{"id":64,"firstname":"John","lastname":"Doe"}');

        $requestMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);

        $adapter = new PSR7RequestAdapter($requestMock);

        $this->assertSame($adapter->getVariables(), [
            'id'        => 64,
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ]);
    }

    public function test_getVariables_With_Response_Content_Type_Is_Not_JSON(): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'hasHeader',
                'getBody',
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn('POST');

        $requestMock->expects($this->once())
            ->method('hasHeader')
            ->with('Content-Type')
            ->willReturn(false);

        $streamMock = $this->getMockBuilder(StreamInterface::class)
            ->onlyMethods(['getContents'])
            ->getMockForAbstractClass();

        $streamMock->expects($this->once())
            ->method('getContents')
            ->willReturn('id=64&firstname=John&lastname=Doe');

        $requestMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);

        $adapter = new PSR7RequestAdapter($requestMock);

        $this->assertSame($adapter->getVariables(), [
            'id'        => '64',
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ]);
    }

    public function test_getVariables_Where_HTTP_Body_Is_Empty(): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'getBody',
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn('POST');

        $streamMock = $this->getMockBuilder(StreamInterface::class)
            ->onlyMethods(['getContents'])
            ->getMockForAbstractClass();

        $streamMock->expects($this->once())
            ->method('getContents')
            ->willReturn('');

        $requestMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);

        $adapter = new PSR7RequestAdapter($requestMock);

        $this->assertSame($adapter->getVariables(), []);
    }
    public function test_getVariables_With_Get_HTTP_Method_And_JSON_Response_Content_Type()
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'hasHeader',
                'getHeaderLine',
                'getUri',
                'getQueryParams'
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn('GET');

        $requestMock->expects($this->once())
            ->method('hasHeader')
            ->willReturn(true);

        $requestMock->expects($this->once())
            ->method('getHeaderLine')
            ->willReturn('application/json;');

        $uriInterfaceMock = $this->getMockBuilder(UriInterface::class)
            ->onlyMethods(['getQuery'])
            ->getMockForAbstractClass();

        $uriInterfaceMock->expects($this->once())
            ->method('getQuery')
            ->willReturn('{"id":64,"firstname":"John","lastname":"Doe"}');

        $requestMock->expects($this->once())
            ->method('getUri')
            ->willReturn($uriInterfaceMock);

        $adapter = new PSR7RequestAdapter($requestMock);

        $this->assertSame($adapter->getVariables(), [
            'id'        => 64,
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ]);
    }

    public function test_getVariables_With_Get_HTTP_Method_And_Response_Content_Type_Is_Not_JSON()
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'hasHeader',
                'getHeaderLine',
                'getUri',
                'getQueryParams'
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn('GET');

        $requestMock->expects($this->once())
            ->method('hasHeader')
            ->willReturn(true);

        $requestMock->expects($this->once())
            ->method('getHeaderLine')
            ->willReturn('application/json;');

        $uriInterfaceMock = $this->getMockBuilder(UriInterface::class)
            ->onlyMethods(['getQuery'])
            ->getMockForAbstractClass();

        $uriInterfaceMock->expects($this->once())
            ->method('getQuery')
            ->willReturn('{"id":64,"firstname":"John","lastname":"Doe"}');

        $requestMock->expects($this->once())
            ->method('getUri')
            ->willReturn($uriInterfaceMock);

        $adapter = new PSR7RequestAdapter($requestMock);

        $this->assertSame($adapter->getVariables(), [
            'id'        => 64,
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ]);
    }
}
