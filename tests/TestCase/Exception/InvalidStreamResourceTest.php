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

namespace PhpCollective\Test\TestCase\Exception;

use PhpCollective\Infrastructure\Storage\Exception\InvalidStreamResourceException;
use PhpCollective\Test\TestCase\TestCase;

/**
 * InvalidStreamResourceTest
 */
class InvalidStreamResourceTest extends TestCase
{
    /**
     * @return void
     */
    public function testException(): void
    {
        $exception = InvalidStreamResourceException::create();
        $this->assertEquals(
            'The provided value is not a valid stream resource',
            $exception->getMessage(),
        );
    }
}
