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

namespace Sigwin\YASSG;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @psalm-type TThumbnailOptions = array{source: string, destination: string}
 */
final class ThumbnailQueue
{
    /**
     * @var array<string, TThumbnailOptions>
     */
    private array $queue = [];

    public function __construct(private string $buildDir, private Filesystem $filesystem) {}

    /**
     * @param TThumbnailOptions $specification
     */
    public function add(array $specification): void
    {
        $this->queue[$specification['destination']] = $specification;
    }

    /**
     * @param callable(TThumbnailOptions): void $callable
     */
    public function flush(callable $callable): void
    {
        foreach ($this->queue as $specification) {
            $destination = $this->buildDir.'/'.ltrim($specification['destination'], '/');
            if (file_exists($destination)) {
                continue;
            }

            // TODO: ImgProxy
            $this->filesystem->copy($specification['source'], $destination);
            $callable($specification);
        }
    }
}
