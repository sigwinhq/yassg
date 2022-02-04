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

namespace Sigwin\YASSG\Bridge\Symfony\ExpressionLanguage;

use Sigwin\YASSG\DatabaseProvider;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class FunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('yassg_query', static function (string $name): string {
                return sprintf('$provider->getDatabase(%s)', $name);
            }, static function (array $variables, string $name, array $arguments = []) {
                /** @var DatabaseProvider $provider */
                $provider = $variables['provider'];

                return $provider->getDatabase($name)->find(...$arguments);
            }),
        ];
    }
}
