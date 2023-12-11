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

namespace Sigwin\YASSG\Storage;

use Sigwin\YASSG\Context\LocaleContext;
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
    /**
     * @var array<string, array<string, array<string, T>>>
     */
    private array $cache = [];

    /**
     * @param Storage<T>      $storage
     * @param class-string<T> $class
     */
    public function __construct(private readonly DenormalizerInterface $denormalizer, private readonly Storage $storage, private readonly string $class, private readonly LocaleContext $context) {}

    /**
     * @return iterable<string, T>
     */
    public function load(): iterable
    {
        $context = $this->context->getLocale();
        $locale = $context[LocaleContext::LOCALE];

        foreach ($this->storage->load() as $id => $item) {
            yield $id => $this->fetch($locale, $id, $item, $context);
        }
    }

    /**
     * @return T
     */
    public function get(string $id): object
    {
        $context = $this->context->getLocale();
        $locale = $context[LocaleContext::LOCALE];

        if (isset($this->cache[$this->class][$locale][$id])) {
            return $this->cache[$this->class][$locale][$id];
        }

        $item = $this->storage->get($id);

        return $this->fetch($locale, $id, $item, $context);
    }

    public function has(string $id): bool
    {
        return $this->storage->has($id);
    }

    /**
     * @param array|T $item
     *
     * @return T
     */
    private function fetch(string $locale, string $id, array|object $item, array $context): object
    {
        if (isset($this->cache[$this->class][$locale][$id])) {
            return $this->cache[$this->class][$locale][$id];
        }

        if (\is_object($item) === false) {
            try {
                // this avoids issues with circular references
                $this->cache[$this->class][$locale][$id] = new $this->class();
                $this->cache[$this->class][$locale][$id] = $this->denormalize($id, $item, $context);
            } catch (ExtraAttributesException $extraAttributesException) {
                throw UnexpectedAttributeException::newSelf($id, $extraAttributesException->getMessage());
            }
        } else {
            $this->cache[$this->class][$locale][$id] = $item;
        }

        return $this->cache[$this->class][$locale][$id];
    }

    /**
     * @return T
     */
    private function denormalize(string $id, array $data, array $context): object
    {
        try {
            $metadata = [];
            foreach ($data as $key => $value) {
                if (str_starts_with($key, '__')) {
                    $metadata[$key] = $value;
                    unset($data[$key]);
                }
            }
            $object = $this->denormalizer->denormalize($data, $this->class, null, $context + [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            ]);

            // attach metadata to the object as dynamic properties
            foreach ($metadata as $key => $value) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $object->{$key} = $value;
            }

            return $object;
        } catch (ExtraAttributesException $extraAttributesException) {
            throw UnexpectedAttributeException::newSelf($id, $extraAttributesException->getMessage());
        }
    }
}
