# File Storage

[![CI](https://img.shields.io/github/actions/workflow/status/php-collective/file-storage/ci.yml?branch=master&style=flat-square)](https://github.com/php-collective/file-storage/actions)
[![Coverage](https://codecov.io/gh/php-collective/file-storage/branch/master/graph/badge.svg)](https://codecov.io/gh/php-collective/file-storage)
[![Latest Stable Version](https://img.shields.io/packagist/v/php-collective/file-storage?style=flat-square)](https://packagist.org/packages/php-collective/file-storage)
[![Total Downloads](https://img.shields.io/packagist/dt/php-collective/file-storage?style=flat-square)](https://packagist.org/packages/php-collective/file-storage)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat-square)](https://phpstan.org/)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg?style=flat-square)](https://php.net)
[![Software License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)

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
