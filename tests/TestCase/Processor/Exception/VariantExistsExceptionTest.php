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

namespace PhpCollective\Test\TestCase\Processor\Exception;

use PhpCollective\Infrastructure\Storage\Processor\Exception\VariantExistsException;
use PhpCollective\Test\TestCase\TestCase;

/**
 * ManipulationExistsExceptionTest
 */
class VariantExistsExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testException(): void
    {
        $exception = VariantExistsException::withName('test');
        $expected = 'A variant with the name `test` already exists';
        $this->assertEquals($expected, $exception->getMessage());
    }
}
