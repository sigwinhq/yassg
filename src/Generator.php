<?php

namespace Sigwin\YASSG;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class Generator
{
    public function __construct(private Permutator $permutator, private UrlGeneratorInterface $urlGenerator, private KernelInterface $kernel)
    {
    }
    
    public function generate(callable $callable): void
    {
        $buildDir = 'public';
        
        $this->mkdir($buildDir);
        
        foreach ($this->permutator->permute() as $routeName => $parameters) {
            $request = Request::create($this->urlGenerator->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));
            $response = $this->kernel->handle($request);

            $path = $buildDir . $request->getPathInfo() .'/index.html';
            $this->mkdir(dirname($path));
            
            $callable($request, $response, $path);
            file_put_contents($path, $response->getContent());
        }
    }
    
    private function mkdir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }
}
