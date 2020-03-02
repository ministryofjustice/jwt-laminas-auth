<?php

namespace JwtZendAuthTest\Authentication\Storage;

use JwtZendAuth\Authentication\Storage\JwtStorage;
use JwtZendAuth\Service\JwtService;
use Lcobucci\JWT\Claim\Basic;
use Lcobucci\JWT\Token;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Authentication\Storage\StorageInterface;

class JwtTest extends MockeryTestCase
{
    /**
     * @dataProvider provideTestRead
     * @param $storageValue
     * @param $token
     * @param $expected
     */
    public function testRead($storageValue, $token, $expected)
    {
        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with($storageValue)->andReturn($token);

        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn($storageValue);

        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $this->assertEquals($expected, $sut->read());
    }

    public function provideTestRead()
    {
        $claim1 = new Basic('session-data', 'user1');
        $claim2 = new Basic('iat', date('U'));

        return [
            // no token present in underlying storage
            [null, null, null],
            //invalid token present in underlying storage
            ['token', new Token(), null],
            //token with no expiry
            ['token', new Token([], ['session-data' => $claim1]), 'user1'],
            //token which doesn't need refreshing
            ['token', new Token([], ['session-data' => $claim1, 'iat' => $claim2]), 'user1'],
        ];
    }

    /**
     * @dataProvider provideTestIsEmpty
     * @param $storageValue
     * @param $token
     * @param $expected
     */
    public function testIsEmpty($storageValue, $token, $expected)
    {
        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with($storageValue)->andReturn($token);

        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn($storageValue);

        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $this->assertEquals($expected, $sut->isEmpty());
    }

    public function provideTestIsEmpty()
    {
        $claim1 = new Basic('session-data', 'user1');
        $claim2 = new Basic('iat', date('U'));

        return [
            // no token present in underlying storage
            [null, null, true],
            //invalid token present in underlying storage
            ['token', new Token(), true],
            //token with no expiry
            ['token', new Token([], ['session-data' => $claim1]), false],
            //token which doesn't need refreshing
            ['token', new Token([], ['session-data' => $claim1, 'iat' => $claim2]), false],
        ];
    }

    public function testReadRewritesToken()
    {
        $storageValue = 'token';

        $claim1 = new Basic('session-data', 'user1');
        $claim2 = new Basic('iat', date('U') - 100);

        $token = new Token([], ['session-data' => $claim1, 'iat' => $claim2]);
        $expected = 'user1';
        $newTokenValue = 'newtoken';

        $newToken = m::mock(Token::class);
        $newToken->shouldReceive('getPayload')->andReturn($newTokenValue);
        $newToken->shouldReceive('getClaim')->with('session-data')->andReturn($claim1);

        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with($storageValue)->andReturn($token);
        $mockJwtService->shouldReceive('createSignedToken')->with('session-data', $expected, 600)->andReturn($newToken);

        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn($storageValue);
        $mockStorage->shouldReceive('write')->with($newTokenValue);

        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $this->assertEquals($expected, $sut->read());
    }

    /**
     * @dataProvider provideTestWrite
     * @param $storageValue
     * @param $token
     * @param $shouldWrite
     * @param $written
     */
    public function testWrite($storageValue, $token, $shouldWrite, $written)
    {
        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with($storageValue)->andReturn($token);

        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn($storageValue);
        if ($shouldWrite) {
            $newTokenValue = 'newtoken';

            $newToken = m::mock(Token::class);
            $newToken->shouldReceive('__toString')->andReturn($newTokenValue);

            $mockJwtService->shouldReceive('createSignedToken')->with('session-data', $written, 600)->andReturn($newToken);
            $mockStorage->shouldReceive('write')->with($newTokenValue)->once();
        }
        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $sut->write($written);
    }

    public function provideTestWrite()
    {
        $claim1 = new Basic('session-data', 'user1');

        return [
            // no token present in underlying storage
            [null, null, true, 'newValue'],
            //invalid token present in underlying storage; write new value
            ['token', new Token(), true, 'newValue'],
            //token with same value as written
            ['token', new Token([], ['session-data' => $claim1]), false, 'user1'],
            //token with different value to written
            ['token', new Token([], ['session-data' => $claim1]), true, 'newValue'],
        ];
    }

    public function testClear()
    {
        $mockJwtService = m::mock(JwtService::class);

        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('clear')->once();

        $sut = new JwtStorage($mockJwtService, $mockStorage);

        $sut->clear();
    }
}
