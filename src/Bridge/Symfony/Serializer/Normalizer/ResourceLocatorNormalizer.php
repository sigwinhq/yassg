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

namespace Sigwin\YASSG\Bridge\Symfony\Serializer\Normalizer;

use Sigwin\YASSG\Resource;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class ResourceLocatorNormalizer implements DenormalizerInterface
{
    private FileLocatorInterface $locator;

    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if (\is_string($data) === false) {
            throw new \LogicException('String expected');
        }

        return $this->locator->locate($data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return $type === Resource::class && \is_string($data) && str_starts_with($data, '@');
    }
}
