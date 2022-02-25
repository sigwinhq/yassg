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

namespace Sigwin\YASSG\Storage;

use Sigwin\YASSG\StorageWithOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template T of object
 * @implements StorageWithOptions<T>
 */
final class MemoryStorage implements StorageWithOptions
{
    /**
     * @var array<string, array>
     */
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public static function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['values']);
        $resolver->setAllowedTypes('values', ['array']);

        return $resolver->resolve($options);
    }

    public function load(): iterable
    {
        return $this->values;
    }

    public function get(string $id): array
    {
        return $this->values[$id];
    }
}
