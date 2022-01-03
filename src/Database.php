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

use Adbar\Dot;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

final class Database
{
    private Dot $data;

    private ExpressionLanguage $expressionLanguage;

    public function __construct(array $data)
    {
        $this->data = dot($data);
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function query(string $query, ?string $condition = null): array
    {
        $keys = mb_substr($query, -4) === '$key';
        if ($keys) {
            $query = mb_substr($query, 0, -5);
        }

        /** @var null|array $result */
        $result = $this->data->get($query);
        if ($result === null) {
            throw new \UnexpectedValueException(sprintf('No database matches for query "%1$s"', $query));
        }
        if ($condition === null) {
            if ($keys) {
                return array_keys($result);
            }

            return $result;
        }

        $restricted = [];
        foreach ($result as $key => $item) {
            try {
                if ($this->expressionLanguage->evaluate($condition, $item) !== null) {
                    if ($keys) {
                        $restricted[] = $key;
                    } else {
                        $restricted[$key] = $item;
                    }
                }
            } catch (SyntaxError $exception) {
                throw new \UnexpectedValueException(sprintf('Syntax error while evaluating "%1$s": %2$s', $query.'.'.$key, $exception->getMessage()));
            }
        }

        return $restricted;
    }
}
