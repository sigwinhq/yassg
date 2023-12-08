<?php

declare(strict_types=1);

/*
 * This file is part of the Sigwin Yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG\Test\Functional\Site\Bridge\Symfony;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class EventSubscriber implements EventSubscriberInterface
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
