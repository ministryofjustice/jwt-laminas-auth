<?php

namespace Carnage\JwtZendAuth\Authentication\Storage;

use Carnage\JwtZendAuth\Service\Jwt as JwtService;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Http\Request;
use Zend\Http\Response;

class Jwt implements StorageInterface
{
    private static $headerName = 'X-Authentication-Token';
    private static $claimName = 'session-data';

    private $request;
    private $jwt;

    private $newClaimData = null;
    private $existingClaimData = null;
    private $hasReadClaimData = false;
    private $rewriteToken = false;


    private $expirationSecs = 3600;

    public function __construct(JwtService $jwt, Request $request)
    {
        $this->jwt = $jwt;
        $this->request = $request;
    }

    public function isEmpty()
    {
        return $this->read() !== null;
    }

    public function read()
    {
        if (!$this->hasReadClaimData) {
            $this->hasReadClaimData = true;
            $this->existingClaimData = $this->readClaimFromHeader();
        }

        return $this->existingClaimData;
    }

    public function write($contents)
    {
        $this->newClaimData = $contents;
        if ($contents !== $this->read()) {
            $this->rewriteToken = true;
        }
    }

    public function clear()
    {
        $this->newClaimData = null;
        $this->rewriteToken = true;
    }

    /** should be called on MVC EVENT FINAL */
    public function close(Response $response)
    {
        if ($this->rewriteToken) {
            $headerValue = $this->jwt->createSignedToken(self::$claimName, $this->newClaimData, $this->expirationSecs);
        } elseif ($this->shouldRefreshToken()) {
            $headerValue = $this->jwt->createSignedToken(self::$claimName, $this->read(), $this->expirationSecs);
        } elseif (!$this->hasTokenHeader()) {
            return;
        } else {
            $headerValue = $this->getTokenHeader()->getFieldValue();
        }

        $response->getHeaders()->addHeaderLine(self::$headerName, $headerValue);
    }

    private function hasTokenHeader()
    {
        return $this->request->getHeaders()->has(self::$headerName);
    }

    private function getTokenHeader()
    {
        return $this->request->getHeader(self::$headerName);
    }

    private function readClaimFromHeader()
    {
        if (!$this->hasTokenHeader()) {
            return null;
        }

        $token = $this->getTokenHeader()->getFieldValue();
        return $this->jwt->retrieveClaim($token, self::$claimName);
    }

    private function shouldRefreshToken()
    {
        if (!$this->hasTokenHeader()) {
            return false;
        }

        $token = $this->getTokenHeader()->getFieldValue();
        return date('U') >= ($this->jwt->retrieveClaim($token, 'iat') + 60);
    }
}