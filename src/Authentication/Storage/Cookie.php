<?php

declare(strict_types=1);

namespace JwtLaminasAuth\Authentication\Storage;

use Laminas\Authentication\Storage\StorageInterface;
use Laminas\Http\Header\SetCookie;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Stdlib\RequestInterface;

class Cookie implements StorageInterface
{
    const COOKIE_NAME = 'auth';

    private $request;

    private $contents;
    private $hasRead = false;

    private $cookieOptions = [
        'path' => '/',
        'domain' => null,
        'secure' => true,
        'expiry' => 600,
        'httpOnly' => true,
    ];

    public function __construct(RequestInterface $request, array $cookieOptions = [])
    {
        $this->request = $request;
        $this->cookieOptions = array_merge($this->cookieOptions, $cookieOptions);
    }

    public function isEmpty()
    {
        return empty($this->read());
    }

    public function read()
    {
        if (!$this->hasRead) {
            if ($this->hasCookie()) {
                $this->contents = $this->readCookie();
            }
            $this->hasRead = true;
        }

        return $this->contents;
    }

    public function write($contents)
    {
        $this->contents = $contents;
        $this->hasRead = true;
    }

    public function clear()
    {
        $this->contents = null;
        $this->hasRead = true;
    }

    public function close(Response $response)
    {
        if (!$this->hasRead) {
            $this->read();
        }

        if ($this->contents !== null) {
            $cookie = new class(
                self::COOKIE_NAME,
                $this->contents,
                $this->cookieOptions['expiry'], //expires
                $this->cookieOptions['path'], //path
                $this->cookieOptions['domain'], // domain
                $this->cookieOptions['secure'], //secure
                $this->cookieOptions['httpOnly'] // httponly
            ) extends SetCookie {
                public function getFieldValue()
                {
                    return parent::getFieldValue() . '; SameSite=lax';
                }
            };

            $response->getHeaders()->addHeader($cookie);
        }
    }

    private function hasCookie()
    {
        if (!($this->request instanceof Request)) {
            return false;
        }

        return $this->request->getHeaders()->has('Cookie') && $this->request->getCookie()->offsetExists(self::COOKIE_NAME);
    }

    private function readCookie()
    {
        if (!($this->request instanceof Request)) {
            return null;
        }

        return $this->request->getCookie()->offsetGet(self::COOKIE_NAME);
    }
}
