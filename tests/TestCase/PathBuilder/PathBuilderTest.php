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

namespace PhpCollective\Test\TestCase\PathBuilder;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use PhpCollective\Infrastructure\Storage\ContentHashInterface;
use PhpCollective\Infrastructure\Storage\File;
use PhpCollective\Infrastructure\Storage\FileFactory;
use PhpCollective\Infrastructure\Storage\FileInterface;
use PhpCollective\Infrastructure\Storage\PathBuilder\PathBuilder;
use PhpCollective\Infrastructure\Storage\Processor\Image\ImageVariantCollection;
use PhpCollective\Infrastructure\Storage\Utility\NoopFilenameSanitizer;
use PhpCollective\Test\TestCase\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * PathBuilderTest
 */
class PathBuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testDatePaths(): void
    {
        $builder = new class ([
            'pathTemplate' => '{year}{ds}{month}{ds}{day}{ds}{hour}{ds}{minute}',
        ]) extends PathBuilder {
            protected function getDateObject(): DateTimeInterface
            {
                return new DateTime('2020-01-01T20:00:00');
            }
        };

        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');

        $result = $builder->path($file);

        $this->assertEquals($this->sanitizeSeparator('2020/01/01/20/00'), $result);
    }

    /**
     * @return void
     */
    public function testPathWithEmptyPlaceHolders(): void
    {
        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');

        $builder = new PathBuilder();
        $result = $builder->path($file);

        $this->assertEquals($this->sanitizeSeparator('/fe/c3/b4/914e151291534253a81e7ee2edc1d973/titus.jpg'), $result);
    }

    /**
     * @return void
     */
    public function testConfiguredFilenameSanitizerIsUsed(): void
    {
        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->withFilename('foo bar.jpg');

        $builder = new PathBuilder([
            'filenameSanitizer' => new NoopFilenameSanitizer(),
        ]);

        $result = $builder->path($file);

        $this->assertStringContainsString(
            $this->sanitizeSeparator('/foo bar.jpg'),
            $result,
        );
    }

    /**
     * @return void
     */
    public function testConfiguredRandomPathAndDirectorySeparatorAreUsed(): void
    {
        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');

        $builder = new PathBuilder([
            'directorySeparator' => '-',
            'randomPath' => static fn (string $uuid, int $level): string => 'custom-path',
        ]);

        $result = $builder->path($file);

        $this->assertEquals(
            '-custom-path-914e151291534253a81e7ee2edc1d973-titus.jpg',
            $result,
        );
    }

    /**
     * @return void
     */
    public function testSha1RandomPathUsesConfiguredDirectorySeparator(): void
    {
        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');

        $builder = new PathBuilder([
            'directorySeparator' => '-',
        ]);

        $result = $builder->path($file);

        $this->assertSame(
            '-fe-c3-b4-914e151291534253a81e7ee2edc1d973-titus.jpg',
            $result,
        );
    }

    /**
     * @return void
     */
    public function testBuilder(): void
    {
        $collection = ImageVariantCollection::create();
        $collection
            ->addNew('resizeAndFlip')
            ->flipHorizontal()
            ->resize(300, 300)
            ->optimize();

        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->addToCollection('avatar')
            ->belongsToModel('User', '1')
            ->withVariants($collection->toArray());

        $builder = new PathBuilder();

        $result = $builder->path($file);
        $this->assertEquals(
            $this->sanitizeSeparator('User\fe\c3\b4\914e151291534253a81e7ee2edc1d973\titus.jpg'),
            $result,
        );

        $result = $builder->pathForVariant($file, 'resizeAndFlip');
        $this->assertEquals(
            $this->sanitizeSeparator('User\fe\c3\b4\914e151291534253a81e7ee2edc1d973\titus.7ae239.jpg'),
            $result,
        );
    }

    /**
     * @return void
     */
    public function testHashedFileUsesHashPathTemplate(): void
    {
        $hash = hash('sha256', 'content');
        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1')
            ->withHash($hash);

        $builder = new PathBuilder(['directorySeparator' => '/']);

        $this->assertSame('blobs/ed/70/02/' . $hash . '.jpg', $builder->path($file));
    }

    /**
     * @return void
     */
    public function testVariantOfHashedFileKeepsUuidBasedPath(): void
    {
        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1')
            ->withHash(hash('sha256', 'content'));

        $builder = new PathBuilder(['directorySeparator' => '/']);

        $this->assertSame(
            'User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/titus.7ae239.jpg',
            $builder->pathForVariant($file, 'resizeAndFlip'),
        );
    }

    /**
     * @return void
     */
    public function testCustomHashPathTemplate(): void
    {
        $hash = hash('sha256', 'content');
        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->withHash($hash);

        $builder = new PathBuilder([
            'directorySeparator' => '/',
            'randomPathLevels' => 1,
            'hashPathTemplate' => 'shared{ds}{hashPath}{ds}{hash}',
        ]);

        $this->assertSame('shared/ed/' . $hash, $builder->path($file));
    }

    /**
     * @return void
     */
    public function testHashPlaceholdersAreEmptyWithoutHash(): void
    {
        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');

        $builder = new PathBuilder([
            'directorySeparator' => '/',
            'pathTemplate' => 'files{ds}{hashPath}{ds}{hash}{ds}{filename}.{extension}',
        ]);

        $this->assertSame('files/titus.jpg', $builder->path($file));
    }

    /**
     * @return void
     */
    public function testFileWithoutContentHashInterfaceIsUnhashed(): void
    {
        $file = $this->createConfiguredStub(FileInterface::class, [
            'uuid' => '914e1512-9153-4253-a81e-7ee2edc1d973',
            'filename' => 'titus.jpg',
            'extension' => 'jpg',
            'model' => 'User',
        ]);

        $builder = new PathBuilder(['directorySeparator' => '/']);

        $this->assertSame('User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/titus.jpg', $builder->path($file));
    }

    /**
     * @return void
     */
    public function testHashPathTemplateWithoutHashPlaceholderThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must contain the `{hash}` placeholder');

        new PathBuilder(['hashPathTemplate' => 'blobs{ds}{filename}']);
    }

    /**
     * @return void
     */
    public function testSetHashPathTemplateWithoutHashPlaceholderThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new PathBuilder())->setHashPathTemplate('blobs{ds}{filename}');
    }

    /**
     * @return void
     */
    public function testHashPathTemplateOptionWithoutHashPlaceholderThrows(): void
    {
        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->withHash(hash('sha256', 'content'));

        $this->expectException(InvalidArgumentException::class);

        (new PathBuilder())->path($file, ['hashPathTemplate' => 'blobs{ds}{extension}']);
    }

    /**
     * @param string $hash
     *
     * @return \PhpCollective\Infrastructure\Storage\FileInterface&\PhpCollective\Infrastructure\Storage\ContentHashInterface
     */
    protected function foreignHashedFile(string $hash): FileInterface
    {
        $file = $this->createStubForIntersectionOfInterfaces([FileInterface::class, ContentHashInterface::class]);
        $file->method('uuid')->willReturn('914e1512-9153-4253-a81e-7ee2edc1d973');
        $file->method('filename')->willReturn('titus.jpg');
        $file->method('extension')->willReturn('jpg');
        $file->method('hash')->willReturn($hash);

        return $file;
    }

    /**
     * A foreign file object does not go through File::withHash().
     *
     * @return void
     */
    public function testNonHexadecimalHashOfForeignFileIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a hexadecimal digest');

        (new PathBuilder())->path($this->foreignHashedFile('../outside'));
    }

    /**
     * @return void
     */
    public function testHashOfForeignFileIsLowercasedInPath(): void
    {
        $builder = new PathBuilder(['directorySeparator' => '/']);

        $this->assertSame('blobs/ab/cd/ef/abcdef12.jpg', $builder->path($this->foreignHashedFile('ABCDEF12')));
    }

    /**
     * @return void
     */
    public function testFalsyVariantNameOfHashedFileUsesVariantTemplate(): void
    {
        $hash = hash('sha256', 'content');
        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1')
            ->withHash($hash);

        $builder = new PathBuilder(['directorySeparator' => '/']);

        $this->assertStringNotContainsString($hash, $builder->pathForVariant($file, '0'));
        $this->assertStringStartsWith('User/', $builder->pathForVariant($file, '0'));
    }

    /**
     * @return array<string, array{string, bool, string}>
     */
    public static function extensionProvider(): array
    {
        return [
            'case kept by default' => ['photo.JPG', false, 'User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/photo.JPG'],
            'lowercased on request' => ['photo.JPG', true, 'User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/photo.jpg'],
            'other characters always removed' => ['photo.J p+G!', false, 'User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/photo.JpG'],
            'other characters removed and lowercased' => ['photo.J p+G!', true, 'User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/photo.jpg'],
            'hyphen kept' => ['archive.tar-GZ', false, 'User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/archive.tar-GZ'],
            'underscore kept' => ['dump.sql_bak', false, 'User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/dump.sql_bak'],
            'nothing left' => ['photo.???', false, 'User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/photo'],
        ];
    }

    /**
     * @param string $filename
     * @param bool $lowercase
     * @param string $expected
     *
     * @return void
     */
    #[DataProvider('extensionProvider')]
    public function testExtensionInPath(string $filename, bool $lowercase, string $expected): void
    {
        $file = File::create($filename, 1, 'image/jpeg', 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1');

        $builder = new PathBuilder(['directorySeparator' => '/', 'lowercaseExtension' => $lowercase]);

        $this->assertSame($expected, $builder->path($file));
    }

    /**
     * @return void
     */
    public function testHashedFilesDifferingOnlyInExtensionCaseShareOnePath(): void
    {
        $hash = hash('sha256', 'content');
        $builder = new PathBuilder(['directorySeparator' => '/']);
        $upper = File::create('a.JPG', 1, 'image/jpeg', 'local')->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')->withHash($hash);
        $lower = File::create('b.jpg', 1, 'image/jpeg', 'local')->withUuid('014e1512-9153-4253-a81e-7ee2edc1d973')->withHash($hash);

        $this->assertSame($builder->path($lower), $builder->path($upper));
    }

    /**
     * @return void
     */
    public function testVariantPathKeepsExtensionCaseByDefault(): void
    {
        $file = File::create('photo.JPG', 1, 'image/jpeg', 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1')
            ->withHash(hash('sha256', 'content'));

        $builder = new PathBuilder(['directorySeparator' => '/']);

        $this->assertStringEndsWith('.JPG', $builder->pathForVariant($file, 'thumb'));
        $this->assertStringEndsWith('.jpg', $builder->path($file));
    }
}
