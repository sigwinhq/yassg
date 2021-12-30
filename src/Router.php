<?php

namespace Sigwin\YASSG;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use function BenTools\CartesianProduct\cartesian_product;

class Router
{
    private array $routes;
    private ?string $baseUrl;
    private RouteCollection $routeCollection;
    
    private ?array $match = null;

    public function __construct(array $routes, ?string $baseUrl = null)
    {
        $this->routes = $routes;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->routeCollection = new RouteCollection();
        foreach ($routes as $name => $route) {
            $this->routeCollection->add($name, new Route($route['path'], $route['defaults'] ?? []));
        }
    }

    public function dispatch(Request $request = null): array
    {
        $request = $request ?? Request::createFromGlobals();
        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routeCollection, $context);
        $attributes = $matcher->match($request->getPathInfo());
        
        $this->match = array_replace($attributes, $request->query->all());
        if (isset($attributes['callable'])) {
            $callable = $attributes['callable'];
            unset($attributes['callable'], $this->match['callable']);
            $this->match = array_merge($this->match, $callable($attributes));
        }
        
        return $this->match;
    }

    public function generate(string $name, array $parameters): string
    {
        $request = Request::createFromGlobals();
        $context = new RequestContext();
        $context->fromRequest($request);
        $generator = new UrlGenerator($this->routeCollection, $context);

        return $this->baseUrl . $generator->generate($name, $parameters);
    }

    public function url(Request $request, array $parameters = [], ?string $name = null): string
    {
        $context = new RequestContext();
        $context->fromRequest($request);
        $generator = new UrlGenerator($this->routeCollection, $context);

        if ($this->match !== null) {
            if ($this->match['_route'] === $name || $name === null) {
                $defaults = $this->match;
            } else {
                $defaults = [];
                
                foreach ($this->match as $key => $value) {
                    if (strpos($key, '_') === 0) {
                        $defaults[$key] = $value;
                    }
                }
            }
            foreach ($request->query->all() as $key => $value) {
                if (strpos($key, '_') === 0) {
                    $defaults[$key] = $value;
                }
            }
            
            $parameters = array_replace($defaults, $parameters);
            unset($parameters['_route']);
        }

        return $this->baseUrl . $generator->generate($name ?? $this->match['_route'], $parameters) . ($this->baseUrl ? '/index.html' : null);
    }
    
    public function permute(Database $database): iterable
    {
        foreach ($this->routes as $route => $spec) {
            $variables = [];
            if (!isset($spec['catalog'])) {
                yield $route => $spec['defaults'] ?? []; 
            }
            
            foreach ($spec['catalog'] ?? [] as $variable => $query) {
                $variables[$variable] = $database->query($query);
            }
            
            foreach (cartesian_product($variables) as $parameters) {
                yield $route => $parameters;
            }
        }
    }
}
