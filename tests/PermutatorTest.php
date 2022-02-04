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
use Sigwin\YASSG\Database\MemoryDatabase;
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
 * @uses \Sigwin\YASSG\Database\MemoryDatabase
 */
final class PermutatorTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testPermutator(array $routes, array $database, array $fixtures): void
    {
        $permutator = new Permutator($routes, new MemoryDatabase($database));
        $iterator = $permutator->permute();

        $idx = 0;
        foreach ($iterator as $key => $value) {
            static::assertArrayHasKey($idx, $fixtures);
            static::assertEquals($fixtures[$idx], [$key => $value]);

            ++$idx;
        }
        static::assertEquals(\count($fixtures), $idx);
    }

    public function dataProvider(): array
    {
        // routes, database, permutations
        return [
            [
                [], [], [],
            ],
            [
                ['a' => []], [], [['a' => []]],
            ],
            [
                ['a' => ['skip' => true], 'b' => []], [], [['b' => []]],
            ],
            [
                ['a' => ['catalog' => []]], [], [['a' => []]],
            ],
            [
                ['a' => ['catalog' => ['var' => 'path']], 'b' => ['defaults' => ['var' => 1]], 'c' => ['catalog' => []]],
                ['path' => [1, 2, 3]],
                [['a' => ['var' => 1]], ['a' => ['var' => 2]], ['a' => ['var' => 3]], ['b' => ['var' => 1]], ['c' => []]],
            ],
        ];
    }
}
