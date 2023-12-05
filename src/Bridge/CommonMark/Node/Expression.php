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

namespace Sigwin\YASSG\Bridge\CommonMark\Node;

use League\CommonMark\Node\Inline\AbstractStringContainer;
use League\CommonMark\Node\StringContainerInterface;

final class Expression extends AbstractStringContainer
{
    public function append(StringContainerInterface $node): void
    {
        $this->literal .= $node->getLiteral();
    }
}
