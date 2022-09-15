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

namespace Sigwin\YASSG\Test;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sigwin\YASSG\DatabaseProvider;
use Sigwin\YASSG\Generator;
use Sigwin\YASSG\Permutator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * @internal
 *
 * @small
 *
 * @covers \Sigwin\YASSG\Generator
 *
 * @uses \Sigwin\YASSG\Bridge\Symfony\Routing\Request
 * @uses \Sigwin\YASSG\DatabaseProvider
 * @uses \Sigwin\YASSG\Permutator
 */
final class GeneratorTest extends TestCase
{
    public function testGenerator(): void
    {
        $routes = [
            'foo' => [],
        ];
        $urlGenerator = $this->mockUrlGenerator('/', $routes);
        $kernel = $this->mockKernel('/', 'body');
        $filesystem = $this->mockFilesystem('respublica//index.html', 'body');

        $this->generate(new Generator(
            'respublica',
            $this->createPermutator($routes),
            $urlGenerator,
            $kernel,
            $filesystem
        ));
    }

    public function testGeneratorWithIndexFile(): void
    {
        $routes = [
            'foo' => ['_filename' => 'index.html'],
        ];
        $urlGenerator = $this->mockUrlGenerator('/', $routes, true);
        $kernel = $this->mockKernel('/', 'body');
        $filesystem = $this->mockFilesystem('respublica//index.html', 'body');

        $this->generate(new Generator(
            'respublica',
            $this->createPermutator($routes),
            $urlGenerator,
            $kernel,
            $filesystem
        ));
    }

    public function testGeneratorWithInvalidResponseBody(): void
    {
        $routes = [
            'foo' => ['_filename' => 'index.html'],
        ];
        $urlGenerator = $this->mockUrlGenerator('/', $routes, true);
        $kernel = $this->mockKernel('/', false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No body in response');

        $this->generate(new Generator(
            'respublica',
            $this->createPermutator($routes),
            $urlGenerator,
            $kernel,
            $this->getMockBuilder(Filesystem::class)->getMock()
        ));
    }

    private function generate(Generator $generator): void
    {
        $called = false;
        $generator->generate(static function () use (&$called): void {
            $called = true;
        });
        if (! $called) {
            static::fail('Callback not called');
        }
    }

    private function createPermutator(array $routes): Permutator
    {
        return new Permutator(
            $routes,
            new DatabaseProvider($this->getMockBuilder(ContainerInterface::class)->getMock()),
            $this->getMockBuilder(ExpressionLanguage::class)->getMock()
        );
    }

    private function mockUrlGenerator(string $baseUrl, array $routes, mixed $indexFile = null): UrlGeneratorInterface
    {
        $requestContext = $this->getMockBuilder(RequestContext::class)->getMock();
        $requestContext
            ->expects(static::once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl)
        ;
        $requestContext
            ->expects(static::once())
            ->method('getParameter')
            ->with('index-file')
            ->willReturn($indexFile)
        ;

        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)->getMock();
        $urlGenerator
            ->expects(static::once())
            ->method('getContext')
            ->willReturn($requestContext)
        ;

        $remapped = [];
        foreach ($routes as $name => $params) {
            $remapped[] = array_merge([$name], [$params], [UrlGeneratorInterface::ABSOLUTE_URL]);
        }

        $urlGenerator
            ->expects(static::exactly(\count($routes)))
            ->method('generate')
            ->withConsecutive(...$remapped)
            ->willReturn('/////')
        ;

        return $urlGenerator;
    }

    private function mockKernel(string $baseUrl, string|false $body, int $status = 200): KernelInterface
    {
        $response = $this->getMockBuilder(Response::class)->getMock();
        $response
            ->expects(static::once())
            ->method('getStatusCode')
            ->willReturn($status)
        ;
        $response
            ->expects(static::once())
            ->method('getContent')
            ->willReturn($body)
        ;

        $kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $kernel
            ->expects(static::once())
            ->method('handle')
            ->with(static::callback(static function (\Symfony\Component\HttpFoundation\Request $request) use ($baseUrl): bool {
                return $request->getUri() === 'http://localhost/'.$baseUrl;
            }))
            ->willReturn($response)
        ;

        return $kernel;
    }

    private function mockFilesystem(?string $path, string $content): Filesystem
    {
        $filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystem
            ->expects(static::once())
            ->method('dumpFile')
            ->with($path, $content)
        ;

        return $filesystem;
    }
}
