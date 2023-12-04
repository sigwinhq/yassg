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

namespace Sigwin\YASSG\Bridge\Symfony\Serializer\Normalizer;

use Sigwin\YASSG\Bridge\Symfony\Serializer\AttributeMetadataTrait;
use Sigwin\YASSG\Collection;
use Sigwin\YASSG\Collection\ReadOnlyCollection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class CollectionNormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use AttributeMetadataTrait;
    use DenormalizerAwareTrait;

    public function __construct(private readonly ExpressionLanguage $expressionLanguage, ClassMetadataFactoryInterface $classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Collection
    {
        $type = mb_substr($type, 0, -2);
        if ($data === null) {
            return new ReadOnlyCollection($this->expressionLanguage, $this->getProperties($type), []);
        }

        if (\is_array($data) === false) {
            throw new \LogicException('Collection normalizer only operates on arrays');
        }

        $denormalized = [];
        foreach ($data as $id => $item) {
            $denormalized[$id] = $this->denormalizer->denormalize($item, $type, $format, $context);
        }

        return new ReadOnlyCollection($this->expressionLanguage, $this->getProperties($type), $denormalized);
    }

    /**
     * @param array<array-key, mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, ?array $context = null): bool
    {
        return str_ends_with($type, '[]');
    }

    /**
     * @return array<string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }
}
