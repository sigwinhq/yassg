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

namespace Sigwin\YASSG;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

final class Generator
{
    private Permutator $permutator;
    private UrlGeneratorInterface $urlGenerator;
    private KernelInterface $kernel;

    public function __construct(Permutator $permutator, UrlGeneratorInterface $urlGenerator, KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->urlGenerator = $urlGenerator;
        $this->permutator = $permutator;
    }

    public function generate(string $baseUrl, callable $callable): void
    {
        // TODO: extract to config
        $buildDir = 'public';
        $this->mkdir($buildDir);

        // TODO: extract to factory
        $this->urlGenerator->setContext(RequestContext::fromUri($baseUrl));

        foreach ($this->permutator->permute() as $routeName => $parameters) {
            $request = Request::create($this->urlGenerator->generate($routeName, $parameters + ['_filename' => 'index.html'], UrlGeneratorInterface::RELATIVE_PATH));
            try {
                $response = $this->kernel->handle($request, HttpKernelInterface::MAIN_REQUEST, false);
            } catch (HttpException $exception) {
                throw $exception;
            }

            $path = $buildDir.$request->getPathInfo();
            $this->mkdir(\dirname($path));

            $callable($request, $response, $path);

            $body = $response->getContent();
            if ($body === false) {
                throw new \RuntimeException('No body in response');
            }

            file_put_contents($path, $body);
        }
    }

    private function mkdir(string $dir): void
    {
        if ( ! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }
}
