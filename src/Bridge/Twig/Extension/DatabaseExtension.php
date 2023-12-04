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

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Sigwin\YASSG\DatabaseProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DatabaseExtension extends AbstractExtension
{
    public function __construct(private readonly DatabaseProvider $provider) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_find_all', fn (string $name, array $arguments = []) => $this->provider->getDatabase($name)->findAll(...$arguments)),
            new TwigFunction('yassg_find_all_by', fn (string $name, array $arguments = []) => $this->provider->getDatabase($name)->findAllBy(...$arguments)),
            new TwigFunction('yassg_find_one', fn (string $name, array $arguments = []) => $this->provider->getDatabase($name)->findOne(...$arguments)),
            new TwigFunction('yassg_find_one_by', fn (string $name, array $arguments = []) => $this->provider->getDatabase($name)->findOneBy(...$arguments)),
            new TwigFunction('yassg_find_one_or_null', fn (string $name, array $arguments = []) => $this->provider->getDatabase($name)->findOneOrNull(...$arguments)),
            new TwigFunction('yassg_find_one_by_or_null', fn (string $name, array $arguments = []) => $this->provider->getDatabase($name)->findOneByOrNull(...$arguments)),
            new TwigFunction('yassg_get', fn (string $name, string $id) => $this->provider->getDatabase($name)->get($id)),
        ];
    }
}
