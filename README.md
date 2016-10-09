# ElasticSearch PHP Helper

[![Build Status](https://travis-ci.org/jancyril/elasticsearch-php-helper.svg?branch=master)](https://travis-ci.org/jancyril/elasticsearch-php-helper)

This class utilizes the official PHP Client for Elasticsearch that can be found [here](https://github.com/elastic/elasticsearch-php).

This class contains methods for common operation for accessing and manipulating documents in Elasticsearch. This will help you avoid the repetitive task of setting up the Elasticsearch client builder everytime you need to use it.

`Note: This class contains some PHP 7 specific syntax.`

## Requirement

* PHP Client for Elasticsearch

## Getting Started

Copy the ElasticSearch.php class into your source directory.

Provide your Elasticsearch host - either via environment variable (preferred) or modifying the constructor method.

Create a new instance of the ElasticSearch.php class.

`$elastic = new ElasticSearch;`

Set and create your index.

`$elastic->setIndex('testing');`

`$elastic->createIndex();`

Set your types and the mapping for it.

`$elastic->setType('users');`

`$elastic->setMapping($array);`

## Example Usage

Adding a new document.

`$data = ['id' => 1, 'username' => 'jancyril', 'role' => 'admin'];`

`$elastic->put($data);`

Adding documents in bulk.

`$data = [
    ['id' => 1, 'username' => 'jancyril', 'role' => 'admin'],
    ['id' => 2, 'username' => 'foxlance', 'role' => 'developer'],
    ['id' => 3, 'username' => 'aceraven777', 'role' => 'developer'],
    ['id' => 4, 'username' => 'admin', 'role' => 'developer']
];`

`$elastic->bulk($data);`

Updating a document.

`$data = ['username' => 'JC'];`

`$elastic->put(1, $data);`

Deleting a document

`$elastic->delete(1);`

Getting a document

`$elastic->get(1);`

Searching a document

`$elastic->match('username', 'jancyril');`