<?php

namespace Carnage\JwtZendAuth\Authentication\Storage;

use Carnage\JwtZendAuth\Service\Jwt as JwtService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class JwtFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config')['jwt_zend_auth'];

        return new Jwt(
            $serviceLocator->get(JwtService::class),
            $serviceLocator->get(Header::class),
            $config['expiry']
        );
    }
}
