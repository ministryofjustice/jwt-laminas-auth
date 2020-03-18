<?php

declare(strict_types=1);

namespace JwtLaminasAuth\Authentication\Storage\Factory;

use Interop\Container\ContainerInterface;
use JwtLaminasAuth\Authentication\Storage\Header;
use Laminas\EventManager\EventManager;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class HeaderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator): Header
    {
        return $this($serviceLocator, Header::class);
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return Header
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Header
    {
        $headerStorage = new Header(
            $container->get('Request')
        );

        /** @var EventManager $eventManager This fetches the main mvc event manager. */
        $eventManager = $container->get('Application')->getEventManager();
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
