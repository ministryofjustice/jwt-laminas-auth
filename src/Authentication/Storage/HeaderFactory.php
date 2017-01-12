<?php

namespace Carnage\JwtZendAuth\Authentication\Storage;

use Carnage\JwtZendAuth\Service\Jwt as JwtService;
use Zend\EventManager\EventManager;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HeaderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $headerStorage = new Header(
            $serviceLocator->get('Request')
        );

        /** @var EventManager $eventManager This fetches the main mvc event manager. */
        $eventManager = $serviceLocator->get('Application')->getEventManager();
        $eventManager->attach(
            MvcEvent::EVENT_FINISH,
            function (MvcEvent $e) use ($headerStorage) {
                $response = $e->getResponse();
                if ($response instanceof Response) {
                    $headerStorage->close($response);
                }
            }
        );

        return $headerStorage;
    }
}
