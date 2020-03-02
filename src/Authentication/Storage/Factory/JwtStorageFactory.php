<?php

declare(strict_types=1);

namespace JwtZendAuth\Authentication\Storage\Factory;

use Interop\Container\ContainerInterface;
use JwtZendAuth\Authentication\Storage\JwtStorage;
use JwtZendAuth\Service\JwtService;
use Zend\Authentication\Storage\Chain;
use Zend\Authentication\Storage\StorageInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class JwtStorageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator): JwtStorage
    {
        return $this($serviceLocator, JwtStorage::class);
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return JwtStorage
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): JwtStorage
    {
        $config = $container->get('Config')['jwt_zend_auth'];

        return new JwtStorage(
            $container->get(JwtService::class),
            $this->buildBaseStorage($container),
            $config['expiry']
        );
    }

    private function buildBaseStorage(ContainerInterface $container): StorageInterface
    {
        $config = $container->get('Config')['jwt_zend_auth']['storage'];

        if ($config['useChainAdaptor'] !== true) {
            return $container->get($config['adaptor']);
        }

        $chainAdaptor = new Chain();
        foreach ($config['adaptors'] as $adaptor) {
            $chainAdaptor->add($container->get($adaptor));
        }

        return $chainAdaptor;
    }
}
