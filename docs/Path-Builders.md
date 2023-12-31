# Path Builders

Path builders are used to generate the path the file gets stored under in the storage system.

## Default Path Builder

The library comes with a pretty powerful path builder that allows you to build the path you want from a lot of variables that can be put together in a string template for the files path and variants of the file.

```
$builder = new PathBuilder([
    // Configure your options
]);
```

### Options:

 * **randomPath**: 'sha1'
 * **sanitizeFilename**: true
 * **beautifyFilename**: false
 * **filenameSanitizer**: null|\PhpCollective\Infrastructure\Storage\Utility\FilenameSanitizerInterface
 * **pathTemplate**: '{model}{ds}{randomPath}{ds}{id}'
 * **variantPathTemplate**: '{filename}.{variant}.{extension}'
 * **dateFormat**: Array of [DateTimeInterface::format()](https://www.php.net/manual/en/datetime.format.php) compatible values
   * **year**: 'Y'
   * **month**: 'm'
   * **day**: 'd'
   * **hour**: 'H'
   * **minute**: 'i'
   * **custom**: 'Y-m-d'

### Path Template Placeholders

The placeholders allow you to build your custom path pretty easy.

For example this template string
`{model}{ds}a-fancy-folder-name{ds}{file}.{extension}` will create this path for a file with the model called `user`:
`/user/a-fancy-folder-name/filename.ext`.

 * **{ds}**: Is the directory separator of the system, or the one you configured.
 * **{filename}**: Is the filename.
 * **{hashedFilename}**: A sha1() hashed filename string.
 * **{extension}**: The extension of the filename without the dot.
 * **{id}**: The UUID of the file.
 * **{strippedId}**: The UUID of the file without dashes.
 * **{randomPath}**: A semi-random path to increase the depth and variability of the path. This will avoid running into limitations of some file systems.
 * **{mimeType}**: The mime type of the file. Be aware it *might* include invalid chars for a storage backend!.
 * **{model}**: The model name.
 * **{modelId}**: The id of the models entity.
 * **{collection}**: The collection the file belongs into.
 * **{year}**: Four digits year
 * **{month}**: Two digits month value
 * **{day}**: Two digits day value
 * **{hour}**: Two digits hour value
 * **{minute}**: Two digits minute value
 * **{date}**: Custom date format i.e. '2020-01-03'

The following placeholders are only valid when used in a path for a manipulated file.

 * **{variant}**: The name of the variant
 * **{hashedVariant}**: A hashed and to six chars truncated version of the manipulation name.

### Filename sanitization

This path builder provides a `setFilenameSanitizer()` method that takes an object implementing `PhpCollective\Infrastructure\Storage\Utility\FilenameSanitizerInterface`.

This is an alternative way to provide a sanitizer besides passing it through the configuration array.

## Conditional Path Builder

Add callbacks and path builders to check on the file which of the builders should be used to build the path.

This allows you to use different instances with a different configuration or implementation of path builders for files of different types or in different collections or models.

The example below will use the "other" path builder instance if the model attached to the file called "user". If not it will fall back to the default builder.

```php
use PhpCollective\Infrastructure\Storage\PathBuilder\PathBuilder;
use PhpCollective\Infrastructure\Storage\PathBuilder\ConditionalPathBuilder;
use PhpCollective\Infrastructure\Storage\FileInterface;

$default = new PathBuilder();
$other = new PathBuilder([
    // Configure it differently
]);

$builder = new ConditionalPathBuilder($default);
$builder->addPathBuilder($other, function(FileInterface $file, ?string $manipulation = null) {
    return ($file->model() === 'User' && $file->collection() === 'Avatar');
});
```

## Implementing your own

To implement your own path builder implement the [PathBuilderInterface](../src/PathBuilder/PathBuilderInterface.php).

```php
use PhpCollective\Infrastructure\Storage\FileInterface;
use PhpCollective\Infrastructure\Storage\PathBuilder\PathBuilderInterface;

class MyPathBuilder implements PathBuilderInterface
{
    public function path(FileInterface $file, array $options = []): string
    {
        // Your code...
    }

    public function pathForManipulation(FileInterface $file, string $name, array $options = []): string
    {
        // Your code...
    }

    public function pathForVariant(FileInterface $file, string $name, array $options = []): string
    {
        // Your code
    }
}
```
