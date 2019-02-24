# jsonapi [![Build Status](https://travis-ci.org/lode/jsonapi.svg?branch=master)](https://travis-ci.org/lode/jsonapi)

A simple and human-friendly library for api servers (php serving json).

It allows you to generate json output according to the [JSON:API](https://jsonapi.org/) standard,
while being easy to understand for people without knowledge of the jsonapi standard.

The JSON:API standard makes it easy for clients to fetch multiple resources in one call and understand the relations between them.
Read more about it at [jsonapi.org](https://jsonapi.org/).


## Installation

[Use Composer](http://getcomposer.org/) require to get the latest stable version:

```
composer require alsvanzelf/jsonapi
```

#### Upgrading from v1

If you used v1 of this library, see [UPGRADE_1_TO_2.md](/UPGRADE_1_TO_2.md) on how to upgrade.



## Getting started

#### A small resource example

```php
use alsvanzelf\jsonapi\ResourceDocument;

$document = new ResourceDocument($type='user', $id=42);
$document->add('name', 'Zaphod Beeblebrox');
$document->add('heads', 2);
$document->sendResponse();
```

Which will result in:

```json
{
	"jsonapi": {
		"version": "1.0"
	},
	"data": {
		"type": "user",
		"id": "42",
		"attributes": {
			"name": "Zaphod Beeblebrox",
			"heads": 2
		}
	}
}
```

#### A collection of resources

```php
use alsvanzelf\jsonapi\CollectionDocument;

$document = new CollectionDocument();
$document->add('user', 42, ['name' => 'Zaphod Beeblebrox']);
$document->add('user', 1, ['name' => 'Ford Prefect']);
$document->add('user', 2, ['name' => 'Arthur Dent']);
$document->sendResponse();
```

Which will result in:

```json
{
	"jsonapi": {
		"version": "1.0"
	},
	"data": [
		{
			"type": "user",
			"id": "42",
			"attributes": {
				"name": "Zaphod Beeblebrox"
			}
		},
		{
			"type": "user",
			"id": "1",
			"attributes": {
				"name": "Ford Prefect"
			}
		},
		{
			"type": "user",
			"id": "2",
			"attributes": {
				"name": "Arthur Dent"
			}
		}
	]
}
```

#### Turning an exception into jsonapi

```php
use alsvanzelf\jsonapi\ErrorsDocument;

$exception = new Exception('something went wrong', 422);

$options = ['exceptionExposeDetails' => true]; // defaults to false
$document = ErrorsDocument::fromException($exception, $options);
$document->sendResponse();
```

Which will result in:

```json
{
	"jsonapi": {
		"version": "1.0"
	},
	"errors": [
		{
			"status": "422",
			"code": "422",
			"title": "Exception",
			"meta": {
				"message": "something went wrong",
				"file": "README.md",
				"line": 107,
				"trace": []
			}
		}
	]
}
```

Examples for all kind of responses are in the [/examples](/examples) directory.


## Features

This library handles all the basics:

- generating single resource documents
- generating resource collection documents
- adding to-one and to-many relationships
- generating errors documents (easily turning thrown exceptions into jsonapi output)
- sending out the json response with the correct http headers

Plans for the future include:

- support v1.1 of the specification
- handle creating, updating and deleting resources ([#5](https://github.com/lode/jsonapi/issues/5))


## Contributing

[Pull Requests](https://github.com/lode/jsonapi/pulls) or [issues](https://github.com/lode/jsonapi/issues) are welcome!


## Licence

[MIT](/LICENSE)
