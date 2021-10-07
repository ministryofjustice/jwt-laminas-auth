<?php

declare(strict_types=1);

namespace JwtLaminasAuth\Service;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use RuntimeException;

class JwtService
{
    public function __construct(
        private Configuration $config,
        private string $signKey
    ) {
    }

    public function createSignedToken(string $claim, mixed $value, int $expirationSecs): Token
    {
        if (empty($this->signKey)) {
            throw new RuntimeException('Cannot sign a token, no sign key was provided');
        }

        $now = new DateTimeImmutable();
        $now = $now->setTime(intval($now->format('G')), intval($now->format('i')), intval($now->format('s')));
        return $this->config->builder()
            ->issuedAt($now)
            ->expiresAt($now->add(new DateInterval('PT' . $expirationSecs . 'S')))
            ->withClaim($claim, $value)
            ->getToken($this->config->signer(), $this->config->signingKey());
    }

    public function parseToken(string $tokenString): Token
    {
        try {
            $token = $this->config->parser()->parse($tokenString);
        } catch (InvalidArgumentException) {
            return new Token();
        }

        if (!$this->config->validator()->validate($token, ...$this->config->validationConstraints())) {
            return new Token();
        }

        return $token;
    }
}
