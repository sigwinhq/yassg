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

namespace Sigwin\YASSG\Test\Functional\Site\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class TestController extends AbstractController
{
    public function indexAction(): Response
    {
        return new Response(__METHOD__);
    }

    public function jsonAction(string $file): JsonResponse
    {
        return new JsonResponse(['file' => $file, 'rot13' => str_rot13($file)]);
    }
}
