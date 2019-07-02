<?php

namespace Carnage\JwtZendAuth\Authentication\Storage;

use Carnage\JwtZendAuth\Service\Jwt as JwtService;
use Zend\EventManager\EventManager;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CookieFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config')['jwt_zend_auth'];

        $config['cookieOptions']['expiry'] = $config['expiry'];

        $cookieStorage = new Cookie(
            $serviceLocator->get('Request'),
            $config['cookieOptions']
        );

        /** @var EventManager $eventManager This fetches the main mvc event manager. */
        $eventManager = $serviceLocator->get('Application')->getEventManager();
        $eventManager->attach(
            MvcEvent::EVENT_FINISH,
            function (MvcEvent $e) use ($cookieStorage) {
                $response = $e->getResponse();
                if ($response instanceof Response) {
                    $cookieStorage->close($response);
                }
            }
        );

        return $cookieStorage;
    }
}
