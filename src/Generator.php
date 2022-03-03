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

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
final class Generator
{
    private string $buildDir;
    private Permutator $permutator;
    private UrlGeneratorInterface $urlGenerator;
    private KernelInterface $kernel;
    private Filesystem $filesystem;

    public function __construct(string $buildDir, Permutator $permutator, UrlGeneratorInterface $urlGenerator, KernelInterface $kernel, Filesystem $filesystem)
    {
        $this->buildDir = $buildDir;
        $this->kernel = $kernel;
        $this->urlGenerator = $urlGenerator;
        $this->permutator = $permutator;
        $this->filesystem = $filesystem;
    }

    public function generate(callable $callable): void
    {
        $indexFile = (bool) ($this->urlGenerator->getContext()->getParameter('index-file') ?? false);

        foreach ($this->permutator->permute() as $routeName => $parameters) {
            $this->dumpFile($callable, $this->urlGenerator->generate($routeName, $parameters + ($indexFile ? ['_filename' => 'index.html'] : []), UrlGeneratorInterface::RELATIVE_PATH), ! $indexFile);
        }
        $this->dumpFile($callable, '/404.html', false);
    }

    private function dumpFile(callable $callable, string $url, bool $doesNotHaveIndexFile): void
    {
        $request = Request::create($url);
        try {
            $response = $this->kernel->handle($request, HttpKernelInterface::MAIN_REQUEST, false);
        } catch (HttpException $exception) {
            throw $exception;
        }

        $body = $response->getContent();
        if ($body === false) {
            throw new \RuntimeException('No body in response');
        }
        $path = $this->buildDir.$request->getPathInfo().($doesNotHaveIndexFile ? 'index.html' : '');

        $this->filesystem->dumpFile($path, $body);

        $callable($request, $response, $path);
    }
}
