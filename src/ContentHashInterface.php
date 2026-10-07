<?php declare(strict_types = 1);

namespace PhpCollective\Infrastructure\Storage;

/**
 * Implemented by file objects that can carry a hash of their content.
 *
 * Kept apart from FileInterface so existing implementations of that
 * interface keep working.
 *
 * @author Mark Scherer
 * @license https://opensource.org/licenses/MIT MIT License
 */
interface ContentHashInterface
{
    /**
     * Content hash of the file, if one was set.
     *
     * @return string|null
     */
    public function hash(): ?string;

    /**
     * @param string $hash Hexadecimal digest of the content
     *
     * @return static
     */
    public function withHash(string $hash): static;
}
