<?php

declare(strict_types=1);

namespace JwtZendAuth\Service;

use InvalidArgumentException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use RuntimeException;

class JwtService
{
    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var string
     */
    private $verifyKey;

    /**
     * @var string
     */
    private $signKey;

    /**
     * @param Signer $signer
     * @param Parser $parser
     * @param $verifyKey
     * @param $signKey
     */
    public function __construct(Signer $signer, Parser $parser, $verifyKey, $signKey)
    {
        $this->signer = $signer;
        $this->verifyKey = $verifyKey;
        $this->signKey = $signKey;
        $this->parser = $parser;
    }

    public function createSignedToken($claim, $value, $expirationSecs)
    {
        if (empty($this->signKey)) {
            throw new RuntimeException('Cannot sign a token, no sign key was provided');
        }

        $timestamp = date('U');
        return (new Builder())
            ->setIssuedAt($timestamp)
            ->setExpiration($timestamp + $expirationSecs)
            ->set($claim, $value)
            ->sign($this->signer, $this->signKey)
            ->getToken();
    }

    public function parseToken($token)
    {
        try {
            $token = $this->parser->parse($token);
        } catch (InvalidArgumentException $invalidToken) {
            return new Token();
        }

        if (!$token->validate(new ValidationData())) {
            return new Token();
        }

        if (!$token->verify($this->signer, $this->verifyKey)) {
            return new Token();
        }

        return $token;
    }
}
