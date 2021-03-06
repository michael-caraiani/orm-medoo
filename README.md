# TiSuit ORM / Medoo
[![Build Status](https://travis-ci.org/TiSuit/orm-medoo.svg?branch=master)](https://travis-ci.org/TiSuit/orm-medoo) [![Coverage Status](https://coveralls.io/repos/TiSuit/orm-medoo/badge.svg?branch=master&service=github)](https://coveralls.io/github/TiSuit/orm-medoo?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/b8a208b6-c03b-4a08-b43f-74b17b35b0f4/mini.png)](https://insight.sensiolabs.com/projects/c63aa255-cc5f-4ee2-aa6d-75a2114d8833) [![Latest Stable Version](https://poser.pugx.org/tisuit/orm-medoo/version)](https://packagist.org/packages/tisuit/orm-medoo) [![Latest Unstable Version](https://poser.pugx.org/tisuit/orm-medoo/v/unstable)](//packagist.org/packages/tisuit/orm-medoo) [![Total Downloads](https://poser.pugx.org/tisuit/orm-medoo/downloads)](https://packagist.org/packages/tisuit/orm-medoo) [![Monthly Downloads](https://poser.pugx.org/tisuit/orm-medoo/d/monthly)](https://packagist.org/packages/tisuit/orm-medoo) [![composer.lock available](https://poser.pugx.org/tisuit/orm-medoo/composerlock)](https://packagist.org/packages/tisuit/orm-medoo) [![License](https://poser.pugx.org/tisuit/orm-medoo/license)](https://packagist.org/packages/tisuit/orm-medoo)

Wrapper on [medoo.in](http://medoo.in) with integration for TiSuit

## Table of Contents


<!-- vim-markdown-toc GFM -->

* [Installation](#installation)
    - [Composer](#composer)
    - [Configuration](#configuration)
* [Usage](#usage)
    - [Get entity](#get-entity)
    - [Entity](#entity)
    - [Migrations and Seeds](#migrations-and-seeds)
* [Documentation](#documentation)

<!-- vim-markdown-toc -->

## Installation

### Composer

```bash
composer require tisuit/orm-medoo
```

### Configuration

Create file `medoo.php` into your config dir with following contents:

```php
<?php

declare(strict_types=1);

return [
    'namespace' => '\App\Entity\\',
    'database_type' => 'mysql',
    'database_name' => 'tisuit',
    'server' => '127.0.0.1',
    'username' => 'travis',
    'password' => 'secret_password',
    'charset' => 'utf8',
    'port' => 3306,
    'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
];
```

And file `phinx.php` into your config dir with following contents:

```php
<?php

declare(strict_types=1);
$db = require __DIR__.'/medoo.php';

return [
    'paths' => [
        'migrations' => dirname(__DIR__).'/migrations',
        'seeds' => dirname(__DIR__).'/seeds',
    ],

    'environments' => [
        'default_database' => 'default',
        'default' => [
            'name' => $db['database_name'],
            'host' => $db['server'],
            'user' => $db['username'],
            'pass' => $db['password'],
            'port' => $db['port'],
            'charset' => $db['charset'],
            'adapter' => $db['database_type'],
        ],
    ],
];
```

Add `\TiSuit\ORM\Provider` into your providers list (`suit.php`)

## Usage

### Get entity

From any `\TiSuit\Core\Root` child class

**Load one entry**

```php
<?php

$authorById = $this->entity('author')->load($id);

$authorByProperty = $this->entity('author')->load('author@example.com', 'email');
```

**Load collection**

```php
<?php

//Load collection as relation of author
$author = $this->entity('author')->load($id);
$books = $author->getBooks();

//Load collection with WHERE filter
$books = $this->entity('book')->loadAll(['author_id' => $id]);
```

### Entity

This library provides you `\TiSuit\ORM\Entity` abstract class, which must be used as parent class of your project entities.

Each entity has following abstract methods:

**public function getTable(): string**

This function MUST return entity table name in database

**public function getValidators(): array**

Return array of [respect/validation](https://github.com/Respect/Validation) rules.

> NOTE: If you don't need validations, just return empty array

Structure:

```php
<?php
return [
    '<method_name>' => [
        '<field_name>' => <rule>
    ],
];
```

Example:

```php
<?php
use Respect\Validation\Validator as v;
return [
    'save' => [ //method name, default: save (will be called on Entity::save())
        'name' => v::stringType()->length(1,255),
    ],
];
```

**public function getRelations(): array**

Return array of relations with other entities

> NOTE: If you don't need relations, just return empty array

Structure:

```php
<?php
return [
    '<relation_name>' => [
        'entity' => '<related_entity_name>',
        'type' => 'has_one', //default, other options: has_many
        'key' => 'current_entity_key', //optional, default for has_one: <current_entity>_id, for has_many: id
        'foreign_key' => 'another_entity_key', //optional, default for has_one: id, for has_many: '<current_entity>_id'
    ],
];
```
Example (current entity: blog post, another entity: user):

```php
<?php
return [
    'author' => [ //has_one
        'entity' => 'user',
        'key' => 'author_id',
        'foreign_key' => 'id'
    ],
];
```

Example (same as above, but with defaults):

```php
<?php
return [
    'author' => [ //has_one
        'entity' => 'user',
    ],
];
```

This example can be called like `$blogPostEntity->getAuthor()`

### Migrations and Seeds

Just use phinx.

Phinx config file (for tisuit): `app/config/phinx.php`

## Documentation

* [medoo](https://medoo.in) - wrapper around PDO, used for DB manipulation
* [respect/validation](https://github.com/Respect/Validation) - validation library
* [phinx](https://phinx.org) - DB migrations the right way
