<?php

declare(strict_types=1);

namespace JwtLaminasAuthTest\Authentication\Storage;

use JwtLaminasAuth\Authentication\Storage\JwtStorage;
use JwtLaminasAuth\Service\Exception\InvalidJwtException;
use JwtLaminasAuth\Service\JwtService;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\Signature;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Authentication\Storage\StorageInterface;
use Mockery\MockInterface;

class JwtTest extends MockeryTestCase
{
    /**
     * @dataProvider provideTestRead
     */
    public function testRead(?string $storageValue, ?Token $token, ?string $expected)
    {
        /** @var JwtService|MockInterface */
        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with($storageValue)->andReturn($token);

        /** @var StorageInterface|MockInterface */
        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn($storageValue);

        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $this->assertEquals($expected, $sut->read());
    }

    public function provideTestRead(): array
    {
        return [
            'no token present in underlying storage' => [null, null, null],
            'invalid token present in underlying storage' => [
                'token',
                new Plain(
                    new DataSet([], ''),
                    new DataSet([], ''),
                    new Signature('', '')
                ),
                null
            ],
            'token with no expiry' => [
                'token',
                new Plain(
                    new DataSet([], ''),
                    new DataSet(['session-data' => 'user1'], 'not encoded'),
                    new Signature('', '')
                ),
                'user1'
            ],
            'token which doesn\'t need refreshing' => [
                'token',
                new Plain(
                    new DataSet([], ''),
                    new DataSet(
                        [
                        'session-data' => 'user1',
                        'iat' => date('U')
                        ],
                        'not encoded'
                    ),
                    new Signature('', '')
                ),
                'user1'
            ],
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
        /** @var JwtService|MockInterface */
        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with($storageValue)->andReturn($token);

        /** @var StorageInterface|MockInterface */
        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn($storageValue);

        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $this->assertEquals($expected, $sut->isEmpty());
    }

    public function provideTestIsEmpty(): array
    {
        return [
            'no token present in underlying storage' => [null, null, true],
            'invalid token present in underlying storage' => [
                'token',
                new Plain(new DataSet([], ''), new DataSet([], ''), new Signature('', '')),
                true
            ],
            'token with no expiry' => [
                'token',
                new Plain(
                    new DataSet([], ''),
                    new DataSet(['session-data' => 'user1'], 'not encoded'),
                    new Signature('', '')
                ),
                false
            ],
            'token which doesn\'t need refreshing' => [
                'token',
                new Plain(
                    new DataSet([], ''),
                    new DataSet(
                        [
                        'session-data' => 'user1',
                        'iat' => date('U')
                        ],
                        'not encoded'
                    ),
                    new Signature('', '')
                ),
                false
            ],
        ];
    }

    public function testIsEmptyHandlesInvalidJwtException()
    {
        /** @var JwtService|MockInterface */
        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with('token')->andThrow(new InvalidJwtException('Invalid JWT'));

        /** @var StorageInterface|MockInterface */
        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn('token');

        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $this->assertEquals(true, $sut->isEmpty());
    }

    public function testReadRewritesToken()
    {
        $storageValue = 'token';

        $headers = new DataSet(['iat' => date('U') - 100], 'not encoded');
        $claims = new DataSet(['session-data' => 'user1'], 'not encoded');

        $token = new Plain($headers, $claims, new Signature('hash', 'signature'));
        $expected = 'user1';
        $newTokenValue = 'newtoken';

        /** @var Token|MockInterface */
        $newToken = m::mock(Token::class);
        $newToken->shouldReceive('claims')->andReturn($claims);

        /** @var JwtService|MockInterface */
        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with($storageValue)->andReturn($token);
        $mockJwtService->shouldReceive('createSignedToken')->with('session-data', $expected, 600)->andReturn($newToken);

        /** @var StorageInterface|MockInterface */
        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn($storageValue);
        $mockStorage->shouldReceive('write')->with($newTokenValue);

        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $this->assertEquals($expected, $sut->read());
    }

    public function testReadHandlesInvalidJwtException()
    {
        /** @var JwtService|MockInterface */
        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with('token')->andThrow(new InvalidJwtException('Invalid JWT'));

        /** @var StorageInterface|MockInterface */
        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn('token');

        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $this->assertEquals(null, $sut->read());
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
        /** @var JwtService|MockInterface */
        $mockJwtService = m::mock(JwtService::class);
        $mockJwtService->shouldReceive('parseToken')->with($storageValue)->andReturn($token);

        /** @var StorageInterface|MockInterface */
        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('read')->andReturn($storageValue);
        if ($shouldWrite) {
            $newTokenValue = 'newtoken';

            /** @var Token|MockInterface */
            $newToken = m::mock(Token::class);
            $newToken->shouldReceive('toString')->andReturn($newTokenValue);

            $mockJwtService->shouldReceive('createSignedToken')->with('session-data', $written, 600)->andReturn($newToken);
            $mockStorage->shouldReceive('write')->with($newTokenValue)->once();
        }
        $sut = new JwtStorage($mockJwtService, $mockStorage);
        $sut->write($written);
    }

    public function provideTestWrite(): array
    {
        return [
            'no token present in underlying storage' => [null, null, true, 'newValue'],
            'invalid token present in underlying storage; write new value' => [
                'token',
                new Plain(
                    new DataSet([], ''),
                    new DataSet([], ''),
                    new Signature('', '')
                ),
                true,
                'newValue'
            ],
            'token with same value as written' => [
                'token',
                new Plain(
                    new DataSet([], ''),
                    new DataSet(['session-data' => 'user1'], 'not encoded'),
                    new Signature('', '')
                ),
                false,
                'user1'
            ],
            'token with different value to written' => [
                'token',
                new Plain(
                    new DataSet([], ''),
                    new DataSet(['session-data' => 'user1'], 'not encoded'),
                    new Signature('', '')
                ),
                true,
                'newValue'
            ],
        ];
    }

    public function testClear()
    {
        /** @var JwtService|MockInterface */
        $mockJwtService = m::mock(JwtService::class);

        /** @var StorageInterface|MockInterface */
        $mockStorage = m::mock(StorageInterface::class);
        $mockStorage->shouldReceive('clear')->once();

        $sut = new JwtStorage($mockJwtService, $mockStorage);

        $sut->clear();
    }
}
