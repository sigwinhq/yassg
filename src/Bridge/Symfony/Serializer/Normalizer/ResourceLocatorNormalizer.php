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

use Sigwin\YASSG\Resource;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class ResourceLocatorNormalizer implements DenormalizerInterface
{
    public function __construct(private FileLocatorInterface $locator) {}

    /**
     * @return array<string>|string
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): array|string
    {
        if (\is_string($data) === false) {
            throw new \LogicException('String expected');
        }

        return $this->locator->locate($data);
    }

    /**
     * @param array<array-key, mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Resource::class && \is_string($data) && str_starts_with($data, '@');
    }

    /**
     * @return array<string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }
}
