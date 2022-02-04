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
use Sigwin\YASSG\Database\MemoryDatabase;

final class Permutator
{
    private array $routes;
    private MemoryDatabase $database;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
        // $this->database = $database;
    }

    public function permute(): \Traversable
    {
        foreach ($this->routes as $route => $spec) {
            if ($spec['skip'] ?? false) {
                continue;
            }

            $variables = [];
            if ( ! isset($spec['catalog']) || $spec['catalog'] === []) {
                yield $route => $spec['defaults'] ?? [];
                continue;
            }

            foreach ($spec['catalog'] as $variable => $query) {
                $variables[$variable] = $this->database->query($query);
            }

            foreach (cartesian_product($variables) as $parameters) {
                yield $route => $parameters;
            }
        }
    }
}
