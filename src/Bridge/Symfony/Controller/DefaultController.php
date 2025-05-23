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

namespace Sigwin\YASSG\Bridge\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class DefaultController extends AbstractController
{
    public function __invoke(RequestStack $requestStack): Response
    {
        $request = $requestStack->getMainRequest();
        if ($request === null) {
            throw new \LogicException('Invalid request, no main request available');
        }

        $route = $request->attributes->get('_route');
        if (\is_string($route) === false) {
            throw new \LogicException('Invalid request, invalid route attribute');
        }

        /** @var string $template */
        $template = $request->attributes->get('_template') ?? \sprintf('pages/%1$s.html.twig', $route);

        return $this->render($template, $request->attributes->all());
    }
}
