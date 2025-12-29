<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 10)]
class LoginRateLimiterListener
{
    public function __construct(
        private RateLimiterFactory $loginLimiter
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Applica rate limiting solo su endpoint di login
        if ($request->getPathInfo() !== '/api/login' || $request->getMethod() !== 'POST') {
            return;
        }

        // Usa IP come identificatore
        $limiter = $this->loginLimiter->create($request->getClientIp());

        // Consuma un token
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            $event->setResponse(new JsonResponse([
                'error' => 'Troppi tentativi di login. Riprova tra ' . $limit->getRetryAfter()->getTimestamp() . ' secondi.',
                'retry_after' => $limit->getRetryAfter()->getTimestamp()
            ], 429));
        }
    }
}
