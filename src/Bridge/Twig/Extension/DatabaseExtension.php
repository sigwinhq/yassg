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
    private DatabaseProvider $provider;

    public function __construct(DatabaseProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_find_all', function (string $name, array $arguments = []) {
                return $this->provider->getDatabase($name)->findAll(...$arguments);
            }),
            new TwigFunction('yassg_find_all_by', function (string $name, array $arguments = []) {
                return $this->provider->getDatabase($name)->findAllBy(...$arguments);
            }),
            new TwigFunction('yassg_find_one', function (string $name, array $arguments = []) {
                return $this->provider->getDatabase($name)->findOne(...$arguments);
            }),
            new TwigFunction('yassg_find_one_by', function (string $name, array $arguments = []) {
                return $this->provider->getDatabase($name)->findOneBy(...$arguments);
            }),
            new TwigFunction('yassg_find_one_or_null', function (string $name, array $arguments = []) {
                return $this->provider->getDatabase($name)->findOneOrNull(...$arguments);
            }),
            new TwigFunction('yassg_find_one_by_or_null', function (string $name, array $arguments = []) {
                return $this->provider->getDatabase($name)->findOneByOrNull(...$arguments);
            }),
            new TwigFunction('yassg_get', function (string $name, string $id) {
                return $this->provider->getDatabase($name)->get($id);
            }),
        ];
    }
}
