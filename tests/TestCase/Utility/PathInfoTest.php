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

namespace PhpCollective\Test\TestCase\Utility;

use PhpCollective\Infrastructure\Storage\Utility\PathInfo;
use PhpCollective\Test\TestCase\TestCase;

/**
 * PathInfoTest
 */
class PathInfoTest extends TestCase
{
    /**
     * @return void
     */
    public function testPathInfo(): void
    {
        $result = PathInfo::for('/some/nice/file.jpg');

        $this->assertEquals('jpg', $result->extension());
        $this->assertEquals('file', $result->filename());
        $this->assertEquals('/some/nice', $result->dirname());
        $this->assertEquals('file.jpg', $result->basename());
        $this->assertTrue($result->hasExtension());
    }
}
