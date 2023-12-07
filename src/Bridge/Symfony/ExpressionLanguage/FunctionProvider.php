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

namespace Sigwin\YASSG\Bridge\Symfony\ExpressionLanguage;

use Sigwin\YASSG\DatabaseProvider;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class FunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('yassg_find_all', static fn (string $name): string => sprintf('$provider->getDatabase(%s)', $name), static function (array $variables, string $name, array $arguments = []) {
                /** @var DatabaseProvider $provider */
                $provider = $variables['provider'];

                return $provider->getDatabase($name)->findAll(...$arguments);
            }),
            new ExpressionFunction('yassg_pages', static fn (string $name): string => sprintf('$provider->getDatabase(%s)', $name), static function (array $variables, string $name, ?string $condition = null, ?int $limit = null) {
                /** @var DatabaseProvider $provider */
                $provider = $variables['provider'];
                $database = $provider->getDatabase($name);
                $count = $database->count($condition);

                return range(1, ceil($count / ($limit ?? $database->getPageLimit())));
            }),
            new ExpressionFunction('yassg_get', static fn (string $name): string => sprintf('$provider->getDatabase(%s)', $name), static function (array $variables, string $name, string $id) {
                /** @var DatabaseProvider $provider */
                $provider = $variables['provider'];

                return $provider->getDatabase($name)->get($id);
            }),
        ];
    }
}
