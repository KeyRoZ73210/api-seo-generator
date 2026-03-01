<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ApiKeySubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly string $apiKey) {}

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Protège uniquement les routes /api/
        if (!str_starts_with($path, '/api/')) {
            return;
        }

        $clientKey = $request->headers->get('X-API-KEY');

        if (!$clientKey || $clientKey !== $this->apiKey) {
            $event->setResponse(new JsonResponse(['error' => 'Unauthorized'], 401));
        }
    }
}