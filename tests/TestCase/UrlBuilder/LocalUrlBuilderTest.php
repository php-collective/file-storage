<?php declare(strict_types=1);

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

namespace PhpCollective\Test\TestCase\UrlBuilder;

use PhpCollective\Infrastructure\Storage\FileFactory;
use PhpCollective\Infrastructure\Storage\PathBuilder\PathBuilder;
use PhpCollective\Infrastructure\Storage\Processor\Image\ImageVariant;
use PhpCollective\Infrastructure\Storage\UrlBuilder\LocalUrlBuilder;
use PhpCollective\Test\TestCase\TestCase;

/**
 * Local Url BuilderTest
 */
class LocalUrlBuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testLocalUrlBuilder(): void
    {
        $pathBuilder = new PathBuilder();
        $urlBuilder = new LocalUrlBuilder('/', 'https');
        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->withFilename('foobar.jpg')
            ->addToCollection('avatar')
            ->belongsToModel('User', '1')
            ->buildPath($pathBuilder);

        $result = $urlBuilder->url($file);
        $this->assertEquals('/User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/foobar.jpg', $result);

        $file = $file->withVariants([
            'crop' => ImageVariant::create('crop')
                ->withUrl('User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/foobar.crop.jpg')
                ->crop(100, 100)
                ->toArray(),
        ]);

        $result = $urlBuilder->urlForVariant($file, 'crop');
        $this->assertEquals('/User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/foobar.crop.jpg', $result);
    }
}
