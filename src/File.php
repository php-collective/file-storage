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

use PhpCollective\Infrastructure\Storage\Exception\InvalidStreamResourceException;
use PhpCollective\Infrastructure\Storage\PathBuilder\PathBuilderInterface;
use PhpCollective\Infrastructure\Storage\Processor\Exception\VariantDoesNotExistException;
use PhpCollective\Infrastructure\Storage\UrlBuilder\UrlBuilderInterface;
use RuntimeException;

/**
 * File
 */
class File implements FileInterface
{
    /**
     * @var int
     */
    protected int $id;

    /**
     * @var string
     */
    protected string $uuid;

    /**
     * @var string
     */
    protected string $filename;

    /**
     * @var int
     */
    protected int $filesize;

    /**
     * @var string
     */
    protected string $mimeType = '';

    /**
     * @var string|null
     */
    protected ?string $extension = null;

    /**
     * @var string|null
     */
    protected ?string $path = null;

    /**
     * @var string|null
     */
    protected ?string $collection = null;

    /**
     * @var string
     */
    protected string $storage = 'local';

    /**
     * @var array
     */
    protected array $metadata = [];

    /**
     * @var string|null
     */
    protected ?string $model = null;

    /**
     * @var string|null
     */
    protected ?string $modelId = null;

    /**
     * Source file to be stored in our system
     *
     * @var mixed
     */
    protected $sourceFile;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var string
     */
    protected string $url = '';

    /**
     * @var array
     */
    protected array $variants = [];

    /**
     * Creates a new instance
     *
     * @param string $filename Filename
     * @param int $filesize Filesize
     * @param string $mimeType Mime Type
     * @param string $storage Storage config name
     * @param string|null $collection Collection name
     * @param string|null $model Model name
     * @param string|null $modelId Model id
     * @param array $metadata Meta data
     * @param array $variants Variants
     * @param resource|null $resource
     *
     * @return self
     */
    public static function create(
        string $filename,
        int $filesize,
        string $mimeType,
        string $storage,
        ?string $collection = null,
        ?string $model = null,
        ?string $modelId = null,
        array $metadata = [],
        array $variants = [],
        $resource = null
    ): FileInterface {
        $that = new self();

        $that->filename = $filename;
        $that->filesize = $filesize;
        $that->mimeType = $mimeType;
        $that->storage = $storage;
        $that->model = $model;
        $that->modelId = $modelId;
        $that->collection = $collection;
        $that->variants = $variants;
        $that->metadata = $metadata;

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $that->extension = !$extension ? null : (string)$extension;

        if ($resource !== null) {
            $that = $that->withResource($resource);
        }

        return $that;
    }

    /**
     * Storage name
     *
     * @return string
     */
    public function storage(): string
    {
        return $this->storage;
    }

    /**
     * UUID of the file
     *
     * @param string $uuid UUID string
     *
     * @return self
     */
    public function withUuid(string $uuid): FileInterface
    {
        $that = clone $this;
        $that->uuid = $uuid;

        return $that;
    }

    /**
     * Stream resource that should be stored
     *
     * @return resource|null
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * Same as withResource() but takes a file path
     *
     * @param string $file File
     *
     * @throws \RuntimeException
     *
     * @return self
     */
    public function withFile(string $file): FileInterface
    {
        $resource = fopen($file, 'rb');
        if ($resource === false) {
            throw new RuntimeException('Cannot open file `' . $file . '`');
        }

        return $this->withResource($resource);
    }

    /**
     * @param mixed $resource
     *
     * @throws \PhpCollective\Infrastructure\Storage\Exception\InvalidStreamResourceException
     *
     * @return void
     */
    protected function assertStreamResource($resource): void
    {
        if (
            !is_resource($resource)
            || get_resource_type($resource) !== 'stream'
        ) {
            throw InvalidStreamResourceException::create();
        }
    }

    /**
     * Stream resource of the file to be stored
     *
     * @param resource $resource Stream Resource
     *
     * @return self
     */
    public function withResource($resource): FileInterface
    {
        $this->assertStreamResource($resource);

        $that = clone $this;
        $that->resource = $resource;

        return $that;
    }

    /**
     * Assign a model and model id to a file
     *
     * @param string $model Model
     * @param string|int $modelId Model ID, UUID string or integer
     *
     * @return $this
     */
    public function belongsToModel(string $model, $modelId)
    {
        $this->model = $model;
        $this->modelId = (string)$modelId;

        return $this;
    }

