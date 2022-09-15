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

use Sigwin\YASSG\Context\LocaleContext;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class LocalizingNormalizer implements CacheableSupportsMethodInterface, DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;
    private const LOCALIZING_NORMALIZER_LAST_TYPE = 'sigwin_yassg_localizing_normalizer_last_type';

    private array $classes;

    public function __construct(array $classes)
    {
        $this->classes = $classes;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (isset($this->classes[$type])) {
            $locale = $context[LocaleContext::LOCALE];
            $fallbackLocale = $context[LocaleContext::LOCALE_FALLBACK];

            if (! \is_array($data)) {
                throw new \LogicException('Localizing normalizer can only work on array input data');
            }

            foreach ($this->classes[$type] as $property) {
                if (isset($data[$property]) === false) {
                    // property not set or set to null
                    continue;
                }

                $data[$property] = $data[$property][$locale] ?? $data[$property][$fallbackLocale] ?? throw new \RuntimeException('Invalid localized property value '.$property);
            }
        }
        $context[self::LOCALIZING_NORMALIZER_LAST_TYPE] = $type;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = null): bool
    {
        return isset($this->classes[$type]) && ($context[self::LOCALIZING_NORMALIZER_LAST_TYPE] ?? null) !== $type;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }
}
