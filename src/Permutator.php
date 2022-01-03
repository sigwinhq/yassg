<?php

namespace Sigwin\YASSG;

use function BenTools\CartesianProduct\cartesian_product;

class Permutator
{
    private array $routes;
    private Database $database;

    public function __construct(array $routes, Database $database)
    {
        $this->routes = $routes;
        $this->database = $database;
    }
    
    public function permute(): iterable
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