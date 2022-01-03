<?php

declare(strict_types=1);

/*
 * This file is part of the yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG\Bridge\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

final class DefaultController extends AbstractController
{
    public function __invoke(RequestStack $requestStack)
    {
        $request = $requestStack->getMainRequest();

        $route = $request->attributes->get('_route');

        return $this->render(sprintf('pages/%1$s.html.twig', $route), $request->attributes->all());
    }
}
