<?php

declare(strict_types=1);

namespace JwtLaminasAuth\Authentication\Storage;

use DateTimeImmutable;
use JwtLaminasAuth\Service\Exception\InvalidJwtException;
use JwtLaminasAuth\Service\JwtService as JwtService;
use Lcobucci\JWT\Token;
use OutOfBoundsException;
use RuntimeException;
use Laminas\Authentication\Storage\StorageInterface;

class JwtStorage implements StorageInterface
{
    const SESSION_CLAIM_NAME = 'session-data';
    const DEFAULT_EXPIRATION_SECS = 600;

    private bool $hasReadClaimData = false;
    private ?Token $token = null;

    public function __construct(
        private JwtService $jwt,
        private StorageInterface $wrapped,
        private int $expirationSecs = self::DEFAULT_EXPIRATION_SECS
    ) {
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->read() === null;
    }

    /**
     * @return mixed
     */
    public function read()
    {
        if (!$this->hasReadClaimData) {
            $this->hasReadClaimData = true;
            if ($this->shouldRefreshToken()) {
                $this->writeToken($this->retrieveClaim());
            }
        }

        return $this->retrieveClaim();
    }

    /**
     * @param mixed $contents
     */
    public function write($contents)
    {
        if ($contents !== $this->read()) {
            $this->writeToken($contents);
        }
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->wrapped->clear();
    }

    /**
     * @return bool
     */
    private function hasTokenValue()
    {
        return ($this->wrapped->read() !== null);
    }

    /**
     * @return Token|null
     */
    private function retrieveToken()
    {
        if ($this->token === null) {
            try {
                $this->token = $this->jwt->parseToken($this->wrapped->read());
            } catch (InvalidJwtException) {
                // If the JWT isn't valid, leave it as null
            }
        }

        return $this->token;
    }

    /**
     * @return mixed|null
     */
    private function retrieveClaim()
    {
        if (!$this->hasTokenValue()) {
            return null;
        }

        $token = $this->retrieveToken();
        if ($token === null) {
            return null;
        }

        try {
            return $token->claims()->get(self::SESSION_CLAIM_NAME);
        } catch (OutOfBoundsException $e) {
            return null;
        }
    }

    private function shouldRefreshToken(): bool
    {
        if (!$this->hasTokenValue()) {
            return false;
        }

        $token = $this->retrieveToken();
        if ($token === null) {
            return false;
        }

        $iat = $token->claims()->get('iat');

        if (!($iat instanceof DateTimeImmutable)) {
            return false;
        }

        return date('U') >= (intval($iat->format('U')) + 60) && $this->retrieveClaim() !== null;
    }

    /**
     * @param $claim
     */
    private function writeToken($claim)
    {
        try {
            $this->token = $this->jwt->createSignedToken(self::SESSION_CLAIM_NAME, $claim, $this->expirationSecs);

            $this->wrapped->write(
                $this->token->toString()
            );
        } catch (RuntimeException $e) {
        }
    }
}
