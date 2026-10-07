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

 * **directorySeparator**: `DIRECTORY_SEPARATOR`
 * **randomPath**: 'sha1'
   * Can also be a callable with the signature `function (string $uuid, int $levels): string`
 * **randomPathLevels**: 3
 * **sanitizeFilename**: true
 * **beautifyFilename**: false
 * **filenameSanitizer**: null|\PhpCollective\Infrastructure\Storage\Utility\FilenameSanitizerInterface
 * **lowercaseExtension**: false
   * Lowercases `{extension}` in `pathTemplate` and `variantPathTemplate`. See [The extension in paths](#the-extension-in-paths).
 * **pathTemplate**: '{model}{ds}{randomPath}{ds}{id}'
 * **variantPathTemplate**: '{filename}.{variant}.{extension}'
 * **hashPathTemplate**: 'blobs{ds}{hashPath}{ds}{hash}.{extension}'
   * Used instead of `pathTemplate` for a file that carries a content hash. Must contain `{hash}`.
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
 * **{hash}**: The content hash of the file. Empty if the file has none. Must be a hexadecimal digest; anything else throws.
 * **{hashPath}**: Directory levels cut from the content hash, two characters per level, `randomPathLevels` deep. Empty if the file has none.

The following placeholders are only valid when used in a path for a manipulated file.

 * **{variant}**: The name of the variant
 * **{hashedVariant}**: A hashed and to six chars truncated version of the manipulation name.

### The extension in paths

The extension comes from the name the file was uploaded under. Two things happen to it on the way into a path.

Characters other than letters, digits and hyphens are always removed. `report.pd f!` ends in `.pdf`, and an extension made up of other characters only is dropped.

Case depends on the template:

- `hashPathTemplate` always uses the lower case extension, so two files with the same content hash share one path whichever spelling they were uploaded with.
- `pathTemplate` and `variantPathTemplate` keep the case as uploaded. Set `lowercaseExtension` to `true` to lowercase there as well:

```php
$builder = new PathBuilder([
    'lowercaseExtension' => true,
]);
```

`File::extension()` still returns the extension as uploaded. Only the path changes.

Switching `lowercaseExtension` on affects files stored before: their stored paths keep working, but a path that is built again, a regenerated variant of `photo.JPG` for example, is then written to a `.jpg` path next to the old `.JPG` one.

Changed in 1.1: characters other than letters, digits and hyphens used to go into the path as they were. A file stored earlier under such an extension keeps its stored path; a path built again for it no longer contains those characters.

### Content addressed paths

A file that carries a content hash (`$file->withHash($hash)`) is stored under `hashPathTemplate` instead of `pathTemplate`. The default template contains neither the model nor the UUID, so two files with the same hash and extension resolve to the same path:

```php
$file = $file->withHash(hash_file('sha256', $pathToFile));

$builder->path($file);
// blobs/ed/70/02/ed7002b439e9ac845f22357d822bac1444730fbdb6016d3ec9432297b9ec9f73.jpg
```

Variant paths are not affected. They are still built from `variantPathTemplate`.

The library only builds the path. Deciding when a shared file may be removed is up to the application, because `FileStorage::remove()` deletes the file at that path.

### Filename sanitization

This path builder provides a `setFilenameSanitizer()` method that takes an object implementing `PhpCollective\Infrastructure\Storage\Utility\FilenameSanitizerInterface`.

This is an alternative way to provide a sanitizer besides passing it through the configuration array.

The `filenameSanitizer` config option and `setFilenameSanitizer()` are equivalent. Use whichever fits your setup better.

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
