<?php

namespace Tests\Unit\AjaxRouter\Internal;

use AmraniCh\AjaxRouter\Psr7\Psr7RequestAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class Psr7RequestAdapterTest extends TestCase
{
    public function test_getVariables_With_POST_Method_With_JSON_Response_Content_Type(): void
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

        $requestMock->expects($this->atLeastOnce())
            ->method('hasHeader')
            ->with('Content-Type')
            ->willReturn(true);

        $requestMock->expects($this->atLeastOnce())
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

        $adapter = new Psr7RequestAdapter($requestMock);

        $this->assertSame($adapter->getVariables(), [
            'id' => 64,
            'firstname' => 'John',
            'lastname' => 'Doe'
        ]);
    }

    public function test_getVariables_With_POST_Method_With_Response_Urlencoded_Content_Type(): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'hasHeader',
                'getParsedBody',
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('POST');

        $requestMock->expects($this->atLeastOnce())
            ->method('hasHeader')
            ->with('Content-Type')
            ->willReturn(true);

        $requestMock->expects($this->atLeastOnce())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/x-www-form-urlencoded;');

        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'id' => '64',
                'firstname' => 'John',
                'lastname' => 'Doe'
            ]);

        $adapter = new Psr7RequestAdapter($requestMock);

        $this->assertSame($adapter->getVariables(), [
            'id' => '64',
            'firstname' => 'John',
            'lastname' => 'Doe'
        ]);
    }

    public function test_getVariables_With_POST_Method_With_Response_FormData_Content_Type(): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'hasHeader',
                'getParsedBody',
                'getUploadedFiles',
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('POST');

        $requestMock->expects($this->atLeastOnce())
            ->method('hasHeader')
            ->with('Content-Type')
            ->willReturn(true);

        $requestMock->expects($this->atLeastOnce())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('multipart/form-data;');

        $parsedBody = [
            'id' => '64',
            'firstname' => 'John',
            'lastname' => 'Doe'
        ];

        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'id' => '64',
                'firstname' => 'John',
                'lastname' => 'Doe'
            ]);

        $uploadedFiles = [$this->getMockClass(UploadedFileInterface::class)];

        $requestMock->expects($this->once())
            ->method('getUploadedFiles')
            ->willReturn($uploadedFiles);

        $adapter = new Psr7RequestAdapter($requestMock);

        $this->assertSame([
            'id' => '64',
            'firstname' => 'John',
            'lastname' => 'Doe',
            $this->getMockClass(UploadedFileInterface::class)
        ], $adapter->getVariables());
    }

    public function test_getVariables_With_POST_Method_With_Response_Content_Urlencoded_Content_Type_With_Empty_Body(): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'hasHeader',
                'getParsedBody',
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('POST');

        $requestMock->expects($this->atLeastOnce())
            ->method('hasHeader')
            ->with('Content-Type')
            ->willReturn(true);

        $requestMock->expects($this->atLeastOnce())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/x-www-form-urlencoded;');

        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn('');

        $adapter = new Psr7RequestAdapter($requestMock);

        $this->assertEmpty($adapter->getVariables());
    }

    public function test_getVariables_With_POST_Method_Where_Reponse_Content_Type_Header_Not_Present(): void
    {
        $requestMock = $this->getMockBuilder(ServerRequestInterface::class)
            ->onlyMethods([
                'getMethod',
                'hasHeader',
            ])
            ->getMockForAbstractClass();

        $requestMock->expects($this->atLeastOnce())
            ->method('getMethod')
            ->willReturn('POST');

        $requestMock->expects($this->atLeastOnce())
            ->method('hasHeader')
            ->with('Content-Type')
            ->willReturn(false);

        $adapter = new Psr7RequestAdapter($requestMock);

        $this->assertEmpty($adapter->getVariables());
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

        $adapter = new Psr7RequestAdapter($requestMock);

        $this->assertSame($adapter->getVariables(), [
            'id' => 64,
            'firstname' => 'John',
            'lastname' => 'Doe'
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

        $adapter = new Psr7RequestAdapter($requestMock);

        $this->assertSame($adapter->getVariables(), [
            'id' => 64,
            'firstname' => 'John',
            'lastname' => 'Doe'
        ]);
    }
}
