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

use function BenTools\CartesianProduct\cartesian_product;

final class Permutator
{
    private array $routes;
    private Database $database;

    public function __construct(array $routes, Database $database)
    {
        $this->routes = $routes;
        $this->database = $database;
    }

    public function permute(): iterable
    {
        foreach ($this->routes as $route => $spec) {
            if ($spec['skip'] ?? false) {
                continue;
            }

            $variables = [];
            if ( ! isset($spec['catalog'])) {
                yield $route => $spec['defaults'] ?? [];
            }

            foreach ($spec['catalog'] ?? [] as $variable => $query) {
                $variables[$variable] = $this->database->query($query);
            }

            foreach (cartesian_product($variables) as $parameters) {
                yield $route => $parameters;
            }
        }
    }
}
