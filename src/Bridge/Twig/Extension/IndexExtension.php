<?php

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Sigwin\YASSG\Permutator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Sigwin\YASSG\Database;
use function BenTools\CartesianProduct\cartesian_product;

class IndexExtension extends AbstractExtension
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
