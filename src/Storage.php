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

/**
 * @template T of object
 */
interface Storage
{
    /**
     * @return iterable<string, array|T>
     */
    public function load(): iterable;

    /**
     * @return array|T
     */
    public function get(string $id): array|object;

    public function has(string $id): bool;
}
