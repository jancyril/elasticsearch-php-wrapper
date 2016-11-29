# ElasticSearch PHP Wrapper

[![Build Status](https://travis-ci.org/jancyril/elasticsearch-php-wrapper.svg?branch=master)](https://travis-ci.org/jancyril/elasticsearch-php-wrapper)

This class utilizes the official PHP Client for Elasticsearch that can be found [here](https://github.com/elastic/elasticsearch-php).

This class contains methods for common operations to access and manipulate documents in Elasticsearch. This will help you avoid the repetitive task of setting up the Elasticsearch client builder everytime you need to use it.

`Note: This class contains some PHP 7 specific syntax.`

## Requirement

* PHP Client for Elasticsearch
* Elasticsearch 2.x

## Getting Started

Copy the ElasticSearch.php class into your source directory.

Provide your Elasticsearch host - either via environment variable (preferred) or modifying the constructor method.

Create a new instance of the ElasticSearch.php class.

```php
$elastic = new ElasticSearch;
```

Set and create your index.

```php
$elastic->setIndex('testing');

$elastic->createIndex();
```

Set your type and the mapping.

```php
$elastic->setType('users');

$elastic->setMapping($array);
```

## Example Usage

Adding a new document.

```php
$data = ['id' => 1, 'username' => 'jancyril', 'role' => 'admin'];

$elastic->put($data);
```

Adding documents in bulk.

```php
$data = [
    ['id' => 1, 'username' => 'jancyril', 'role' => 'admin'],
    ['id' => 2, 'username' => 'foxlance', 'role' => 'developer'],
    ['id' => 3, 'username' => 'aceraven777', 'role' => 'developer'],
    ['id' => 4, 'username' => 'admin', 'role' => 'developer']
];

$elastic->bulk($data);
```

Updating a document.

```php
$data = ['username' => 'JC'];

$elastic->update(1, $data);
```

Deleting a document

```php
$elastic->delete(1);
```

Getting a document

```php
$elastic->get(1);
```

Searching a document

```php
$elastic->match('username', 'jancyril');
```