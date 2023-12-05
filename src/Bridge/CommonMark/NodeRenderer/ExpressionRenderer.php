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

namespace Sigwin\YASSG\Bridge\CommonMark\NodeRenderer;

use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Sigwin\YASSG\Bridge\CommonMark\Node\Expression;
use Sigwin\YASSG\DatabaseProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExpressionRenderer implements NodeRendererInterface
{
    public function __construct(private ExpressionLanguage $expressionLanguage, private DatabaseProvider $provider) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        \assert($node instanceof Expression);

        /** @var Text $child */
        foreach ($node->children() as $child) {
            $expression = $child->getLiteral();
            $value = $this->expressionLanguage->evaluate($child->getLiteral(), [
                'provider' => $this->provider,
            ]);
            if (! \is_scalar($value)) {
                throw new \RuntimeException(sprintf('Expression "%1$s" did not evaluate to a scalar, got %2$s', $expression, \gettype($value)));
            }
            $child->setLiteral((string) $value);
        }

        return $childRenderer->renderNodes($node->children());
    }
}
