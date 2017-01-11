<?php

namespace Carnage\JwtZendAuth\Service;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class JwtFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Jwt(
            //Hmac is not known to be vulnerable to length extension attacks, but using sha384 provides extra defence
            new Sha384(),
            new Parser(),
            'secretKey123',
            'secretKey123'
        );
    }
}
