<?php

namespace Carnage\JwtZendAuth;

use Carnage\JwtZendAuth\Authentication\Storage;
use Carnage\JwtZendAuth\Service;
use Zend\EventManager\EventInterface;
use Zend\Http\Response;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements BootstrapListenerInterface, ConfigProviderInterface
{
    public function onBootstrap(EventInterface $e)
    {
        /** @var MvcEvent $e */
        $sl = $e->getApplication()->getServiceManager();
        /** @var Storage\Jwt $jwtStorage */
        $jwtStorage = $sl->get(Storage\Jwt::class);
        $e->getApplication()->getEventManager()->attach(
            MvcEvent::EVENT_FINISH,
            function (MvcEvent $e) use ($jwtStorage) {
                $response = $e->getResponse();
                if ($response instanceof Response) {
                    $jwtStorage->close($response);
                }
            }
        );
    }

    public function getConfig()
    {
        return [
            'service_manager' => [
                'factories' => [
                    Storage\Jwt::class => Storage\JwtFactory::class,
                    Service\Jwt::class => Service\JwtFactory::class,
                ]
            ]
        ];
    }

}