# Architecture

## Background

The whole library is build with clear Separation of Concerns (SoC) in mind: A file is always an entry in a database from the app perspective. The database (table) is the reference to the real place of where the file is stored and keeps some information like mime type, filename and size as well. Storing the path to a file inside an arbitrary table along other data is considered as bad practice because it doesn't respect SoC from an architecture perspective, but many people do it this way for some reason.

## The file object

The *File* object is the central object in this library around which all functionality has being built. The file object contains all information needed to store and retrieve the file later from a storage backend. It also has methods to add variants to the file that are checked and applied by a *Processor*. This can be image or video processing for example in the most common cases.

The file object is serializable to json, and you can call `toArray()` on it to turn it into an array that you can either save in the structure you get or continue transforming it into whatever structure your persistence layer expects.

You'll have to reconstruct the file object later from your persisted information when you want to come back to it later and work with new variants for example. Depending on your architecture, your domain model could also simply implement the `FileInterface` if this is more convenient for your.

## Workflow

 * Create file object.
   * Add a resource handle of a file to it (if it is a new file).
 * Store file using the file storage service.
 * Use a processor to apply variants to the file.
 * Read file and manipulated files from the backend using the FileStorageService.

The processors can be instantiated and used alone without further dependencies on the service. This enables you to use them very easy in your applications shell for example.

## Caveats

The file object represents the state and information about a file. However, there are a few logical problems, basically the chicken-egg problem: Which of both came first?

In an application this can be the same for a file: Has the file an associated model id or itself an id in the case of auto incrementing integer type ids? So you need the id before you can associate the file it or when you want to set the id for the file object itself, you'll need to get it first. For that reason we favour UUIDs over integer type IDs for the files.

---

The same problem applies to the actual logic of transferring the file from the application into the storage system.

The file object implements `withResource()` and `withFile()`. The second takes a file path and turns it into a resource and calls withResource() internally.

When you store a file for the first time in your storage backend, you **must** add a resource to the file object. If you don't do that, you'll get an exception from the file storage service.

## Dependencies

We think there is a need to explain why we have picked the dependencies we have, because we try to keep dependencies low. However, there are some cases that make sense to use existing libraries.

The dependencies have been selected based on the code quality and amount of further dependencies they would introduce.

There are two dependencies, one of them `php-collective/file-storage-factories` is just to abstract the real dependency `flysystem\flysystem`.

Flysystem is the underlying storage abstraction library that provides the functionality to write to different storage backends.
