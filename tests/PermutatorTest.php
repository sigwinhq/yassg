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
use Sigwin\YASSG\Database;
use Sigwin\YASSG\Permutator;

/**
 * @internal
 *
 * @small
 *
 * @group functional
 *
 * @covers \Sigwin\YASSG\Permutator
 *
 * @uses \Sigwin\YASSG\Database
 */
final class PermutatorTest extends TestCase
{
    public function testPermutatorIsEmptyByDefault(): void
    {
        $permutator = new Permutator([], new Database([]));
        
        self::assertCount(0, $permutator->permute());
    }
}
