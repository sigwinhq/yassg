<?php

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Sigwin\YASSG\Database;

class DatabaseExtension extends AbstractExtension
{
    private Database $database;
    
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_query', [$this->database, 'query']),
        ];
    }
}
