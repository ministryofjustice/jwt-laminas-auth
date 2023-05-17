<?php

declare(strict_types=1);

namespace JwtLaminasAuth\Service\Factory;

use Interop\Container\ContainerInterface;
use JwtLaminasAuth\Service\JwtService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Lcobucci\JWT\Signer\Key;
use RuntimeException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

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

        $signKey = $config['signKey'];
        if (!is_object($signKey) || !$signKey instanceof Key) {
            $signKey = InMemory::plainText($signKey);
        }

        $verifyKey = $config['verifyKey'];
        if (!is_object($verifyKey) || !$verifyKey instanceof Key) {
            $verifyKey = InMemory::plainText($verifyKey);
        }

        $configuration = Configuration::forAsymmetricSigner($signer, $signKey, $verifyKey);
        $configuration->setValidationConstraints(
            new SignedWith($signer, $verifyKey),
            new LooseValidAt(SystemClock::fromUTC()),
        );

        return new JwtService(
            $configuration,
            $config['signKey']
        );
    }
}
