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
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AssetQueue
{
    /**
     * @var array<string, AssetCopy|AssetFetch>
     */
    private array $queue = [];

    public function __construct(private string $buildDir, private Filesystem $filesystem, private HttpClientInterface $httpClient) {}

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
                $response = $this->httpClient->request('GET', $specification->url);
                $this->filesystem->mkdir(\dirname($destination));
                $handle = fopen($destination, 'w');
                if ($handle === false) {
                    throw new \RuntimeException('Failed to open file for writing');
                }
                foreach ($this->httpClient->stream($response) as $chunk) {
                    fwrite($handle, $chunk->getContent());
                }
                fclose($handle);

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
