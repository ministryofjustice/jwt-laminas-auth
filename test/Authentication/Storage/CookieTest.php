<?php

namespace Carnage\JwtZendAuthTest\Authentication\Storage;

use Carnage\JwtZendAuth\Authentication\Storage\Cookie;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Header\Cookie as CookieHeader;
use Zend\Http\Request;
use Zend\Http\Response;

class CookieTest extends MockeryTestCase
{
    /**
     * @dataProvider provideRead
     * @param $request
     * @param $expected
     */
    public function testRead($request, $expected)
    {
        $sut = new Cookie($request);
        $this->assertEquals($expected, $sut->read());
    }

    public function provideRead()
    {
        $request = new Request();
        $request->getHeaders()->addHeader(new CookieHeader([Cookie::COOKIE_NAME => 'token']));

        $emptyRequest = new Request();
        $emptyRequest->getHeaders()->addHeader(new CookieHeader([Cookie::COOKIE_NAME => '']));

        return [
            [$request, 'token'],
            [$emptyRequest, null],
            [new Request(), null],
            [new ConsoleRequest(), null]
        ];
    }

    public function testConsecutiveRead()
    {
        $request = new Request();
        $request->getHeaders()->addHeader(new CookieHeader([Cookie::COOKIE_NAME => 'token']));

        $sut = new Cookie($request);
        $this->assertEquals($sut->read(), $sut->read());
    }

    /**
     * @dataProvider provideIsEmpty
     * @param $request
     * @param $expected
     */
    public function testIsEmpty($request, $expected)
    {
        $sut = new Cookie($request);
        $this->assertEquals($expected, $sut->isEmpty());
    }

    public function provideIsEmpty()
    {
        $request = new Request();
        $request->getHeaders()->addHeader(new CookieHeader([Cookie::COOKIE_NAME => 'token']));

        $emptyRequest = new Request();
        $emptyRequest->getHeaders()->addHeader(new CookieHeader([Cookie::COOKIE_NAME => '']));

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
        $request->getHeaders()->addHeader(new CookieHeader([Cookie::COOKIE_NAME => 'token']));

        $response = new Response();

        $sut = new Cookie($request);
        $sut->close($response);

        $this->assertStringContainsString('Set-Cookie: auth=token; Expires=Thu, 01-Jan-1970 00:10:00 GMT; Path=/; Secure; HttpOnly; SameSite=lax', $response->getHeaders()->toString(), 'Header missing from response');
    }

    public function testWrite()
    {
        $request = new Request();
        $request->getHeaders()->addHeader(new CookieHeader([Cookie::COOKIE_NAME => 'token']));

        $response = new Response();

        $sut = new Cookie($request);
        $sut->write('newtoken');
        $sut->close($response);
        $this->assertStringContainsString('Set-Cookie: auth=newtoken; Expires=Thu, 01-Jan-1970 00:10:00 GMT; Path=/; Secure; HttpOnly; SameSite=lax', $response->getHeaders()->toString(), 'Header missing from response');

    }

    public function testClear()
    {
        $request = new Request();
        $request->getHeaders()->addHeader(new CookieHeader([Cookie::COOKIE_NAME => 'token']));

        $response = new Response();

        $sut = new Cookie($request);
        $sut->clear();
        $sut->close($response);

        $this->assertEmpty($response->getHeaders()->toString(), 'Header still added after clear');
    }

    public function testWriteThenRead()
    {
        $request = new Request();
        $request->getHeaders()->addHeader(new CookieHeader([Cookie::COOKIE_NAME => 'token']));

        $sut = new Cookie($request);
        $sut->write('newtoken');

        $this->assertEquals('newtoken', $sut->read());
    }
}
