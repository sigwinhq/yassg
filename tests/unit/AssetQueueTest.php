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

namespace Sigwin\YASSG\Test;

use PHPUnit\Framework\TestCase;
use Sigwin\YASSG\Asset\AssetCopy;
use Sigwin\YASSG\Asset\AssetFetch;
use Sigwin\YASSG\AssetQueue;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @internal
 *
 * @small
 */
#[\PHPUnit\Framework\Attributes\CoversClass(AssetQueue::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(AssetCopy::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(AssetFetch::class)]
final class AssetQueueTest extends TestCase
{
    private string $tempDir;
    private AssetQueue $assetQueue;
    private Filesystem $filesystem;
    private HttpClientInterface $httpClient;

    #[\Override]
    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/yassg_test_'.uniqid();
        mkdir($this->tempDir, 0777, true);

        $this->filesystem = new Filesystem();
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->assetQueue = new AssetQueue($this->tempDir, $this->filesystem, $this->httpClient);
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->filesystem->remove($this->tempDir);
        }
    }

    public function testFlushReturnsAllAssetsIncludingExisting(): void
    {
        // Create a temporary source file
        $sourceFile = $this->tempDir.'/source.txt';
        file_put_contents($sourceFile, 'test content');

        // Create existing destination file
        $existingDestination = '/existing/asset.txt';
        $existingDestinationPath = $this->tempDir.$existingDestination;
        $this->filesystem->mkdir(\dirname($existingDestinationPath));
        file_put_contents($existingDestinationPath, 'existing content');

        // Create new destination path (doesn't exist yet)
        $newDestination = '/new/asset.txt';

        // Add assets to queue
        $existingAsset = new AssetCopy($sourceFile, $existingDestination);
        $newAsset = new AssetCopy($sourceFile, $newDestination);

        $this->assetQueue->add($existingAsset);
        $this->assetQueue->add($newAsset);

        // Track which assets were processed via callback
        $processedAssets = [];

        // Flush the queue
        $returnedAssets = $this->assetQueue->flush(static function ($asset) use (&$processedAssets): void {
            $processedAssets[] = $asset;
        });

        /**
         * Added for mutation tests.
         *
         * @psalm-suppress RedundantCondition
         *
         * @phpstan-ignore-next-line
         */
        self::assertTrue(array_is_list($returnedAssets), 'Returned assets should be a list');

        // Should return ALL assets (both existing and new) for incremental build support
        self::assertCount(2, $returnedAssets, 'Should return all assets including existing ones');
        self::assertContains($existingAsset, $returnedAssets, 'Should include existing asset in return');
        self::assertContains($newAsset, $returnedAssets, 'Should include new asset in return');

        // Verify callback was only called for new asset (existing assets are skipped from processing)
        self::assertCount(1, $processedAssets, 'Callback should only be called for newly processed assets');
        self::assertSame($newAsset, $processedAssets[0], 'Callback should be called with the new asset only');

        // Verify the new asset was actually copied
        self::assertFileExists($this->tempDir.$newDestination, 'New asset should be copied');
        self::assertSame('test content', file_get_contents($this->tempDir.$newDestination));

        // Verify existing file was not modified
        self::assertSame('existing content', file_get_contents($existingDestinationPath));
    }

    public function testFlushWithNoExistingAssets(): void
    {
        // Create a temporary source file
        $sourceFile = $this->tempDir.'/source.txt';
        file_put_contents($sourceFile, 'test content');

        // Create assets that don't exist yet
        $asset1 = new AssetCopy($sourceFile, '/new/asset1.txt');
        $asset2 = new AssetCopy($sourceFile, '/new/asset2.txt');

        $this->assetQueue->add($asset1);
        $this->assetQueue->add($asset2);

        // Track which assets were processed via callback
        $processedAssets = [];

        // Flush the queue
        $returnedAssets = $this->assetQueue->flush(static function ($asset) use (&$processedAssets): void {
            $processedAssets[] = $asset;
        });

        // Should return all assets since none existed
        self::assertCount(2, $returnedAssets, 'Should return all processed assets');
        self::assertContains($asset1, $returnedAssets);
        self::assertContains($asset2, $returnedAssets);

        // Verify callback was called for all assets
        self::assertCount(2, $processedAssets, 'Callback should be called for all processed assets');
        self::assertContains($asset1, $processedAssets);
        self::assertContains($asset2, $processedAssets);

        // Verify both assets were copied
        self::assertFileExists($this->tempDir.'/new/asset1.txt');
        self::assertFileExists($this->tempDir.'/new/asset2.txt');
    }

    public function testFlushWithAllExistingAssets(): void
    {
        // Create a temporary source file
        $sourceFile = $this->tempDir.'/source.txt';
        file_put_contents($sourceFile, 'test content');

        // Create multiple existing destination files
        $existingDestinations = ['/existing1/asset.txt', '/existing2/asset.txt'];
        $existingAssets = [];

        foreach ($existingDestinations as $destination) {
            $path = $this->tempDir.$destination;
            $this->filesystem->mkdir(\dirname($path));
            file_put_contents($path, 'existing content');

            $asset = new AssetCopy($sourceFile, $destination);
            $existingAssets[] = $asset;
            $this->assetQueue->add($asset);
        }

        // Track which assets were processed via callback
        $processedAssets = [];

        // Flush the queue
        $returnedAssets = $this->assetQueue->flush(static function ($asset) use (&$processedAssets): void {
            $processedAssets[] = $asset;
        });

        // Should return all assets even though they all exist (for incremental build support)
        self::assertCount(2, $returnedAssets, 'Should return all assets even when they all exist');
        self::assertContains($existingAssets[0], $returnedAssets);
        self::assertContains($existingAssets[1], $returnedAssets);

        // Verify callback was never called since no assets were actually processed
        self::assertEmpty($processedAssets, 'Callback should not be called when all assets already exist');

        // Verify all existing files were not modified
        foreach ($existingDestinations as $destination) {
            $path = $this->tempDir.$destination;
            self::assertSame('existing content', file_get_contents($path));
        }
    }

    public function testEmptyQueue(): void
    {
        $returnedAssets = $this->assetQueue->flush();

        self::assertEmpty($returnedAssets, 'Empty queue should return empty array');
    }
}
