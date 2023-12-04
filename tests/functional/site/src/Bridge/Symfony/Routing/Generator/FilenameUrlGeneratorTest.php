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

namespace Sigwin\YASSG\Test\Functional\Site\Bridge\Symfony\Routing\Generator;

use PHPUnit\Framework\TestCase;
use Sigwin\YASSG\Bridge\Symfony\Routing\Generator\FilenameUrlGenerator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers \Sigwin\YASSG\Bridge\Symfony\Routing\Generator\FilenameUrlGenerator
 *
 * @internal
 *
 * @small
 */
#[\PHPUnit\Framework\Attributes\Small]
#[\PHPUnit\Framework\Attributes\CoversClass(\Sigwin\YASSG\Bridge\Symfony\Routing\Generator\FilenameUrlGenerator::class)]
final class FilenameUrlGeneratorTest extends TestCase
{
    public function testCannotGenerateUnknownRoute(): void
    {
        $this->expectException(RouteNotFoundException::class);

        $generator = new FilenameUrlGenerator($this->getMockBuilder(UrlGeneratorInterface::class)->getMock(), [], []);
        $generator->generate('unknown');
    }
}
