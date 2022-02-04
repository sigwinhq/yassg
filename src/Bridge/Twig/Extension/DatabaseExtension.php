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

use Sigwin\YASSG\DatabaseProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DatabaseExtension extends AbstractExtension
{
    private DatabaseProvider $provider;

    public function __construct(DatabaseProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_query', function (string $name, ?array $arguments) {
                return $this->provider->getDatabase($name)->find(...$arguments);
            }),
        ];
    }
}
