<?php

namespace Carnage\JwtZendAuth\Service;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\ValidationData;

class Jwt
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
        $timestamp = date('U');
        return (new Builder())
            ->setIssuedAt($timestamp)
            ->setExpiration($timestamp + $expirationSecs)
            ->set($claim, $value)
            ->sign($this->signer, $this->signKey)
            ->getToken();
    }

    public function retrieveClaim($token, $claim)
    {
        try {
            $token = $this->parser->parse($token);
        } catch (\InvalidArgumentException $invalidToken) {
            return null;
        }
        if (!$token->validate(new ValidationData())) {
            return null;
        }
        if (!$token->verify($this->signer, $this->verifyKey)) {
            return null;
        }
        return $token->getClaim($claim);
    }
}