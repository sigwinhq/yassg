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

use Sigwin\YASSG\FileDecoder;
use Sigwin\YASSG\StorageWithOptions;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FilesystemStorage implements StorageWithOptions
{
    private FileDecoder $decoder;
    private string $root;
    private Finder $finder;

    public function __construct(FileDecoder $decoder, string $root, ?array $names = null)
    {
        $this->decoder = $decoder;
        $this->root = rtrim($root, \DIRECTORY_SEPARATOR);
        $this->finder = new Finder();
        $this->finder
            ->files()
            ->in($root);

        if ($names !== null) {
            $this->finder->name($names);
        }
    }

    public function load(): iterable
    {
        foreach ($this->finder as $file) {
            /** @var string $path */
            $path = $file->getRealPath();

            yield str_replace($this->root, '', $path) => $this->decode($file);
        }
    }

    public function get(string $id): array
    {
        return $this->decode(new \SplFileObject($this->root.$id));
    }

    public static function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['root', 'names']);
        $resolver->setRequired(['root']);
        $resolver->setAllowedTypes('root', 'string');
        $resolver->setAllowedTypes('names', ['array', 'string']);

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
