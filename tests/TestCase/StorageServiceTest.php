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

use League\Flysystem\Config;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PhpCollective\Infrastructure\Storage\AdapterCollection;
use PhpCollective\Infrastructure\Storage\StorageAdapterFactory;
use PhpCollective\Infrastructure\Storage\StorageAdapterFactoryInterface;
use PhpCollective\Infrastructure\Storage\StorageService;

/**
 * StorageTest
 */
class StorageServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function testStorage(): void
    {
        $service = new StorageService(
            new StorageAdapterFactory(),
        );

        $this->assertFalse($service->adapters()->has('local'));

        $service->setAdapterConfigFromArray([
            'local' => [
                'class' => 'Local',
                'options' => [
                    'root' => $this->storageRoot,
                ],
            ],
        ]);

        $adapter = $service->adapter('local');
        $this->assertTrue($service->adapters()->has('local'));
        $this->assertInstanceOf(LocalFilesystemAdapter::class, $adapter);

        $result = $service->adapterFactory();
        $this->assertInstanceOf(StorageAdapterFactoryInterface::class, $result);

        $this->assertFalse($service->fileExists('local', 'doesnot'));

        $service->storeFile(
            'local',
            '/horse/photo.jpg',
            $this->getFixtureFile('titus.jpg'),
        );
        $this->assertTrue($service->fileExists('local', '/horse/photo.jpg'));

        $service->storeResource(
            'local',
            '/horse/photo2.jpg',
            fopen($this->getFixtureFile('titus.jpg'), 'rb'),
        );
        $this->assertTrue($service->fileExists('local', '/horse/photo2.jpg'));

        $service->removeFile('local', '/horse/photo.jpg');
        $this->assertFalse($service->fileExists('local', '/horse/photo.jpg'));
    }

    /**
     * @return void
     */
    public function testStoreFileUsesStreamWrite(): void
    {
        $adapter = $this->createMock(LocalFilesystemAdapter::class);
        $adapter->expects($this->once())
            ->method('writeStream')
            ->with(
                '/horse/photo.jpg',
                $this->callback(static fn ($resource): bool => is_resource($resource)),
                $this->isInstanceOf(Config::class),
            );
        $adapter->expects($this->never())
            ->method('write');

        $collection = new AdapterCollection();
        $collection->add('local', $adapter);

        $service = new StorageService(
            new StorageAdapterFactory(),
            $collection,
        );

        $service->storeFile(
            'local',
            '/horse/photo.jpg',
            $this->getFixtureFile('titus.jpg'),
        );
    }
}
