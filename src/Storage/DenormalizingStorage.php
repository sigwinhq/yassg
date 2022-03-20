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
    private DenormalizerInterface $denormalizer;

    /**
     * @var Storage<T>
     */
    private Storage $storage;
    /**
     * @var class-string<T>
     */
    private string $class;
    private LocaleContext $context;

    /**
     * @var array<string, array<string, array<string, T>>>
     */
    private array $cache = [];

    /**
     * @param Storage<T>      $storage
     * @param class-string<T> $class
     */
    public function __construct(DenormalizerInterface $denormalizer, Storage $storage, string $class, LocaleContext $context)
    {
        $this->denormalizer = $denormalizer;
        $this->storage = $storage;
        $this->class = $class;
        $this->context = $context;
    }

    /**
     * @return iterable<string, T>
     */
    public function load(): iterable
    {
        $context = $this->context->getLocale();
        $locale = $context[LocaleContext::LOCALE];

        foreach ($this->storage->load() as $id => $item) {
            if (isset($this->cache[$this->class][$locale][$id])) {
                yield $id => $this->cache[$this->class][$locale][$id];

                continue;
            }

            if (\is_object($item) === false) {
                $this->cache[$this->class][$locale][$id] = $this->denormalize($id, $item, $context);
            }

            yield $id => $this->cache[$this->class][$locale][$id];
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
        if (\is_object($item) === false) {
            $this->cache[$this->class][$locale][$id] = $this->denormalize($id, $item, $context);
        }

        return $this->cache[$this->class][$locale][$id];
    }

    /**
     * @return T
     */
    private function denormalize(string $id, array $data, array $context): object
    {
        try {
            return $this->denormalizer->denormalize($data, $this->class, null, $context + [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            ]);
        } catch (ExtraAttributesException $extraAttributesException) {
            throw UnexpectedAttributeException::newSelf($id, $extraAttributesException->getMessage());
        }
    }
}
