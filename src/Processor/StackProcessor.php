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
 * The stack processor takes a list of other processors and processes them in
 * the order they were added to the stack.
 */
class StackProcessor implements ProcessorInterface
{
    protected array $processors = [];

    /**
     * @param array<\PhpCollective\Infrastructure\Storage\Processor\ProcessorInterface> $processors
     */
    public function __construct(array $processors)
    {
        foreach ($processors as $processor) {
            $this->add($processor);
        }
    }

    /**
     * @param \PhpCollective\Infrastructure\Storage\Processor\ProcessorInterface $processor
     *
     * @return void
     */
    public function add(ProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }

    /**
     * @inheritDoc
     */
    public function process(FileInterface $file): FileInterface
    {
        foreach ($this->processors as $processor) {
            $file = $processor->process($file);
        }

        return $file;
    }
}
