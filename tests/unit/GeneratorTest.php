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
 * @uses \Sigwin\YASSG\BuildOptions
 * @uses \Sigwin\YASSG\DatabaseProvider
 * @uses \Sigwin\YASSG\Location
 * @uses \Sigwin\YASSG\Permutator
 * @uses \Sigwin\YASSG\Route
 */
#[\PHPUnit\Framework\Attributes\Small]
#[\PHPUnit\Framework\Attributes\CoversClass(\Sigwin\YASSG\Generator::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\Sigwin\YASSG\Bridge\Symfony\Routing\Request::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\Sigwin\YASSG\BuildOptions::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\Sigwin\YASSG\DatabaseProvider::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\Sigwin\YASSG\Location::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\Sigwin\YASSG\Permutator::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\Sigwin\YASSG\Route::class)]
final class GeneratorTest extends TestCase
{
    public function testGeneratorWithoutIndexFile(): void
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

    private function generate(Generator $generator): void
    {
        $called = false;
        $generator->generate(static function () use (&$called): void {
            $called = true;
        });
        if (! $called) {
            self::fail('Callback not called');
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
            ->expects(self::atLeastOnce())
            ->method('getBaseUrl')
            ->willReturn($baseUrl)
        ;
        $requestContext
            ->expects(self::atLeastOnce())
            ->method('getParameter')
            ->with('index-file')
            ->willReturn($indexFile)
        ;

        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)->getMock();
        $urlGenerator
            ->expects(self::atLeastOnce())
            ->method('getContext')
            ->willReturn($requestContext)
        ;

        return $urlGenerator;
    }

    private function mockKernel(string $baseUrl, false|string $body, int $status = 200): KernelInterface
    {
        $response = $this->getMockBuilder(Response::class)->getMock();
        $response
            ->expects(self::atLeastOnce())
            ->method('getStatusCode')
            ->willReturn($status)
        ;
        $response
            ->expects(self::atLeastOnce())
            ->method('getContent')
            ->willReturn($body)
        ;

        $kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $kernel
            ->expects(self::atLeastOnce())
            ->method('handle')
            ->with(self::callback(static fn (\Symfony\Component\HttpFoundation\Request $request): bool => $request->getUri() === 'http://localhost/'.$baseUrl))
            ->willReturn($response)
        ;

        return $kernel;
    }

    private function mockFilesystem(?string $path, string $content): Filesystem
    {
        $filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystem
            ->expects(self::atLeastOnce())
            ->method('dumpFile')
            ->willReturnCallback(static function (string $callPath, string $callContent) use ($path, $content): bool {
                if ($path === $callPath) {
                    self::assertSame($content, $callContent);
                }

                return true;
            })
        ;

        return $filesystem;
    }
}
