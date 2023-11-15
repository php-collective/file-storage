<?php
declare(strict_types=1);

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

namespace PhpCollective\Storage\Test\TestCase;

use League\Flysystem\Adapter\Local;
use PhpCollective\Infrastructure\Storage\StorageAdapterFactory;
use PhpCollective\Infrastructure\Storage\StorageAdapterFactoryInterface;
use PhpCollective\Infrastructure\Storage\StorageService;
use PhpCollective\Test\TestCase\TestCase;

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
        $this->assertInstanceOf(Local::class, $adapter);

        $result = $service->adapterFactory();
        $this->assertInstanceOf(StorageAdapterFactoryInterface::class, $result);

        $this->assertFalse($service->fileExists('local', 'doesnot'));

        $result = $service->storeFile(
            'local',
            '/horse/photo.jpg',
            $this->getFixtureFile('titus.jpg'),
        );
        $this->assertIsArray($result);

        $result = $service->storeResource(
            'local',
            '/horse/photo.jpg',
            fopen($this->getFixtureFile('titus.jpg'), 'rb'),
        );
        $this->assertIsArray($result);

        $result = $service->removeFile('local', '/horse/photo.jpg');
        $this->assertTrue($result);
    }
}
