<?php

namespace Carnage\JwtZendAuth;

use Carnage\JwtZendAuth\Authentication\Storage;
use Carnage\JwtZendAuth\Service;
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
            ]
        ];
    }

}