    /**
     * Adds the file to a collection
     *
     * @param string $collection Collection
     *
     * @return $this
     */
    public function addToCollection(string $collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Sets the path, immutable
     *
     * @param string $path Path to the file
     *
     * @return self
     */
    public function withPath(string $path): FileInterface
    {
        $that = clone $this;
        $that->path = $path;

        return $that;
    }

    /**
     * Filename
     *
     * @param string $filename Filename
     *
     * @return self
     */
    public function withFilename(string $filename): self
    {
        $that = clone $this;
        $that->filename = $filename;

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $that->extension = !$extension ? null : (string)$extension;

        return $that;
    }

    /**
     * The collections name this file belongs into
     *
     * @return string|null
     */
    public function collection(): ?string
    {
        return $this->collection;
    }

    /**
     * Model name
     *
     * @return string|null
     */
    public function model(): ?string
    {
        return $this->model;
    }

    /**
     * Model ID
     *
     * @return string|null
     */
    public function modelId(): ?string
    {
        return $this->modelId;
    }

    /**
     * Size of the file in bytes
     *
     * @return int
     */
    public function filesize(): int
    {
        return $this->filesize;
    }

    /**
     * Returns a human readable file size
     *
     * @return string
     */
    public function readableSize(): string
    {
        $i = floor(log($this->filesize, 1024));
        $round = (string)round($this->filesize / (1024 ** $i), [0, 0, 2, 2, 3][$i]);

        return $round . ['B', 'kB', 'MB', 'GB', 'TB'][$i];
    }

    /**
     * @return string|null
     */
    public function extension(): ?string
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function mimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function uuid(): string
    {
        return $this->uuid;
    }

    /**
     * @throws \RuntimeException
     *
     * @return string
     */
    public function path(): string
    {
        if ($this->path === null) {
            throw new RuntimeException(
                'Path has not been set',
            );
        }

        return $this->path;
    }

    /**
     * Builds the path for this file
     *
     * @param \PhpCollective\Infrastructure\Storage\PathBuilder\PathBuilderInterface $pathBuilder Path Builder
     *
     * @return self
     */
    public function buildPath(PathBuilderInterface $pathBuilder): FileInterface
    {
        $that = clone $this;
        $that->path = $pathBuilder->path($this);

        return $that;
    }

    /**
     * @param array $metadata Meta data
     * @param bool $overwrite Overwrite whole metadata instead of assoc merging.
     *
     * @return self
     */
    public function withMetadata(array $metadata, bool $overwrite = false): FileInterface
    {
        $that = clone $this;
        if ($overwrite) {
            $that->metadata = $metadata;
        } else {
            $that->metadata = $metadata + $that->metadata;
        }

        return $that;
    }

    /**
     * @inheritDoc
     */
    public function withMetadataByKey(string $name, $data): FileInterface
    {
        $that = clone $this;
        $that->metadata[$name] = $data;

        return $that;
    }

    /**
     * @inheritDoc
     */
    public function withoutMetadataByKey(string $name): FileInterface
    {
        $that = clone $this;
        unset($that->metadata[$name]);

        return $that;
    }

    /**
     * @inheritDoc
     */
    public function withoutMetadata(): self
    {
        $that = clone $this;
        $that->metadata = [];

        return $that;
    }

    /**
     * @inheritDoc
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return bool
     */
    public function hasVariants(): bool
    {
        return (bool)$this->variants;
    }

    /**
     * @param string $name Name
     *
     * @return bool
     */
    public function hasVariant(string $name): bool
    {
        return isset($this->variants[$name]);
    }

    /**
     * @return array
     */
    public function variants(): array
    {
        return $this->variants;
    }

    /**
     * Returns a variant by name
     *
     * @param string $name Name
     *
     * @throws \PhpCollective\Infrastructure\Storage\Processor\Exception\VariantDoesNotExistException
     *
     * @return array
     */
    public function variant(string $name): array
    {
        if (!isset($this->variants[$name])) {
            throw VariantDoesNotExistException::withName($name);
        }

        return $this->variants[$name];
    }

    /**
     * Adds a variant
     *
     * @param string $name Name
     * @param array $data Data
     *
     * @return self
     */
    public function withVariant(string $name, array $data): FileInterface
    {
        $that = clone $this;
        $that->variants[$name] = $data;

        return $that;
    }

    /**
     * Gets the paths for all variants
     *
     * @return array
     */
    public function variantPaths(): array
    {
        $paths = [];
        foreach ($this->variants as $variant => $data) {
            if (isset($data['path'])) {
                $paths[$variant] = $data['path'];
            }
        }

        return $paths;
    }

    /**
     * @param string $key
     * @param mixed $data
     *
     * @return self
     */
    public function withMetadataKey(string $key, $data): FileInterface
    {
        $that = clone $this;
        $that->metadata[$key] = $data;

        return $that;
    }

    /**
     * @inheritDoc
     */
    public function withoutMetadataKey(string $key): FileInterface
    {
        $that = clone $this;
        unset($that->metadata[$key]);

        return $that;
    }

    /**
     * Sets many variants at once
     *
     * @param array $variants Variants
     * @param bool $merge Merge Variants, default is true
     *
     * @return self
     */
    public function withVariants(array $variants, bool $merge = true): FileInterface
    {
        $that = clone $this;
        $that->variants = $this->arrayMergeDeep(
            $merge ? $that->variants : [],
            $variants,
        );

        return $that;
    }

    /**
     * Sets many variants at once
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function arrayMergeDeep(array $array1, array $array2): array {
        foreach ($array2 as $key => $value) {
            if (
                isset($array1[$key]) &&
                is_array($array1[$key]) &&
                is_array($value)
            ) {
                $array1[$key] = $this->arrayMergeDeep($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }

        return $array1;
    }

    /**
     * @inheritDoc
     */
    public function buildUrl(UrlBuilderInterface $urlBuilder)
    {
        $this->url = $urlBuilder->url($this);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function withUrl(string $url): FileInterface
    {
        $that = clone $this;
        $that->url = $url;

        return $that;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'filename' => $this->filename,
            'filesize' => $this->filesize,
            'mimeType' => $this->mimeType,
            'extension' => $this->extension,
            'path' => $this->path,
            'model' => $this->model,
            'modelId' => $this->modelId,
            'collection' => $this->collection,
            'readableSize' => $this->readableSize(),
            'variants' => $this->variants,
            'metadata' => $this->metadata,
            'url' => $this->url,
        ];
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
