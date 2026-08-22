# jsonapi

A simple and human-friendly library for api servers (php serving json).

It allows you to generate json output according to the [JSON:API v1.1](https://jsonapi.org/) standard,
while being easy to understand for people without knowledge of the jsonapi standard.

The JSON:API standard makes it easy for clients to fetch multiple resources in one call and understand the relations between them.
Read more about it at [jsonapi.org](https://jsonapi.org/).

The library's code uses strict typing and is checked against a high standard in phpstan and rector. It is tested extensively with 99% coverage.


## Installation

[Use Composer](http://getcomposer.org/) require to get the latest stable version:

```
composer require alsvanzelf/jsonapi
```

The library requires php 8.2 or higher, supporting 8.2-8.5. Use the latest [v2.x release](/releases/tag/v2.5.0) for lower php versions.

#### Upgrading from v1 or v2

If you used v1 or v2 of this library, see [UPGRADE_1_TO_2.md](/UPGRADE_1_TO_2.md) or [UPGRADE_2_TO_3.md](/UPGRADE_1_TO_2.md) on how to upgrade.



## Getting started

#### A small resource example

```php
use alsvanzelf\jsonapi\ResourceDocument;

$document = new ResourceDocument(type: 'user', id: 42);
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

#### A collection of resources with relationships

```php
use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\objects\ResourceObject;

$arthur      = new ResourceObject('user', 1);
$ford        = new ResourceObject('user', 2);
$zaphod      = new ResourceObject('user', 42);
$heartOfGold = new ResourceObject('starship', 2001);

$arthur->add('name', 'Arthur Dent');
$ford->add('name', 'Ford Prefect');
$zaphod->add('name', 'Zaphod Beeblebrox');
$heartOfGold->add('name', 'Heart of Gold');

$zaphod->addRelationship('drives', $heartOfGold);

$users    = [$arthur, $ford, $zaphod];
$document = CollectionDocument::fromResources(...$users);
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
			"id": "1",
			"attributes": {
				"name": "Arthur Dent"
			}
		},
		{
			"type": "user",
			"id": "2",
			"attributes": {
				"name": "Ford Prefect"
			}
		},
		{
			"type": "user",
			"id": "42",
			"attributes": {
				"name": "Zaphod Beeblebrox"
			},
			"relationships": {
				"drives": {
					"data": {
						"type": "starship",
						"id": "2001"
					}
				}
			}
		}
	],
	"included": [
		{
			"type": "starship",
			"id": "2001",
			"attributes": {
				"name": "Heart of Gold"
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
				"line": 137,
				"trace": []
			}
		}
	]
}
```

This can be useful for development. For production usage, you can better construct an `ErrorsDocument` with only specific values.

#### Using extensions and profiles

The [Atomic Operations extension](https://jsonapi.org/ext/atomic/) and the [Cursor Pagination profile](https://jsonapi.org/profiles/ethanresnick/cursor-pagination/) come packaged along. Any 3rd party of self-made extension can be applied with:

```php
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\interfaces\ExtensionInterface;

class ExampleExtension implements ExtensionInterface {
	public function getOfficialLink(): string {
		return 'https://example.org/extension-documentation';
	}
	
	public function getNamespace(): string {
		return 'foo';
	}
}

$document = new ResourceDocument('user', 42);
$document->add('name', 'Zaphod Beeblebrox');

$extension = new ExampleExtension();
$document->applyExtension($extension);
$document->addExtensionMember($extension, 'bar', 'baz');

$document->sendResponse();
```

Which will result in:

```json
{
    "foo:bar": "baz",
    "jsonapi": {
        "version": "1.1",
        "ext": [
            "https://example.org/extension-documentation"
        ]
    },
    "data": {
        "type": "user",
        "id": "42",
        "attributes": {
            "name": "Zaphod Beeblebrox"
        }
    }
}
```

A similar flow can be used for profiles.

#### Other examples

Examples for all kind of responses are in the [/examples](/examples) directory.


## Features

This library supports [v1.1 of the JSON:API specification](https://jsonapi.org/format/1.1/).

It has support for generating & sending documents with:

- single resources
- resource collections
- to-one and to-many relationships
- errors (easily turning exceptions into jsonapi output)
- v1.1 extensions and profiles
- v1.1 @-members for JSON-LD and others

Also there's tools to help processing of incoming requests:

- parse request options (include paths, sparse fieldsets, sort fields, pagination, filtering)
- parse request documents for creating, updating and deleting resources and relationships

Next to custom extensions/profiles, the following [official extensions/profiles](https://jsonapi.org/extensions/) are included:

- Atomic Operations extension ([example code](/examples/atomic_operations_extension.php), [specification](https://jsonapi.org/ext/atomic/))
- Cursor Pagination profile ([example code](/examples/cursor_pagination_profile.php), [specification](https://jsonapi.org/profiles/ethanresnick/cursor-pagination/))

Plans for the future include:

- validate request options ([#58](https://github.com/lode/jsonapi/issues/58))
- validate request documents ([#57](https://github.com/lode/jsonapi/issues/57))


## Contributing

If you use the library, please ask questions or share what can be improved by [creating an issue](https://github.com/lode/jsonapi/issues).

For bugs [issues](https://github.com/lode/jsonapi/issues) or [Pull Requests](https://github.com/lode/jsonapi/pulls) are welcome!

See [/script's README](/script) to steps to setup a local development environment.


## Licence

[MIT](/LICENSE)
