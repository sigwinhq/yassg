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

use Sigwin\YASSG\Context\LocaleContext;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class LocalizingNormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @param array<class-string, list<string>> $classes
     */
    public function __construct(private readonly array $classes)
    {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (isset($this->classes[$type])) {
            $locale = $context[LocaleContext::LOCALE];
            $fallbackLocale = $context[LocaleContext::LOCALE_FALLBACK];

            if (! \is_array($data)) {
                throw new \LogicException(\sprintf('Localizing normalizer can only work on array input data, %1$s given for %2$s', \gettype($data), $type));
            }

            foreach ($this->classes[$type] as $property) {
                if (isset($data[$property]) === false) {
                    // property not set or set to null
                    continue;
                }

                $data[$property] = $data[$property][$locale] ?? $data[$property][$fallbackLocale] ?? throw new \RuntimeException(\sprintf('Invalid localized property value %1$s::%2$s', $type, $property));
            }
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    /**
     * @param array<array-key, mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, ?array $context = null): bool
    {
        if (! isset($this->classes[$type]) || ! \is_array($data)) {
            return false;
        }

        foreach ($this->classes[$type] as $property) {
            if (isset($data[$property]) === false) {
                continue;
            }

            if (! \is_array($data[$property])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }
}
