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

final class DenormalizingStorage implements Storage
{
    private DenormalizerInterface $denormalizer;
    private Storage $storage;
    private string $class;

    public function __construct(DenormalizerInterface $denormalizer, Storage $storage, string $class)
    {
        $this->denormalizer = $denormalizer;
        $this->storage = $storage;
        $this->class = $class;
    }

    public static function resolveOptions(array $options): array
    {
        throw new \LogicException('Does not take options');
    }

    public function load(): iterable
    {
        foreach ($this->storage->load() as $id => $item) {
            try {
                yield $id => $this->denormalizer->denormalize($item, $this->class, null, [
                    AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
                ]);
            } catch (ExtraAttributesException $extraAttributesException) {
                throw UnexpectedAttributeException::newSelf($id, $extraAttributesException->getMessage());
            }
        }
        yield;
    }
}
