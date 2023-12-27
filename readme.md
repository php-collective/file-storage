# File Storage

[![CI](https://github.com/php-collective/file-storage/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/php-collective/file-storage/actions/workflows/ci.yml)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

A framework agnostic file storage system.

Dealing with uploads, storing and managing the files has been very often painful and cumbersome. This library tries to make this more easy and convenient for you - no matter what framework you are using.

This library is pretty much the same as these plugins for [Laravel](https://github.com/spatie/laravel-medialibrary), [Yii](https://github.com/yii2tech/file-storage) and [Cake](https://github.com/burzum/cakephp-file-storage), but not tied to any framework or ORM and less tight coupled.

## Features

 * **Store files on almost everything:** Local disk, Amazon S3, Dropbox... and many more through the fantastic [league/flysystem](thephpleague/flysystem) library.
 * Framework-agnostic
 * Image processing (optional feature / dependency)
 * Image optimization (optional feature / dependency)
 * Provides factories for the adapters
 * As lite as possible on dependencies

## Installation

```sh
composer require php-collective/file-storage
```

## Documentation

Please start by reading [docs/](/docs/readme.md) in this repository.

## Example

Take a look at [example.php](example.php) or even run it:

```php
php example.php
```

The example should give you an exhaustive overview of the library.
