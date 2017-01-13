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
        $config = $serviceLocator->get('Config')['jwt_zend_auth'];

        $signer = new $config['signer']();

        if (empty($config['signKey']) && !$config['readOnly']) {
            throw new \RuntimeException('A signing key was not provided, provide one or set to read only');
        }

        if (empty($config['verifyKey'])) {
            throw new \RuntimeException('A verify key was not provided');
        }

        return new Jwt(
            $signer,
            new Parser(),
            $config['verifyKey'],
            $config['signKey']
        );
    }
}
