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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class LocalizingNormalizer implements DenormalizerInterface, SerializerAwareInterface
{
    private array $classes;
    private ObjectNormalizer $normalizer;
    private RequestStack $requestStack;

    public function __construct(array $classes, ObjectNormalizer $normalizer, RequestStack $requestStack)
    {
        $this->classes = $classes;
        $this->normalizer = $normalizer;
        $this->requestStack = $requestStack;
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->normalizer->setSerializer($serializer);
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (isset($this->classes[$type])) {
            $request = $this->requestStack->getMainRequest();
            if ($request === null) {
                throw new \RuntimeException('Cannot fetch main request');
            }
            $locale = $request->getLocale();
            $defaultLocale = $request->getDefaultLocale();

            if ( ! \is_array($data)) {
                throw new \LogicException('Localizing normalizer can only work on array input data');
            }

            foreach ($this->classes[$type] as $property) {
                $data[$property] = $data[$property][$locale] ?? $data[$property][$defaultLocale] ?? throw new \RuntimeException('Invalid localized property value');
            }
        }

        return $this->normalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return $this->normalizer->supportsDenormalization($data, $type, $format);
    }
}
