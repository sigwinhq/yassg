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

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class Permutator
{
    private array $routes;
    private DatabaseProvider $provider;
    private ExpressionLanguage $expressionLanguage;

    public function __construct(array $routes, DatabaseProvider $provider, ExpressionLanguage $expressionLanguage)
    {
        $this->routes = $routes;
        $this->provider = $provider;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function permute(): \Traversable
    {
        foreach ($this->routes as $route => $spec) {
            if ($spec['options']['skip'] ?? $spec['skip'] ?? false) {
                continue;
            }

            $variables = [];
            if ( ! isset($spec['catalog']) || $spec['catalog'] === []) {
                yield $route => $spec['defaults'] ?? [];
                continue;
            }

            foreach ($spec['catalog'] as $variable => $expression) {
                $variables[$variable] = $this->expressionLanguage->evaluate(
                    $expression,
                    ['provider' => $this->provider]
                );
            }

            foreach (cartesian_product($variables) as $parameters) {
                yield $route => $parameters;
            }
        }
    }
}
