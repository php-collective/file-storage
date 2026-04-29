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

use PhpCollective\Infrastructure\Storage\FileFactory;
use PhpCollective\Infrastructure\Storage\FileInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * FileFactoryTest
 */
class FileFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testInvalidUpload(): void
    {
        $uploadedFile = $this->createStub(UploadedFileInterface::class);

        $uploadedFile->method('getError')
            ->willReturn(UPLOAD_ERR_NO_FILE);

        $this->expectException(RuntimeException::class);
        FileFactory::fromUploadedFile($uploadedFile, 'local');
    }

    /**
     * @return void
     */
    public function testValidUpload(): void
    {
        $stream = $this->createStub(StreamInterface::class);

        $stream->method('isReadable')
            ->willReturn(true);

        $stream->method('isWritable')
            ->willReturn(false);

        $stream->method('detach')
            ->willReturn(fopen('composer.json', 'r'));

        $uploadedFile = $this->createStub(UploadedFileInterface::class);

        $uploadedFile->method('getError')
            ->willReturn(UPLOAD_ERR_OK);

        $uploadedFile->method('getClientFilename')
            ->willReturn('titus.jpg');

        $uploadedFile->method('getSize')
            ->willReturn(12345);

        $uploadedFile->method('getClientMediaType')
            ->willReturn('image/image-jpg');

        $uploadedFile->method('getStream')
            ->willReturn($stream);

        $file = FileFactory::fromUploadedFile($uploadedFile, 'local');

        $this->assertInstanceOf(FileInterface::class, $file);
    }
}
