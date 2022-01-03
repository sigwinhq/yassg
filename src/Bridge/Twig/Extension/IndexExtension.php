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

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Sigwin\YASSG\Permutator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IndexExtension extends AbstractExtension
{
    private Permutator $permutator;

    public function __construct(Permutator $permutator)
    {
        $this->permutator = $permutator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_index', [$this->permutator, 'permute']),
        ];
    }
}
