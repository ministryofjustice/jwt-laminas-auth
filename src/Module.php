<?php

declare(strict_types=1);

namespace JwtLaminasAuth;

use JwtLaminasAuth\Authentication\Storage;
use JwtLaminasAuth\Authentication\Storage\Cookie;
use JwtLaminasAuth\Authentication\Storage\Factory\CookieFactory;
use JwtLaminasAuth\Authentication\Storage\Factory\HeaderFactory;
use JwtLaminasAuth\Authentication\Storage\Factory\JwtStorageFactory;
use JwtLaminasAuth\Authentication\Storage\Header;
use JwtLaminasAuth\Authentication\Storage\JwtStorage;
use JwtLaminasAuth\Service\Factory\JwtServiceFactory;
use JwtLaminasAuth\Service\JwtService;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return [
            'service_manager' => [
                'factories' => [
                    JwtStorage::class => JwtStorageFactory::class,
                    JwtService::class => JwtServiceFactory::class,
                    Header::class => HeaderFactory::class,
                    Cookie::class => CookieFactory::class,
                ]
            ],
            'jwt_laminas_auth' => [
                'signer' => Sha256::class,
                'readOnly' => false,
                'signKey' => '',
                'verifyKey' => '',
                'expiry' => 600,
                'cookieOptions' => [
                    'path' => '/',
                    'domain' => null,
                    'secure' => true,
                    'httpOnly' => true,
                ],
                'storage' => [
                    'adaptor' => Storage\Header::class,
                    'useChainAdaptor' => false,
                    'adaptors' => [],
                ],
            ]
        ];
    }

}
