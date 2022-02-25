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

use Sigwin\YASSG\Exception\UnexpectedAttributeException;
use Sigwin\YASSG\Storage;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @template T of object
 */
final class DenormalizingStorage implements Storage
{
    private DenormalizerInterface $denormalizer;
    /**
     * @var Storage<T>
     */
    private Storage $storage;
    /**
     * @var class-string<T>
     */
    private string $class;

    /**
     * @param Storage<T>      $storage
     * @param class-string<T> $class
     */
    public function __construct(DenormalizerInterface $denormalizer, Storage $storage, string $class)
    {
        $this->denormalizer = $denormalizer;
        $this->storage = $storage;
        $this->class = $class;
    }

    /**
     * @return iterable<string, T>
     */
    public function load(): iterable
    {
        foreach ($this->storage->load() as $id => $item) {
            if (\is_object($item)) {
                yield $id => $item;
                continue;
            }

            yield $id => $this->denormalize($id, $item);
        }
    }

    /**
     * @return T
     */
    public function get(string $id): object
    {
        $item = $this->storage->get($id);
        if (\is_object($item)) {
            return $item;
        }

        return $this->denormalize($id, $item);
    }

    /**
     * @return T
     */
    private function denormalize(string $id, array $data): object
    {
        try {
            return $this->denormalizer->denormalize($data, $this->class, null, [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            ]);
        } catch (ExtraAttributesException $extraAttributesException) {
            throw UnexpectedAttributeException::newSelf($id, $extraAttributesException->getMessage());
        }
    }
}
