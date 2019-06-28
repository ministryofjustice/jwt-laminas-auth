<?php

namespace Carnage\JwtZendAuth\Authentication\Storage;

use Carnage\JwtZendAuth\Service\Jwt as JwtService;
use Zend\Authentication\Storage\Chain;
use Zend\Authentication\Storage\StorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class JwtFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config')['jwt_zend_auth'];

        return new Jwt(
            $serviceLocator->get(JwtService::class),
            $this->buildBaseStorage($serviceLocator),
            $config['expiry']
        );
    }

    private function buildBaseStorage(ServiceLocatorInterface $serviceLocator): StorageInterface
    {
        $config = $serviceLocator->get('Config')['jwt_zend_auth']['storage'];

        if ($config['useChainAdaptor'] !== true) {
            return $serviceLocator->get($config['adaptor']);
        }

        $chainAdaptor = new Chain();
        foreach ($config['adaptors'] as $adaptor) {
            $chainAdaptor->add($serviceLocator->get($adaptor));
        }

        return $chainAdaptor;
    }
}
