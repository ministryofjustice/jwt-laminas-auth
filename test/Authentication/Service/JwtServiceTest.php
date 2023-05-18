<?php

declare(strict_types=1);

namespace JwtLaminasAuthTest\Authentication\Service;

use DateTimeImmutable;
use InvalidArgumentException;
use JwtLaminasAuth\Service\Exception\InvalidJwtException;
use JwtLaminasAuth\Service\JwtService;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\Signature;
use Lcobucci\JWT\Validator;
use Lcobucci\JWT\Validation\Constraint;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class JwtServiceTest extends MockeryTestCase
{
    private Signer|MockInterface $mockSigner;
    private Key|MockInterface $mockSigningKey;
    private Key|MockInterface $mockVerificationKey;

    private Configuration $config;
    private JwtService $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockSigner = m::mock(Signer::class);
        $this->mockSigningKey = m::mock(Key::class);
        $this->mockVerificationKey = m::mock(Key::class);

        $this->config = Configuration::forAsymmetricSigner($this->mockSigner, $this->mockSigningKey, $this->mockVerificationKey);

        $this->sut = new JwtService($this->config);
    }

    public function test_createSignedToken()
    {
        $mockToken = new Plain(new DataSet([], ''), new DataSet([], ''), new Signature('', ''));

        /** @var Builder|MockInterface */
        $mockBuilder = m::mock(Builder::class);
        $mockBuilder->shouldReceive('issuedAt')->withArgs(function (DateTimeImmutable $time) {
            return $time->getTimestamp() === (new DateTimeImmutable())->getTimestamp();
        })->andReturnSelf();
        $mockBuilder->shouldReceive('expiresAt')->withArgs(function (DateTimeImmutable $time) {
            return $time->getTimestamp() === (new DateTimeImmutable())->getTimestamp() + 100;
        })->andReturnSelf();
        $mockBuilder->shouldReceive('withClaim')->with('claim', 'value')->andReturnSelf();
        $mockBuilder->shouldReceive('getToken')->with($this->mockSigner, $this->mockSigningKey)->andReturn($mockToken);

        $this->config->setBuilderFactory(function () use ($mockBuilder) {
            return $mockBuilder;
        });

        $this->mockSigningKey->shouldReceive('contents')->andReturn('my-signing-key');

        $token = $this->sut->createSignedToken('claim', 'value', 100);
        $this->assertEquals($mockToken, $token);
    }

    public function test_parseToken()
    {
        $mockToken = new Plain(new DataSet([], ''), new DataSet([], ''), new Signature('', ''));

        /** @var Parser|MockInterface */
        $mockParser = m::mock(Parser::class);
        $mockParser->shouldReceive('parse')->with('encoded jwt')->andReturn($mockToken);

        /** @var Constraint|MockInterface */
        $mockConstraint = m::mock(Constraint::class);

        /** @var Validator|MockInterface */
        $mockValidator = m::mock(Validator::class);
        $mockValidator->shouldReceive('validate')->with($mockToken, $mockConstraint)->andReturn(true);

        $this->config->setParser($mockParser);
        $this->config->setValidator($mockValidator);
        $this->config->setValidationConstraints($mockConstraint);

        $token = $this->sut->parseToken('encoded jwt');
        self::assertEquals($mockToken, $token);
    }

    public function test_parseToken_handles_invalid_parse()
    {
        /** @var Parser|MockInterface */
        $mockParser = m::mock(Parser::class);
        $mockParser->shouldReceive('parse')->with('encoded jwt')->andThrow(new InvalidArgumentException('Could not decode JWT'));

        $this->config->setParser($mockParser);

        $this->expectException(InvalidJwtException::class);
        $this->expectExceptionMessage('Could not decode JWT');
        $this->sut->parseToken('encoded jwt');
    }

    public function test_parseToken_handles_invalid_constraint()
    {
        /** @var Token|MockInterface */
        $mockToken = m::mock(Token::class);

        /** @var Parser|MockInterface */
        $mockParser = m::mock(Parser::class);
        $mockParser->shouldReceive('parse')->with('encoded jwt')->andReturn($mockToken);

        /** @var Constraint|MockInterface */
        $mockConstraint = m::mock(Constraint::class);

        /** @var Validator|MockInterface */
        $mockValidator = m::mock(Validator::class);
        $mockValidator->shouldReceive('validate')->with($mockToken, $mockConstraint)->andReturn(false);

        $this->config->setParser($mockParser);
        $this->config->setValidator($mockValidator);
        $this->config->setValidationConstraints($mockConstraint);

        $this->expectException(InvalidJwtException::class);
        $this->expectExceptionMessage('Constraints were not met');
        $this->sut->parseToken('encoded jwt');
    }
}
