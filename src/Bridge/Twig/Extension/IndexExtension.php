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

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Sigwin\YASSG\Permutator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IndexExtension extends AbstractExtension
{
    public function __construct(private readonly Permutator $permutator)
    {
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_index', $this->permutator->permute(...)),
        ];
    }
}
