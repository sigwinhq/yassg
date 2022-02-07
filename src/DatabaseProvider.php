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

namespace Sigwin\YASSG;

final class DatabaseProvider
{
    /**
     * @var array<Database>
     */
    private array $databases;

    /**
     * @param array<Database> $databases
     */
    public function __construct(array $databases)
    {
        $this->databases = $databases;
    }

    public function getDatabase(string $name): Database
    {
        if (isset($this->databases[$name]) === false) {
            throw new \LogicException(sprintf('No such database "%1$s"', $name));
        }

        return $this->databases[$name];
    }
}
