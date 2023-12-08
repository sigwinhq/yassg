<?php

namespace Sigwin\YASSG\Test\Functional\Site\Bridge\Symfony;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['kernel.response' => 'onResponse'];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->add(['Last-Modified' => gmdate('D, d M Y H:i:s', strtotime('2021-12-31 00:00:00')).' GMT']);
    }
}
