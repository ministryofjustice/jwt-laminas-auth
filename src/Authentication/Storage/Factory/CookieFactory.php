<?php

declare(strict_types=1);

namespace JwtZendAuth\Authentication\Storage\Factory;

use Interop\Container\ContainerInterface;
use JwtZendAuth\Authentication\Storage\Cookie;
use Zend\EventManager\EventManager;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CookieFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator): Cookie
    {
        return $this($serviceLocator, Cookie::class);
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return Cookie
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Cookie
    {
        $config = $container->get('Config')['jwt_zend_auth'];

        $config['cookieOptions']['expiry'] = $config['expiry'];

        $cookieStorage = new Cookie(
            $container->get('Request'),
            $config['cookieOptions']
        );

        /** @var EventManager $eventManager This fetches the main mvc event manager. */
        $eventManager = $container->get('Application')->getEventManager();
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
