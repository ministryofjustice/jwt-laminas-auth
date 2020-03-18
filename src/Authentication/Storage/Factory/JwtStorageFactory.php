<?php

declare(strict_types=1);

namespace JwtLaminasAuth\Authentication\Storage\Factory;

use Interop\Container\ContainerInterface;
use JwtLaminasAuth\Authentication\Storage\JwtStorage;
use JwtLaminasAuth\Service\JwtService;
use Laminas\Authentication\Storage\Chain;
use Laminas\Authentication\Storage\StorageInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
        $config = $container->get('Config')['jwt_laminas_auth'];

        return new JwtStorage(
            $container->get(JwtService::class),
            $this->buildBaseStorage($container),
            $config['expiry']
        );
    }

    private function buildBaseStorage(ContainerInterface $container): StorageInterface
    {
        $config = $container->get('Config')['jwt_laminas_auth']['storage'];

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
