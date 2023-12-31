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

namespace PhpCollective\Infrastructure\Storage;

use PhpCollective\Infrastructure\Storage\Exception\FileDoesNotExistException;
use PhpCollective\Infrastructure\Storage\Exception\FileNotReadableException;
use PhpCollective\Infrastructure\Storage\Utility\MimeType;
use PhpCollective\Infrastructure\Storage\Utility\PathInfo;
use PhpCollective\Infrastructure\Storage\Utility\StreamWrapper;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * File Factory
 */
class FileFactory implements FileFactoryInterface
{
    /**
     * @inheritDoc
     */
    public static function fromUploadedFile(
        UploadedFileInterface $uploadedFile,
        string $storage
    ): FileInterface {
        static::checkUploadedFile($uploadedFile);

        $file = File::create(
            (string)$uploadedFile->getClientFilename(),
            (int)$uploadedFile->getSize(),
            (string)$uploadedFile->getClientMediaType(),
            $storage,
        );

        return $file->withResource(
            StreamWrapper::getResource($uploadedFile->getStream()),
        );
    }

    /**
     * @inheritDoc
     *
     * @throws \RuntimeException
     */
    public static function fromDisk(string $path, string $storage): FileInterface
    {
        static::checkFile($path);

        $info = PathInfo::for($path);
        $filesize = filesize($path) ?: 0;
        $extension = (string)$info->extension();
        $mimeType = MimeType::byExtension($extension);

        $file = File::create(
            $info->basename(),
            $filesize,
            $mimeType,
            $storage,
        );

        $resource = fopen($path, 'rb');
        if ($resource === false) {
            throw new RuntimeException('Cannot open file `' . $path . '`');
        }

        return $file->withResource($resource);
    }

    /**
     * Checks if the uploaded file is a valid upload
     *
     * @param \Psr\Http\Message\UploadedFileInterface $uploadedFile Uploaded File
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    protected static function checkUploadedFile(UploadedFileInterface $uploadedFile): void
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new RuntimeException(sprintf(
                'Can\'t create storage object from upload with error code: %d',
                $uploadedFile->getError(),
            ));
        }
    }

    /**
     * @param string $path Path
     *
     * @throws \PhpCollective\Infrastructure\Storage\Exception\FileDoesNotExistException
     * @throws \PhpCollective\Infrastructure\Storage\Exception\FileNotReadableException
     *
     * @return void
     */
    protected static function checkFile(string $path): void
    {
        if (!file_exists($path)) {
            throw FileDoesNotExistException::filename($path);
        }

        if (!is_readable($path)) {
            throw FileNotReadableException::filename($path);
        }
    }
}
