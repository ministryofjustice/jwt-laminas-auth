<?php

namespace Carnage\JwtZendAuth;

use Carnage\JwtZendAuth\Authentication\Storage;
use Carnage\JwtZendAuth\Service;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return [
            'service_manager' => [
                'factories' => [
                    Storage\Jwt::class => Storage\JwtFactory::class,
                    Service\Jwt::class => Service\JwtFactory::class,
                    Storage\Header::class => Storage\HeaderFactory::class,
                ]
            ],
            'jwt_zend_auth' => [
                'signer' => Sha256::class,
                'readOnly' => false,
                'signKey' => '',
                'verifyKey' => '',
                'expiry' => 600
            ]
        ];
    }

}