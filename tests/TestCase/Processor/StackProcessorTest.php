<?php

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

declare(strict_types=1);

namespace PhpCollective\Test\TestCase\Processor;

use PhpCollective\Infrastructure\Storage\FileFactory;
use PhpCollective\Infrastructure\Storage\Processor\ProcessorInterface;
use PhpCollective\Infrastructure\Storage\Processor\StackProcessor;
use PhpCollective\Test\TestCase\TestCase;

/**
 * StackProcessorTest
 */
class StackProcessorTest extends TestCase
{
    /**
     * @return void
     */
    public function testVariant(): void
    {
        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'Local');

        /** @var \PhpCollective\Infrastructure\Storage\Processor\ProcessorInterface|\PHPUnit\Framework\MockObject\MockObject $processor1 */
        $processor1 = $this->getMockBuilder(ProcessorInterface::class)
            ->getMock();

        $processor1->expects($this->once())
            ->method('process')
            ->with($file)
            ->willReturn($file);

        /** @var \PhpCollective\Infrastructure\Storage\Processor\ProcessorInterface|\PHPUnit\Framework\MockObject\MockObject $processor2 */
        $processor2 = $this->getMockBuilder(ProcessorInterface::class)
            ->getMock();

        $processor2->expects($this->once())
            ->method('process')
            ->with($file)
            ->willReturn($file);

        $processor = new StackProcessor([
            $processor1,
            $processor2,
        ]);

        $file2 = $processor->process($file);

        $this->assertEquals($file, $file2);
    }
}
