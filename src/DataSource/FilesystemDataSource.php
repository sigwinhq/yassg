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

namespace Sigwin\YASSG\DataSource;

use Sigwin\YASSG\DataSource;
use Sigwin\YASSG\FileDecoder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FilesystemDataSource implements DataSource
{
    private FileDecoder $decoder;
    private Finder $finder;

    public function __construct(FileDecoder $decoder, array $paths, ?array $names = null)
    {
        $this->decoder = $decoder;
        $this->finder = new Finder();
        $this->finder
            ->files()
            ->in($paths);

        if ($names !== null) {
            $this->finder->name($names);
        }
    }

    public function count(): int
    {
        return $this->finder->count();
    }

    public function get(string $id): array
    {
        return [];
    }

    public function find(): array
    {
        $files = [];
        foreach ($this->finder as $file) {
            $id = $file->getRealPath();
            if ($this->decoder->supports($file) === false) {
                throw new \RuntimeException(sprintf('Decoder does not know how to decode %1$s file', $id));
            }

            $files[$id] = $this->decoder->decode($file);
        }

        return $files;
    }

    public static function getType(): string
    {
        return 'filesystem';
    }

    public static function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['paths', 'names']);
        $resolver->setRequired(['paths']);
        $resolver->setAllowedTypes('paths', ['array', 'string']);
        $resolver->setAllowedTypes('names', ['array', 'string']);

        return $resolver->resolve($options);
    }
}
