<?php

declare(strict_types=1);

namespace JwtLaminasAuth\Authentication\Storage\Factory;

use Interop\Container\ContainerInterface;
use JwtLaminasAuth\Authentication\Storage\Cookie;
use Laminas\EventManager\EventManager;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
        $config = $container->get('Config')['jwt_laminas_auth'];

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
