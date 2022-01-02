<?php

namespace Sigwin\YASSG;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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
        // TODO: extract to config
        $buildDir = 'public';
        
        $this->mkdir($buildDir);
        
        foreach ($this->permutator->permute() as $routeName => $parameters) {
            $parameters += ['_filename' => 'index.html'];
            
            $request = Request::create($this->urlGenerator->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));
            try {
                $response = $this->kernel->handle($request, HttpKernelInterface::MAIN_REQUEST, false);
            } catch (HttpException $exception) {
                throw $exception;
            }

            $path = $buildDir . $request->getPathInfo();
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
