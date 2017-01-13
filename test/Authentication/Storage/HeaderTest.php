<?php

namespace Carnage\JwtZendAuthTest\Authentication\Storage;

use Carnage\JwtZendAuth\Authentication\Storage\Header;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Request;
use Zend\Http\Response;

class HeaderTest extends MockeryTestCase
{
    /**
     * @dataProvider provideRead
     * @param $request
     * @param $expected
     */
    public function testRead($request, $expected)
    {
        $sut = new Header($request);
        $this->assertEquals($expected, $sut->read());
    }

    public function provideRead()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine(Header::HEADER_NAME, 'token');

        $emptyRequest = new Request();
        $emptyRequest->getHeaders()->addHeaderLine(Header::HEADER_NAME, '');

        return [
            [$request, 'token'],
            [$emptyRequest, null],
            [new Request(), null],
            [new ConsoleRequest(), null]
        ];
    }

    /**
     * @dataProvider provideIsEmpty
     * @param $request
     * @param $expected
     */
    public function testIsEmpty($request, $expected)
    {
        $sut = new Header($request);
        $this->assertEquals($expected, $sut->isEmpty());
    }

    public function provideIsEmpty()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine(Header::HEADER_NAME, 'token');

        $emptyRequest = new Request();
        $emptyRequest->getHeaders()->addHeaderLine(Header::HEADER_NAME, '');

        return [
            [$request, false],
            [$emptyRequest, true],
            [new Request(), true],
            [new ConsoleRequest(), true]
        ];
    }

    public function testClose()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine(Header::HEADER_NAME, 'token');

        $response = new Response();

        $sut = new Header($request);
        $sut->close($response);

        $this->assertTrue($response->getHeaders()->has(Header::HEADER_NAME), 'Header missing from response');
        $this->assertEquals('Bearer token', $response->getHeaders()->get(Header::HEADER_NAME)->getFieldValue());
    }

    public function testWrite()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine(Header::HEADER_NAME, 'token');

        $response = new Response();

        $sut = new Header($request);
        $sut->write('newtoken');
        $sut->close($response);

        $this->assertTrue($response->getHeaders()->has(Header::HEADER_NAME), 'Header missing from response');
        $this->assertEquals('Bearer newtoken', $response->getHeaders()->get(Header::HEADER_NAME)->getFieldValue());
    }


    public function testClear()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine(Header::HEADER_NAME, 'token');

        $response = new Response();

        $sut = new Header($request);
        $sut->clear();
        $sut->close($response);

        $this->assertFalse($response->getHeaders()->has(Header::HEADER_NAME), 'Header still added after clear');
    }

    public function testWriteThenRead()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine(Header::HEADER_NAME, 'token');

        $sut = new Header($request);
        $sut->write('newtoken');

        $this->assertEquals('newtoken', $sut->read());
    }
}
