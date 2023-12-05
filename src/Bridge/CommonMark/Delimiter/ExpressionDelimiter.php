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

namespace Sigwin\YASSG\Bridge\CommonMark\Delimiter;

use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Node\Inline\AbstractStringContainer;
use League\CommonMark\Node\StringContainerInterface;
use Sigwin\YASSG\Bridge\CommonMark\Node\Expression;

final class ExpressionDelimiter implements DelimiterProcessorInterface
{
    public function getOpeningCharacter(): string
    {
        return '{';
    }

    public function getClosingCharacter(): string
    {
        return '}';
    }

    public function getMinLength(): int
    {
        return 2;
    }

    public function getDelimiterUse(DelimiterInterface $opener, DelimiterInterface $closer): int
    {
        return 2;
    }

    public function process(AbstractStringContainer $opener, AbstractStringContainer $closer, int $delimiterUse): void
    {
        $opener->insertAfter($this->expression($opener, $closer));
    }

    private function expression(AbstractStringContainer $opener, AbstractStringContainer $closer): AbstractStringContainer
    {
        $expressionNode = new Expression();

        $node = $opener->next();
        while ($node !== null && $node !== $closer) {
            if ($node instanceof StringContainerInterface === false) {
                throw new \RuntimeException('Invalid node type found');
            }
            $expressionNode->append($node);
            $expressionNode->appendChild($node);
            $node = $node->next();
        }

        return $expressionNode;
    }
}
