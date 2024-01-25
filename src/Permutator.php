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

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function BenTools\CartesianProduct\cartesian_product;

final readonly class Permutator
{
    public function __construct(private array $routes, private DatabaseProvider $provider, private ExpressionLanguage $expressionLanguage)
    {
    }

    /**
     * @return \Traversable<Location>
     */
    public function permute(): \Traversable
    {
        foreach ($this->routes as $route => $spec) {
            if ($spec['options']['skip'] ?? $spec['skip'] ?? false) {
                continue;
            }

            $variables = [];
            if (! isset($spec['catalog']) || $spec['catalog'] === []) {
                yield new Location(
                    new Route($route, $spec['defaults'] ?? []),
                    new BuildOptions($spec['options']['headers'] ?? null)
                );
                continue;
            }

            foreach ($spec['catalog'] as $variable => $expression) {
                $variables[$variable] = $this->expressionLanguage->evaluate(
                    $expression,
                    ['provider' => $this->provider]
                );
            }

            foreach (cartesian_product($variables) as $parameters) {
                yield new Location(
                    new Route($route, $parameters),
                    new BuildOptions($spec['options']['headers'] ?? null)
                );
            }
        }
    }
}
