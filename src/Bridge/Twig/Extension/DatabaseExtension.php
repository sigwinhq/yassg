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

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Sigwin\YASSG\Database\MemoryDatabase;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DatabaseExtension extends AbstractExtension
{
    private MemoryDatabase $database;

    public function __construct(MemoryDatabase $database)
    {
        $this->database = $database;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_query', [$this->database, 'query']),
        ];
    }
}
