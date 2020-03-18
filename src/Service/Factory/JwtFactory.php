<?php

declare(strict_types=1);

namespace JwtLaminasAuth\Service\Factory;

use Interop\Container\ContainerInterface;
use JwtLaminasAuth\Service\JwtService;
use Lcobucci\JWT\Parser;
use RuntimeException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class JwtServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator): JwtService
    {
        return $this($serviceLocator, JwtService::class);
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return JwtService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): JwtService
    {
        $config = $container->get('Config')['jwt_laminas_auth'];

        $signer = new $config['signer']();

        if (empty($config['signKey']) && !$config['readOnly']) {
            throw new RuntimeException('A signing key was not provided, provide one or set to read only');
        }

        if (empty($config['verifyKey'])) {
            throw new RuntimeException('A verify key was not provided');
        }

        return new JwtService(
            $signer,
            new Parser(),
            $config['verifyKey'],
            $config['signKey']
        );
    }
}
