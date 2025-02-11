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

namespace Sigwin\YASSG;

use Psr\Container\ContainerInterface;

final readonly class DatabaseProvider
{
    public function __construct(private ContainerInterface $locator)
    {
    }

    public function getDatabase(string $name): Database
    {
        if ($this->locator->has($name) === false) {
            throw new \LogicException(\sprintf('No such database "%1$s"', $name));
        }

        $database = $this->locator->get($name);
        if ($database instanceof Database === false) {
            throw new \LogicException(\sprintf('Service "%1$s" is not a database', $name));
        }

        return $database;
    }
}
