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

namespace PhpCollective\Infrastructure\Storage\Processor;

use PhpCollective\Infrastructure\Storage\FileInterface;

/**
 * Processor Interface
 */
interface ProcessorInterface
{
    /**
     * @param \PhpCollective\Infrastructure\Storage\FileInterface $file File
     *
     * @return \PhpCollective\Infrastructure\Storage\FileInterface
     */
    public function process(FileInterface $file): FileInterface;
}
