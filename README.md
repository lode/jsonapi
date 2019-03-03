# jsonapi [![Build Status](https://travis-ci.org/lode/jsonapi.svg?branch=master)](https://travis-ci.org/lode/jsonapi)

A simple and human-friendly library for api servers (php serving json).

It allows you to generate json output according to the [JSON:API v1.1](https://jsonapi.org/) standard,
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
		"version": "1.1"
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
		"version": "1.1"
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

$exception = new Exception('That is not valid', 422);

$document = ErrorsDocument::fromException($exception);
$document->sendResponse();
```

Which will result in:

```json
{
	"jsonapi": {
		"version": "1.1"
	},
	"errors": [
		{
			"status": "422",
			"code": "Exception",
			"meta": {
				"class": "Exception",
				"message": "That is not valid",
				"code": 422,
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

This library supports [v1.1 of the JSON:API specification](https://jsonapi.org/format/1.1/).

It has support for generating & sending documents with:

- single resources
- resource collections
- to-one and to-many relationships
- errors (easily turning exceptions into jsonapi output)
- v1.1 extensions via profiles
- v1.1 @-members for JSON-LD and others

Next to custom extensions, the following [official extensions](https://jsonapi.org/extensions/) are included:

- Cursor Pagination ([example code](/examples/cursor_pagination_profile.php), [specification](https://jsonapi.org/profiles/ethanresnick/cursor-pagination/))

Plans for the future include:

- support v1.1 of the specification ([#40](https://github.com/lode/jsonapi/pull/40))
- parse request options: sparse fields, sorting, pagination, filtering ([#44](https://github.com/lode/jsonapi/issues/44))
- parse requests for creating, updating and deleting resources and relationships ([#5](https://github.com/lode/jsonapi/issues/5))


## Contributing

[Pull Requests](https://github.com/lode/jsonapi/pulls) or [issues](https://github.com/lode/jsonapi/issues) are welcome!


## Licence

[MIT](/LICENSE)
