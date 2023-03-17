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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * @author https://gist.github.com/michelv/8218895fbaf6c325664877f4a18ca874
 */
trait ConsecutiveCallsTrait
{
    /**
     * @param array<Constraint|mixed> $expectedArgsList
     *
     * @return callback<Constraint|mixed>
     */
    private static function consecutiveCalls(array ...$expectedArgsList): Callback
    {
        return Assert::callback(static function (mixed ...$args) use ($expectedArgsList): bool {
            static $invocationCount = 0;

            $expectedArgs = $expectedArgsList[$invocationCount++];

            if (\count($args) > \count($expectedArgs)) {
                return false;
            }

            foreach ($args as $key => $arg) {
                if ($expectedArgs[$key] instanceof Constraint) {
                    Assert::assertThat($arg, $expectedArgs[$key]);
                } else {
                    Assert::assertEquals($expectedArgs[$key], $arg);
                }
            }

            return true;
        });
    }
}
