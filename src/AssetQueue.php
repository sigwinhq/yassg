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

use Sigwin\YASSG\Asset\AssetCopy;
use Sigwin\YASSG\Asset\AssetFetch;
use Symfony\Component\Filesystem\Filesystem;

final class AssetQueue
{
    /**
     * @var array<string, AssetCopy|AssetFetch>
     */
    private array $queue = [];

    public function __construct(private string $buildDir, private Filesystem $filesystem) {}

    public function add(AssetCopy|AssetFetch $specification): void
    {
        $this->queue[$specification->destination] = $specification;
    }

    /**
     * @param callable(AssetCopy|AssetFetch): void $callable
     *
     * @return list<AssetCopy|AssetFetch>
     */
    public function flush(?callable $callable = null): array
    {
        foreach ($this->queue as $specification) {
            $destination = $this->buildDir.'/'.ltrim($specification->destination, '/');
            if (file_exists($destination)) {
                continue;
            }

            // TODO: ImgProxy
            if ($specification instanceof AssetFetch) {
                $this->filesystem->copy($specification->url, $destination);
                if ($callable !== null) {
                    $callable($specification);
                }
                continue;
            }

            $this->filesystem->copy($specification->source, $destination);
            if ($callable !== null) {
                $callable($specification);
            }
        }
        $queue = array_values($this->queue);
        $this->queue = [];

        return $queue;
    }
}
