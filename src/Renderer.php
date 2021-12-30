<?php

namespace Sigwin\YASSG;

use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Renderer
{
    private Environment $template;

    public function __construct(Database $database, Router $router, array $options = [])
    {
        $path = $options['templates'];
        unset($options['templates']);
        
        $baseUrl = $options['base_url'];
        unset($options['base_url']);
        
        $this->database = $database;
        $this->router = $router;
        $this->template = new Environment(new FilesystemLoader($path), $options);

        $this->template->addFunction(
            new TwigFunction(
                'query', static function (string $query, ?string $condition = null) use ($database) {
                    return $database->query($query, $condition);
            })
        );
        $this->template->addFunction(
            new TwigFunction(
                'url', static function (array $parameters = [], ?string $name = null) use ($router) {
                    return $router->url(Request::createFromGlobals(), $parameters, $name);
            })
        );
        $this->template->addFunction(
            new TwigFunction(
                'index', static function () use ($router, $database) {
                    return $router->permute($database);
            })
        );
        $this->template->addFunction(
            new TwigFunction(
                'asset',
                static function (string $path) use ($baseUrl) {
                    return $baseUrl.'/'.ltrim($path, '/'); 
                }
            )
        );
    }

    public function render(array $context): string
    {
        return $this->template->render(sprintf('pages/%1$s.html.twig', $context['_route']), $context);
    }
    
    public function permute($baseUrl): iterable
    {
        foreach ($this->router->permute($this->database) as $route => $parameters) {
            $url = str_replace($baseUrl, '', trim($this->router->generate($route, $parameters), '/'));
            
            $response = $this->render($this->router->dispatch(Request::create($url)));
            
            yield $url => $response;
        }
    }
}
