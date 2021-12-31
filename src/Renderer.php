<?php

namespace Sigwin\YASSG;

use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Renderer
{
    private Environment $template;
    private string $baseUrl;

    public function __construct(Database $database, Router $router, array $options = [])
    {
        $this->template->addFunction(
            new TwigFunction(
                'index', static function () use ($router, $database) {
                    return $router->permute($database);
            })
        );
    }
    
    public function permute(): iterable
    {
        foreach ($this->router->permute($this->database) as $route => $parameters) {
            $url = str_replace($this->baseUrl, '', trim($this->router->generate($route, $parameters), '/'));
            
            $response = $this->render($this->router->dispatch(Request::create($url)));
            
            yield $url => $response;
        }
    }
}
