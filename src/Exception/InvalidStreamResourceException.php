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

namespace PhpCollective\Infrastructure\Storage\Exception;

/**
 * Invalid Stream Resource
 */
class InvalidStreamResourceException extends StorageException
{
    /**
     * @return self
     */
    public static function create(): self
    {
        return new self(
            'The provided value is not a valid stream resource',
        );
    }
}
