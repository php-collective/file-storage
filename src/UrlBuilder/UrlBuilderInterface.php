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

namespace PhpCollective\Infrastructure\Storage\UrlBuilder;

use PhpCollective\Infrastructure\Storage\FileInterface;

/**
 * UrlBuilderInterface
 */
interface UrlBuilderInterface
{
   /**
    * @param \PhpCollective\Infrastructure\Storage\FileInterface $file File
    *
    * @return string
    */
    public function url(FileInterface $file): string;

   /**
    * @param \PhpCollective\Infrastructure\Storage\FileInterface $file File
    * @param string $variant Version
    *
    * @return string
    */
    public function urlForVariant(FileInterface $file, string $variant): string;
}
