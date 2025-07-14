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

use function BenTools\CartesianProduct\combinations;

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
        /** @var array{
         *     catalog?: array<string, string>,
         *     defaults?: array<string, string>,
         *     options?: array{headers?: array<string, string>, skip?: bool}, skip?: bool
         * } $spec
         */
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
                $catalog = $this->expressionLanguage->evaluate(
                    $expression,
                    ['provider' => $this->provider]
                );
                if (! is_iterable($catalog)) {
                    throw new \InvalidArgumentException('Catalog for variable "'.$variable.'" must be iterable, got '.\gettype($catalog));
                }
                $variables[$variable] = $catalog;
            }

            foreach (combinations($variables) as $parameters) {
                yield new Location(
                    new Route($route, $parameters),
                    new BuildOptions($spec['options']['headers'] ?? null)
                );
            }
        }
    }
}
