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

use IteratorAggregate;
use League\Flysystem\FilesystemAdapter;

/**
 * Factory Collection Interface
 */
interface AdapterCollectionInterface extends IteratorAggregate
{
    /**
     * @param string $name Name
     * @param \League\Flysystem\FilesystemAdapter $adapter Adapter
     *
     * @return void
     */
    public function add($name, FilesystemAdapter $adapter);

    /**
     * @param string $name Name
     *
     * @return void
     */
    public function remove(string $name): void;

    /**
     * @param string $name Name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * @param string $name
     *
     * @return \League\Flysystem\FilesystemAdapter
     */
    public function get(string $name): FilesystemAdapter;

    /**
     * Empties the collection
     *
     * @return void
     */
    public function empty(): void;
}
