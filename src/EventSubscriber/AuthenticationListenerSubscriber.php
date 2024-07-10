<?php

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthenticationListenerSubscriber implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if ($route === null) {
            return;
        }

        if ($this->isRouteSecured($route)) {
            $user = $this->security->getUser();
            if (!$user) {
                $event->setController(function() {
                    return new JsonResponse([
                        'message' => 'User is not authenticated',
                        'error' => 'authentication_error',
                    ], JsonResponse::HTTP_UNAUTHORIZED);
                });
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    private function isRouteSecured(string $route): bool
    {
        $securedRoutes = [
            'app_post_comment',
            'app_like'
        ];

        return in_array($route, $securedRoutes);
    }
}
