<?php

namespace Sigwin\YASSG\Bridge\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Sigwin\YASSG\Database;
use function BenTools\CartesianProduct\cartesian_product;

class IndexExtension extends AbstractExtension
{
    private array $routes;
    private Database $database;

    public function __construct(array $routes, Database $database)
    {
        $this->routes = $routes;
        $this->database = $database;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('yassg_index', [$this, 'permute']),
        ];
    }
    
    public function permute()
    {
        foreach ($this->routes as $route => $spec) {
            $variables = [];
            if (!isset($spec['catalog'])) {
                yield $route => $spec['defaults'] ?? [];
            }

            foreach ($spec['catalog'] ?? [] as $variable => $query) {
                $variables[$variable] = $this->database->query($query);
            }

            foreach (cartesian_product($variables) as $parameters) {
                yield $route => $parameters;
            }
        }
    }
}
