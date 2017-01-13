<?php

namespace Carnage\JwtZendAuth\Authentication\Storage;

use Zend\Authentication\Storage\StorageInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\RequestInterface;

class Header implements StorageInterface
{
    const HEADER_NAME = 'X-Authentication-Token';

    private $request;

    private $contents;
    private $hasRead = false;

    /**
     * Header constructor.
     * @param $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function isEmpty()
    {
        return empty($this->read());
    }

    public function read()
    {
        if (!$this->hasRead) {
            if ($this->hasHeader()) {
                $this->contents = $this->readHeader();
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
            $response->getHeaders()->addHeaderLine(self::HEADER_NAME, $this->contents);
        }
    }

    private function hasHeader()
    {
        if (!($this->request instanceof Request)) {
            return false;
        }

        return $this->request->getHeaders()->has(self::HEADER_NAME);
    }

    private function getHeader()
    {
        if (!($this->request instanceof Request)) {
            return null;
        }

        return $this->request->getHeader(self::HEADER_NAME);
    }

    private function readHeader()
    {
        return $this->getHeader()->getFieldValue();
    }
}