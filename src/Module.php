<?php

declare(strict_types=1);

namespace JwtZendAuth;

use JwtZendAuth\Authentication\Storage;
use JwtZendAuth\Authentication\Storage\Cookie;
use JwtZendAuth\Authentication\Storage\Factory\CookieFactory;
use JwtZendAuth\Authentication\Storage\Factory\HeaderFactory;
use JwtZendAuth\Authentication\Storage\Factory\JwtStorageFactory;
use JwtZendAuth\Authentication\Storage\Header;
use JwtZendAuth\Authentication\Storage\JwtStorage;
use JwtZendAuth\Service\Factory\JwtServiceFactory;
use JwtZendAuth\Service\JwtService;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

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
            'jwt_zend_auth' => [
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
