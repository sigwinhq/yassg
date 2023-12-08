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

use Presta\SitemapBundle\Sitemap\Sitemapindex;
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

        $deflate = true;
        $index = new Sitemapindex();
        $offset = 0;
        $urlSet = null;
        foreach ($this->permutator->permute() as $location) {
            if ($urlSet !== null) {
                if ($this->generateSitemapPath($deflate, $location->getRoute()->getName(), $offset) !== $urlSet->getLoc()) {
                    $this->dumpSitemap($urlSet, $deflate);
                    $urlSet = null;
                } elseif ($urlSet->isFull()) {
                    $this->dumpSitemap($urlSet, $deflate);
                    $urlSet = null;
                    ++$offset;
                }
            }
            if ($urlSet === null) {
                $urlSet = new Urlset($this->generateSitemapPath($deflate, $location->getRoute()->getName(), $offset));
                $index->addSitemap($urlSet);
                $offset = 0;
            }

            $route = $location->getRoute();
            $url = $this->urlGenerator->generate($route->getName(), $route->getParameters() + ($indexFile ? ['_filename' => 'index.html'] : []), UrlGeneratorInterface::ABSOLUTE_URL);
            $request = $this->createRequest($url);
            if (($buildHeaders = $location->getBuildOptions()->getRequestHeaders()) !== null) {
                $request->headers->add($buildHeaders);
            }

            $this->dumpRequest($callable, $request);
            $urlSet->addUrl(new UrlConcrete($url));
        }
        if ($urlSet !== null) {
            $this->dumpSitemap($urlSet, $deflate);
        }
        $this->dumpSitemap($index, $deflate);

        // dump static files
        $this->dumpRequest($callable, $this->createRequest($this->urlGenerator->generate('error404', [], UrlGeneratorInterface::ABSOLUTE_URL)), 404);
    }

    private function createRequest(string $path): Request
    {
        return Request::create(rtrim($path, '/'))->withBaseUrl($this->urlGenerator->getContext()->getBaseUrl());
    }

    private function generateSitemapPath(bool $deflate, ?string $name = null, ?int $offset = null): string
    {
        if ($name === null) {
            return $this->generateUrl('/sitemap.xml'.($deflate ? '.gz' : ''));
        }

        return $this->generateUrl(sprintf('/sitemap-%1$s-%2$d.xml'.($deflate ? '.gz' : ''), $name, $offset ?? throw new \LogicException('Offset must be set when name is set')));
    }

    private function generateUrl(string $path): string
    {
        $context = $this->urlGenerator->getContext();

        return sprintf('%1$s://%2$s%3$s%4$s', $context->getScheme(), $context->getHost(), $context->getBaseUrl(), $path);
    }

    private function dumpRequest(callable $callable, Request $request, int $expectedStatusCode = 200): void
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

    private function dumpSitemap(Sitemapindex|Urlset $sitemap, bool $deflate): void
    {
        if ($sitemap->count() === 0) {
            return;
        }

        $path = $this->generateSitemapPath($deflate);
        if ($sitemap instanceof Urlset) {
            $path = $sitemap->getLoc();
        }

        /** @var string $content */
        $content = $deflate ? gzdeflate($sitemap->toXml()) : $sitemap->toXml();
        $this->filesystem->dumpFile($this->buildDir.str_replace($this->generateUrl(''), '', $path), $content);
    }
}
