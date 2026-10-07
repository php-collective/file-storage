<?php declare(strict_types = 1);

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * @author Florian Krämer
 * @link https://github.com/Phauthentic
 * @license https://opensource.org/licenses/MIT MIT License
 */

namespace PhpCollective\Test\TestCase;

use PhpCollective\Infrastructure\Storage\Factories\LocalFactory;
use PhpCollective\Infrastructure\Storage\FileFactory;
use PhpCollective\Infrastructure\Storage\FileStorage;
use PhpCollective\Infrastructure\Storage\PathBuilder\PathBuilder;
use PhpCollective\Infrastructure\Storage\StorageAdapterFactory;
use PhpCollective\Infrastructure\Storage\StorageService;

/**
 * File Storage Test
 */
class FileStorageTest extends TestCase
{
    /**
     * @return void
     */
    public function testFileStorage(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage1' . $ds,
                ],
            ],
        ]);

        $fileStorage = new FileStorage(
            $storageService,
            new PathBuilder(),
        );

        $fileOnDisk = $this->getFixtureFile('titus.jpg');

        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1')
            ->withMetadataByKey('bar', 'foo');

        $file = $fileStorage->store($file);

        $this->assertNotEmpty($file->path());

        $file = $fileStorage->remove($file);
    }

    /**
     * @return void
     */
    public function testBuildPathMatchesStoreWithoutWriting(): void
    {
        $root = $this->storageRoot . DIRECTORY_SEPARATOR . 'storage2' . DIRECTORY_SEPARATOR;
        $storageService = new StorageService(new StorageAdapterFactory());
        $storageService->setAdapterConfigFromArray([
            'local' => ['class' => LocalFactory::class, 'options' => ['root' => $root]],
        ]);
        $fileStorage = new FileStorage($storageService, new PathBuilder());

        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1');

        $path = $fileStorage->buildPath($file)->path();

        $this->assertFileDoesNotExist($root . $path);
        $this->assertSame($path, $fileStorage->store($file)->path());

        $fileStorage->remove($fileStorage->buildPath($file));
    }

    /**
     * @return void
     */
    public function testBuildPathWithoutPathBuilderReturnsFileUnchanged(): void
    {
        $fileStorage = new FileStorage(new StorageService(new StorageAdapterFactory()));
        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')->withPath('given/path.jpg');

        $this->assertSame('given/path.jpg', $fileStorage->buildPath($file)->path());
    }
}
