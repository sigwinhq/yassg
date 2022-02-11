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
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LocalizingNormalizer implements CacheableSupportsMethodInterface, ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    private const LOCALIZING_PROCESSING_NEEDED = 'sigwin_yassg_localizing_processing_needed';

    private array $classes;
    private RequestStack $requestStack;
    private TranslatorInterface $translator;

    public function __construct(array $classes, RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->classes = $classes;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (isset($this->classes[$type])) {
            $request = $this->requestStack->getMainRequest();
            if ($request === null) {
                if ($this->translator instanceof \Symfony\Component\Translation\Translator === false) {
                    // TODO: remove with Symfony 6.x being lowest
                    throw new \LogicException();
                }
                $locale = $defaultLocale = $this->translator->getLocale();
            } else {
                $locale = $request->getLocale();
                $defaultLocale = $request->getDefaultLocale();
            }

            if ( ! \is_array($data)) {
                throw new \LogicException('Localizing normalizer can only work on array input data');
            }

            foreach ($this->classes[$type] as $property) {
                $data[$property] = $data[$property][$locale] ?? $data[$property][$defaultLocale] ?? throw new \RuntimeException('Invalid localized property value '.$property);
            }

            $context[self::LOCALIZING_PROCESSING_NEEDED] = false;
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = null): bool
    {
        return isset($this->classes[$type]) && ($context[self::LOCALIZING_PROCESSING_NEEDED] ?? true) === true;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }
}
