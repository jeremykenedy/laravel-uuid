# Laravel Uuid (Universally unique identifier)

[![Total Downloads](https://poser.pugx.org/jeremykenedy/uuid/d/total.svg)](https://packagist.org/packages/jeremykenedy/uuid)
[![Build Status](https://travis-ci.org/jeremykenedy/laravel-uuid.svg?branch=master)](http://travis-ci.org/jeremykenedy/laravel-uuid)
[![Latest Stable Version](https://poser.pugx.org/jeremykenedy/uuid/v/stable.svg)](https://packagist.org/packages/jeremykenedy/uuid)
[![Latest Unstable Version](https://poser.pugx.org/jeremykenedy/uuid/v/unstable.svg)](https://packagist.org/packages/jeremykenedy/uuid)
[![License](https://poser.pugx.org/jeremykenedy/uuid/license.svg)](https://raw.githubusercontent.com/jeremykenedy/laravel-auth/LICENSE)

Laravel package to generate a UUID according to the RFC 4122 standard. UUID Versions 1, 3, 4 and 5 are supported. With MIT license.

- [About](#about)
- [Requirements](#requirements)
- [Installation](#installation)
    - [Laravel 5.5 and above](#laravel-5.5-and-above)
    - [Laravel 5.4 and below](#laravel-5.4-and-below)
- [Basic Usage](#basic-usage)
- [Advanced Usage](#advanced-usage)
    - [UUID creation](#uuid-creation)
        - [UUID V1](#uuid-v1)
        - [UUID V3](#uuid-v3)
        - [UUID V4](#uuid-v4)
        - [UUID V5](#uuid-v5)
- [Additional Features](#additional-features)
    - [Import UUID](#import-uuid)
    - [Extract time](#extract-time)
    - [Extract Version](#extract-version)
- [Changelog](#changelog)
    - [2.*](#2.*)
- [License](#license)
- [Credits](#credits)

### About
Since Laravel `4.*` and `5.*` both rely on either `OpenSSL` or `Mcrypt`, the pseudo random byte generator now tries to use one of them. If both cannot be used (not a Laravel project?), the 'less random' `mt_rand()` function is used.

### Requirements
* [Laravel 5.3, 5.4 or 5.5+](https://laravel.com/docs/installation)

### Installation

##### Laravel 5.5 and above
1. From your projects root folder in terminal run:

```bash
    composer require jeremykenedy/uuid
```

* Uses package auto discovery feature, no need to edit the `config/app.php` file.

##### Laravel 5.4 and below
1. From your projects root folder in terminal run:

```bash
    composer require jeremykenedy/uuid:2.1.2
```

2. Register the package with laravel in `config/app.php` under `aliases` with the following:

```php
    'aliases' => [
        'Uuid' => jeremykenedy\Uuid\Uuid::class,
    ];
```

### Basic Usage

To quickly generate a UUID just do

```php
    Uuid::generate()
```
* This will generate a version 1 with a random ganerated MAC address.

### Advanced Usage

#### UUID creation

##### UUID V1
Generate a version 1, time-based, UUID. You can set the optional node to the MAC address. If not supplied it will generate a random MAC address.

```php
Uuid::generate(1,'00:11:22:33:44:55');
```

##### UUID V3
Generate a version 3, name-based using MD5 hashing, UUID

```php
Uuid::generate(3,'test', Uuid::NS_DNS);
```

##### UUID V4
Generate a version 4, truly random, UUID

```php
Uuid::generate(4);
```

##### UUID V5
Generate a version 5, name-based using SHA-1 hashing, UUID

```php
Uuid::generate(5,'test', Uuid::NS_DNS);
```

### Additional Features
###### Import UUID
* To import a UUID

```php
$uuid = Uuid::import('d3d29d70-1d25-11e3-8591-034165a3a613');
```

###### Extract Time
* Extract the time for a time-based UUID (version 1)

```php
$uuid = Uuid::generate(1);
dd($uuid->time);
```

###### Extract Version
* Extract the version of an UUID

```php
$uuid = Uuid::generate(4);
dd($uuid->version);
````

### Changelog
##### 2.*
* Laravel Uuid is now fully PSR-2, just like Laravel 5.1.
* Not that much has changed except for UPPERCASING the constants used in Laravel Uuid.
* Meaning `Uuid::nsDNS` is now `Uuid::NS_DNS` etc. Should be an easy fix.

### Credits
* Full development credit must go to [webpatser](https://github.com/webpatser). This package was forked and modified to be compliant with [MIT](https://opensource.org/licenses/MIT) licensing standards for production use.

## License
Laravel UUID is licensed under the MIT license for both personal and commercial products. Enjoy!
