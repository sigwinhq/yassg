<?php

declare(strict_types=1);

/*
 * This file is part of the Sigwin Yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG;

use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
    use Presta\SitemapBundle\Sitemap\Urlset;
use Sigwin\YASSG\Bridge\Symfony\Routing\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
final readonly class Generator
{
    public function __construct(private string $buildDir, private Permutator $permutator, private UrlGeneratorInterface $urlGenerator, private KernelInterface $kernel, private Filesystem $filesystem) {}

    public function generate(callable $callable): void
    {
        $requestContext = $this->urlGenerator->getContext();

        $indexFile = (bool) ($requestContext->getParameter('index-file') ?? false);

        $index = 0;
        $urlSet = null;
        foreach ($this->permutator->permute() as $location) {
            if ($urlSet !== null && $location->getRoute()->getName() !== $urlSet->getLoc()) {
                $this->dumpSitemapUrlSet($urlSet, $index);
                $urlSet = null;
            }

            if ($urlSet === null) {
                $urlSet = new Urlset($location->getRoute()->getName());
                $index = 0;
            }
            $route = $location->getRoute();
            $url = $this->urlGenerator->generate($route->getName(), $route->getParameters() + ($indexFile ? ['_filename' => 'index.html'] : []), UrlGeneratorInterface::ABSOLUTE_URL);
            $request = $this->createRequest($url);
            if (($buildHeaders = $location->getBuildOptions()->getRequestHeaders()) !== null) {
                $request->headers->add($buildHeaders);
            }

            $this->dumpFile($callable, $request);
            $urlSet->addUrl(new UrlConcrete($url));
            if ($urlSet->isFull()) {
                $this->dumpSitemapUrlSet($urlSet, $index);
                $urlSet = new Urlset($location->getRoute()->getName());
                ++$index;
            }
        }
        $this->dumpSitemapUrlSet($urlSet, $index);

        // dump static files
        $this->dumpFile($callable, $this->createRequest($this->urlGenerator->generate('error404', [], UrlGeneratorInterface::ABSOLUTE_URL)), 404);
        $this->dumpFile($callable, $this->createRequest($this->urlGenerator->generate('PrestaSitemapBundle_index', ['_format' => 'xml'], UrlGeneratorInterface::ABSOLUTE_URL)));
    }

    private function createRequest(string $path): Request
    {
        return Request::create(rtrim($path, '/'))->withBaseUrl($this->urlGenerator->getContext()->getBaseUrl());
    }

    private function dumpFile(callable $callable, Request $request, int $expectedStatusCode = 200): void
    {
        try {
            $response = $this->kernel->handle($request);
        } catch (HttpException $exception) {
            throw $exception;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== $expectedStatusCode) {
            throw new \RuntimeException(sprintf('Invalid response for %1$s, expected %2$d, got %3$d', $request->getUri(), $expectedStatusCode, $statusCode));
        }

        $body = $response->getContent();
        if ($body === false) {
            throw new \RuntimeException('No body in response');
        }
        $path = $this->buildDir.$request->getPathInfo();
        if (mb_strpos(basename($path), '.') === false) {
            $path .= '/index.html';
        }

        $path = urldecode($path);

        $this->filesystem->dumpFile($path, $body);

        $callable($request, $response, $path);
    }

    private function dumpSitemapUrlSet(Urlset $urlSet, int $index): void
    {
        if ($urlSet->count() === 0) {
            return;
        }

        $this->filesystem->dumpFile(sprintf('%1$s/sitemap-%2$s-%3$d.xml.gz', $this->buildDir, $urlSet->getLoc(), $index), gzdeflate($urlSet->toXml()));
    }
}
