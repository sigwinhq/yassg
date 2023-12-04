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

final class PaginatorExtension extends AbstractExtension
{
    public function __construct(private readonly DatabaseProvider $provider) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_pages', function (string $name, ?string $condition = null, ?int $limit = null) {
                $database = $this->provider->getDatabase($name);
                $count = $database->count($condition);

                return range(1, ceil($count / ($limit ?? $database->getPageLimit())));
            }),
            new TwigFunction('yassg_paginate', function (string $name, int|string $page, array $conditions = []) {
                $page = (int) $page;
                $database = $this->provider->getDatabase($name);

                $conditions['limit'] ??= $database->getPageLimit();
                $conditions['offset'] = ($page - 1) * $conditions['limit'];

                return $database->findAll(...$conditions);
            }),
        ];
    }
}
