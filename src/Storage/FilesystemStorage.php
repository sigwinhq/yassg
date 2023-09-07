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

use Sigwin\YASSG\FileDecoder;
use Sigwin\YASSG\StorageWithOptions;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FilesystemStorage implements StorageWithOptions
{
    private FileDecoder $decoder;
    private array $roots;
    private Finder $finder;

    public function __construct(FileLocatorInterface $locator, FileDecoder $decoder, array $root, ?array $names = null)
    {
        $this->decoder = $decoder;
        $this->roots = array_map(static function (string $path) use ($locator): string {
            if (str_starts_with($path, '@')) {
                /** @phpstan-ignore-next-line PHPStan thinks this is array|string, but only string is possible */
                return $locator->locate($path);
            }

            $realpath = realpath($path);
            if ($realpath === false) {
                throw new \InvalidArgumentException(sprintf('The path "%s" does not exist.', $path));
            }

            return $realpath;
        }, $root);
        $this->finder = new Finder();
        $this->finder
            ->files()
            ->sortByName()
            ->in($this->roots)
        ;

        if ($names !== null) {
            $this->finder->name($names);
        }
    }

    public function load(): iterable
    {
        $ids = [];
        foreach ($this->finder as $file) {
            /** @var string $path */
            $path = $file->getRealPath();

            $id = str_replace($this->roots, '', $path);
            if (isset($ids[$id])) {
                continue;
            }
            $ids[$id] = true;

            yield $id => $this->decode($file);
        }
    }

    public function get(string $id): array
    {
        foreach ($this->roots as $root) {
            $path = $root.$id;
            if (file_exists($path)) {
                return $this->decode(new \SplFileObject($path));
            }
        }

        throw new \RuntimeException(sprintf('Failed to open stream: No such file: %1$s', $id));
    }

    public function has(string $id): bool
    {
        foreach ($this->roots as $root) {
            if (file_exists($root.$id)) {
                return true;
            }
        }

        return false;
    }

    public static function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['root', 'names']);
        $resolver->setRequired(['root']);
        $resolver->setAllowedTypes('root', ['array', 'string']);
        $resolver->setAllowedTypes('names', ['array', 'string']);
        $resolver->setNormalizer('root', static function (OptionsResolver $resolver, array|string $value): array {
            if (\is_string($value)) {
                $value = [$value];
            }

            return $value;
        });

        return $resolver->resolve($options);
    }

    private function decode(\SplFileInfo $file): array
    {
        if ($this->decoder->supports($file) === false) {
            throw new \RuntimeException(sprintf('Decoder does not know how to decode %1$s file', $file->getRealPath()));
        }

        return $this->decoder->decode($file);
    }
}
