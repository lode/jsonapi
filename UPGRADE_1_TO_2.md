# Upgrade from library v1 to v2

- [Introduction](#introduction)
- [Composer update](#composer-update)
- [Changed](#changed)
  - [The main classes have 'Document' appended](#the-main-classes-have-document-appended)
  - [Using camelCase](#using-camelcase)
  - [Method to add primary property is shortened](#method-to-add-primary-property-is-shortened)
  - [Adding multiple key-value pairs at construction only](#adding-multiple-key-value-pairs-at-construction-only)
  - [Change logic via single options argument](#change-logic-via-single-options-argument)
  - [Renamed methods](#renamed-methods)
  - [Exception handling](#exception-handling)
  - [Levels of links (and meta)](#levels-of-links-and-meta)
- [Removed](#removed)
  - [`\jsonapi\exception` class](#jsonapiexception-class)
  - [`LINK_LEVEL_BOTH` constant](#link_level_both-constant)
  - [`fill_*()` methods](#fill_-methods)
  - [Debug mode](#debug-mode)
  - [Links are not auto generated anymore](#links-are-not-auto-generated-anymore)
- [Added](#added)
  - [Specification-based methods](#specification-based-methods)
  - [Jsonapi object](#jsonapi-object)
  - [`LEVEL_JSONAPI` constant](#level_jsonapi-constant)
  - [Unit and output tests](#unit-and-output-tests)
  - [php7 ready](#php7-ready)
- [Reference of changes](#reference-of-changes)

---

## Introduction

Most of the interface and output stayed the same. However, v2 is breaking backwards compatibility. The main reasons are:

- the jsonapi specification started to recommend CamelCase in their v1.1, thus all method names changed
- php7 marks `resource` a reserved keyword, thus the main class name changed (php5 is still supported)
- the current implementation offers too little flexibility, in v2 the output changed

The main feature v2 introduces is two different ways of building the json:

- a **human-friendly way** (already in v1): a way to build the json without needing to understand the specification
- a **specification-based way** (new in v2): to easier define the small details of each part of the output

See the [README.md](/README.md) and the [examples](/examples) for more information.

_Note: v2.0 of the library doesn't yet support v1.1 of the specification._

## Composer update

As v2 uses completely new files the files for v1 could be left intact.
This means a composer update can be done to v2 without any impact.
A migration to the new code can be made step-by-step.

However, the old code will be removed in a future minor release of v2, and will not be seen as a BC break.

## Changed

### The main classes have 'Document' appended

Names of all starter classes to build the output (e.g. `collection`) have 'Document' appended.

Old:

```php
$document = new resource();
$document = new collection();
$document = new errors();
```

New:

```php
$document = new ResourceDocument();
$document = new CollectionDocument();
$document = new ErrorsDocument();
```

### Using camelCase

All methods changed to using camelCase.

Old:

```php
$document->add_link();
$document->add_meta();
$document->send_response();
```

New:

```php
$document->addLink();
$document->addMeta();
$document->sendResponse();
```

### Method to add primary property is shortened

The methods to add the primary property to documents or objects now use the shorter `add()` vs `addX()`.

Old:

```php
$resourceDocument->add_data();
$errorsDocument->add_error();
```

New:

```php
$resourceDocument->add();
$errorsDocument->add();
```

### Adding multiple key-value pairs at construction only

Adding an array of key-value pairs changes from `->fill_x()` to `::fromArray()`.
And thus is only possible when starting an object, not to append on it.

Old:

```php
$document = new resource($type, $id);
$document->fill_data($array);
$document->add_data($key, $value);
```

New:

```php
$document = ResourceDocument::fromArray($array, $type, $id);
$document->add($key, $value);
```

What is thus not possible anymore is to first do `add_data()` followed by `fill_data()`.
If you really need this, the workaround is to foreach over the array and call `add()`.

### Change logic via single options argument

Methods now use a single `$options` argument, instead of multiple loose arguments, to adjust logic.
Note this is not used for changing content, only for changing logic.

Old:

```php
$document->add_relation($key, $relatedResource, $skip_include=true);
$document->get_json($encode_options=JSON_PRETTY_PRINT);
$document->send_response($content_type='application/json', $encode_options=JSON_PRETTY_PRINT, $response='{"data":[]}', $jsonp_callback='callback');
```

New:

```php
$document->addRelationship($key, $relatedResource, $links=[], $meta=[], $options=['includeContainedResources' => false]);
$document->toJson($options['encodeOptions' => JSON_PRETTY_PRINT]);
$document->sendResponse($options[
	'contentType'   => 'application/json',
	'encodeOptions' => JSON_PRETTY_PRINT,
	'json'          => '{"data":[]}',
	'jsonpCallback' => 'callback'
]);
```

### Renamed methods

Some specific methods changed name.

Old:

```php
$document->add_relation('foo', $relatedResource);
$document->get_array();
$document->get_json();
```

New:

```php
$document->addRelationship('foo', $relatedResource); // note relation*ship* postfix
$document->toArray();
$document->toJson();
```

### Exception handling

Transforming an exception to jsonapi output changed.
Only `$exception->getCode()` is used outside meta, `$exception->getMessage()` (and everything else) is now placed inside meta.
Also, this is only done when `$options['exceptionExposeDetails']` is passed, previously `$debug` mode needed to be turned on.

Old

```php
$exception = new \Exception($message='user not found', $code=404);
$document = new errors($exception);
$document->send_response();
```

```json
{
	"errors": [
		{
			"status": "404 Not Found",
			"code": "user not found",
			"meta": {
				"file": "UPGRADE_1_TO_2.md",
				"line": 177
			}
		}
	]
}
```

New:

```php
$exception = new \Exception($message='user not found', $code=404);
$document = ErrorsDocument::fromException($exception);
$document->sendResponse();
```

```json
{
	"errors": [
		{
			"status": "404",
			"code": "404",
			"title": "Exception",
			"meta": {
				"message": "user not found",
				"file": "UPGRADE_1_TO_2.md",
				"line": 177
			}
		}
	]
}
```

### Levels of links (and meta)

There's multiple changes here.

_Note: these levels can now also be used for adding meta._

##### Link levels use another name, `data` is now called `resource`

Old:

```php
$document->add_link('foo', 'https://jsonapi.org', $meta, $level=resource::LINK_LEVEL_DATA);
$document->add_link('foo', 'https://jsonapi.org', $meta, $level=resource::LINK_LEVEL_ROOT);
```

New:

```php
$document->addLink('foo',  'https://jsonapi.org', $meta, $level=Document::LEVEL_RESOURCE);
$document->addLink('foo',  'https://jsonapi.org', $meta, $level=Document::LEVEL_ROOT);
```

##### The default level changed from `LINK_LEVEL_DATA`/`LEVEL_RESOURCE` to `LEVEL_ROOT`

Old:

```php
$jsonapi->add_link('foo', 'https://jsonapi.org', $meta); // defaulted to LINK_LEVEL_DATA
$jsonapi->add_link('foo', 'https://jsonapi.org', $meta, $level=resource::LINK_LEVEL_ROOT); // set to LINK_LEVEL_ROOT explicitly
```

New:

```php
$jsonapi->addLink('foo',  'https://jsonapi.org', $meta, $level=Document::LEVEL_RESOURCE); // set to LEVEL_RESOURCE explicitly
$jsonapi->addLink('foo',  'https://jsonapi.org', $meta); // defaults to LEVEL_ROOT
```

##### The `self` link is set at `LEVEL_ROOT` for collections, and at `LEVEL_RESOURCE` for resources, not both anymore

Old:

```php
$resourceDocument->set_self_link('/example'); // defaulted to LINK_LEVEL_BOTH (root & resource)
$collectionDocument->set_self_link('/example'); // defaulted to LINK_LEVEL_ROOT
```

New:

```php
$resourceDocument->setSelfLink('/example'); // defaults to LEVEL_RESOURCE only
$collectionDocument->setSelfLink('/example'); // defaults to LEVEL_ROOT still
```

## Removed

### `\jsonapi\exception` class

The `\jsonapi\exception` class to generate error documents is removed.

Old:

```php
$jsonapiException = new jsonapi\exception($message, $code, $previous, $friendly_message, $about_link);
$jsonapiException->send_response();
```

New:

```php
$exception = new \Exception($message, $code, $previous);
$errorObject = ErrorObject::fromException($exception);
$errorObject->setHumanExplanation($genericTitle, $specificDetails=null, $specificAboutLink); // replaces $friendly_message and $about_link
$document = new ErrorsDocument($errorObject);
$document->sendResponse();
```

### `LINK_LEVEL_BOTH` constant

The `resource::LINK_LEVEL_BOTH` is removed.

Old:

```php
$document->add_link('foo', 'https://jsonapi.org', $meta, $level=resource::LINK_LEVEL_BOTH);
```

New:

```php
$document->addLink('foo',  'https://jsonapi.org', $meta, $level=Document::LEVEL_RESOURCE);
$document->addLink('foo',  'https://jsonapi.org', $meta, $level=Document::LEVEL_ROOT);
```

### `fill_*()` methods

Adding data via `fill_data()` and other `fill_*()` methods is removed.
See [Adding multiple key-value pairs at construction only](#adding-multiple-key-value-pairs-at-construction-only).

### Debug mode

Debug mode is removed as an all-or-nothing setting, and replaced by specific options.
See [Change logic via single options argument](#change-logic-via-single-options-argument) and [Exception handling](#exception-handling).

It was automatically guessed based on the `display_errors` directive, and managing multiple things:

- encode json with `JSON_PRETTY_PRINT`
  ```php
  // in v2, use one of:
  $document->sendResponse($options=['prettyPrint' => true]);
  $document->toJson($options=['prettyPrint' => true]);
  ```
- send `application/json` instead of `application/vnd.api+json`
  ```php
  // in v2, use:
  $document->sendResponse($options=['contentType' => Document::CONTENT_TYPE_DEBUG]);
  ```
- output `code` from error objects, and exception details `file`, `line`, `trace` (hidden if debug was false)
  ```php
  // in v2, use one of:
  $errorsDocument->addException($exception, $options=['exceptionExposeDetails' => true]);
  ErrorsDocument::fromException($exception, $options=['exceptionExposeDetails' => true]);
  ErrorObject::fromException($exception, $options=['exceptionExposeDetails' => true]);
  ```

### Links are not auto generated anymore

The `self` link at root, the `self` link at the resource, and the `self` and `related` links for relationships are not automatically added anymore.
When actually supported, add them explicitly.

Old:

```php
$document = new resource('user', 42);
$document->add_relation('foo', new resource('user', 24));
```

```json
{
	"links": {
		"self": "/UPGRADE_1_TO_2.md"
	},
	"data": {
		"type": "user",
		"id": 42,
		"relationships": {
			"foo": {
				"data": {
					"type": "user",
					"id": 24
				},
				"links": {
					"self": "/UPGRADE_1_TO_2.md/relationships/foo",
					"related": "/UPGRADE_1_TO_2.md/foo"
				}
			}
		},
		"links": {
			"self": "/UPGRADE_1_TO_2.md"
		}
	}
}
```

New, same code, without links:

```php
$document = new ResourceDocument('user', 42);
$document->addRelationship('foo', new ResourceObject('user', 24));
```

```json
{
	"data": {
		"type": "user",
		"id": "42",
		"relationships": {
			"foo": {
				"data": {
					"type": "user",
					"id": "24"
				}
			}
		}
	}
}
```

New, with links:

```php
$document = new ResourceDocument('user', 42);

$document->setSelfLink('https://example.org/user/42'); // default at LEVEL_RESOURCE only
$document->setSelfLink('https://example.org/user/42', $meta=[], Document::LEVEL_ROOT);

$relationshipLinks = [
	'self'    => 'https://example.org/user/42/relationships/foo',
	'related' => 'https://example.org/user/42/foo',
];
$document->addRelationship('foo', new ResourceObject('user', 24), $relationshipLinks);
```

```json
{
	"links": {
		"self": "https://example.org/user/42"
	},
	"data": {
		"type": "user",
		"id": "42",
		"relationships": {
			"foo": {
				"links": {
					"self": "https://example.org/user/42/relationships/foo",
					"related": "https://example.org/user/42/foo"
				},
				"data": {
					"type": "user",
					"id": "24"
				}
			}
		},
		"links": {
			"self": "https://example.org/user/42"
		}
	}
}
```

## Added

### Specification-based methods

Specification-based methods are available to adjust each part of the output in the small details.

In general there's two types you can use: Documents and Objects.
Documents are what v1 also used, a class to build the output with.
Next to the human-friendly methods (like `add()`), there's methods using terms of the specification (like `setAttributesObject()`).
Objects are used for these new methods. There's an Object for each part the specification describes.

Mostly you'll use the human-friendly methods, as they are usually easier and need less code.
But the specification-based ones are needed to adjust some parts and make you understand more.

See the [examples/resource_human_api](/examples//resource_human_api.php) and [examples/resource_spec_api](/examples//resource_spec_api.php).

### Jsonapi object

Every document by defaults get a jsonapi object in the output:

```json
{
	"jsonapi": {
		"version": "1.0"
	}
}
```

This helps discovery and lets clients know which version of the specification to use.

The object can be changed:

```php
$jsonapiObject = new JsonapiObject();
$jsonapiObject->setVersion(Document::JSONAPI_VERSION_1_1);
$jsonapiObject->addMeta('foo', 'bar');
$document->setJsonapiObject($jsonapiObject);
```

```json
{
	"jsonapi": {
		"version": "1.1",
		"meta": {
			"foo": "bar"
		}
	}
}
```

Or removed:

```php
$document->unsetJsonapiObject();
```

### `LEVEL_JSONAPI` constant

A new level `Document::LEVEL_JSONAPI` is added for when adding meta.

```php
$document = new ResourceDocument('user', 42);
$document->addMeta('foo', 'root', $level=Document::LEVEL_ROOT);
$document->addMeta('foo', 'resource', $level=Document::LEVEL_RESOURCE);
$document->addMeta('foo', 'jsonapi', $level=Document::LEVEL_JSONAPI);
```

```json
{
	"jsonapi": {
		"version": "1.0",
		"meta": {
			"foo": "jsonapi"
		}
	},
	"meta": {
		"foo": "root"
	},
	"data": {
		"type": "user",
		"id": "42",
		"meta": {
			"foo": "resource"
		}
	}
}
```

### Unit and output tests

Tests are added to:

- exception are thrown when using the library incorrectly
- options for changing logic are applied correctly
- make sure the output is how it should be

### php7 ready

The code is tested against php7. But will continue to support php5.

## Reference of changes

Old | New
--- | ---
**base** | n/a
`base::$debug;` | See [Debug mode](#debug-mode)
`base::$appRoot;`<br><br><br><br>&nbsp; | Use `$options['stripExceptionBasePath'=>'...']`<br>on `ErrorObject::fromException()`<br>or `ErrorsDocument::fromException()`<br>or `$errorsDocument = new ErrorsDocument(); $errorsDocument->addException()`
&nbsp; | &nbsp;
**collection** | **CollectionDocument**
`$collection = new collection();` | `$collectionDocument = new CollectionDocument();`
`$collection->add_resource();` | `$collectionDocument->addResource();`
`$collection->fill_collection();` | `$collectionDocument = CollectionDocument::fromResources();`
&nbsp; | &nbsp;
**error** | **ErrorObject**
`$error = new error();`<br>&nbsp; | `$errorObject = new ErrorObject();`<br>Same arguments, same order, skipping the third argument.
`$error->set_http_status();` | `$errorObject->setHttpStatusCode();`
`$error->set_error_message();` | `$errorObject->setApplicationCode();`
`$error->set_friendly_message();` | `$errorObject->setHumanTitle();`
`$error->set_friendly_detail();` | `$errorObject->setHumanDetails();`
`$error->blame_post_body();`<br>&nbsp; | `$errorObject->blameJsonPointer();`
`$error->blame_get_parameter();` | `$errorObject->blameQueryParameter();`
`$error->set_about_link();` | `$errorObject->setAboutLink();`
`$error->set_identifier();` | `$errorObject->setUniqueIdentifier();`
`$error->add_meta();` | `$errorObject->addMeta();`
`$error->fill_meta();` | `$errorObject->setMetaObject(MetaObject::fromArray());`
&nbsp; | &nbsp;
**errors** | **ErrorsDocument**
`$errors = new errors();`<br>&nbsp; | `$errorsDocument = new ErrorsDocument(); $errorsDocument->add();`<br>Same arguments, same order, skipping the third argument
`$errors->send_response();`<br>&nbsp; | `$errorsDocument->sendResponse();`<br>See [Change logic via single options argument](#change-logic-via-single-options-argument).
`$errors->set_http_status();` | `$errorsDocument->setHttpStatusCode();`
`$errors->add_error();`<br>&nbsp; | `$errorsDocument->add();`<br>Same arguments, same order, skipping the third argument.
`$errors->fill_errors();` | Removed.
`$errors->add_exception();` | `$errorsDocument->addException();`
&nbsp; | &nbsp;
**exception** | _Removed, see [Removed - `\jsonapi\exception` class](#jsonapiexception-class)._
&nbsp; | &nbsp;
**resource** | **ResourceDocument**
`resource::RELATION_TO_MANY` | `RelationshipObject::TO_MANY;`
`resource::RELATION_TO_ONE` | `RelationshipObject::TO_ONE;`
`resource::RELATION_LINKS_`<br>`resource::LINK_LEVEL_`<br>`resource::SELF_LINK_`<br>`resource::$self_link_data_level`<br>`resource::$relation_links` | See [Links are not auto generated anymore](#links-are-not-auto-generated-anymore).<br><br><br><br>&nbsp;
`$resource = new resource();` | `$resourceDocument = new ResourceDocument();`
`$resource->add_data();` | `$resourceDocument->add();`
`$resource->fill_data();` | `$resourceDocument = ResourceDocument::fromArray();`
`$resource->add_relation();`<br>&nbsp; | `$resourceDocument->addRelationship();`<br>See [Change logic via single options argument](#change-logic-via-single-options-argument).
`$resource->fill_relations();` | No direct replacement.
`$resource->add_link();` | `$resourceDocument->addLink();`
`$resource->set_self_link();` | `$resourceDocument->setSelfLink();`
`$resource->add_self_link_meta();`<br>&nbsp; | `$resourceDocument->setSelfLink();`<br>Also see [Links are not auto generated anymore](#links-are-not-auto-generated-anymore).
`$resource->add_meta();` | `$resourceDocument->addMeta();`
`$resource->fill_meta();` | `$resourceDocument->setMetaObject(MetaObject::fromArray());`
&nbsp; | &nbsp;
**response** | **Document**, **MetaDocument** or **ResourceDocument**
`response::STATUS_*` | Use own supplied hard-coded http status codes.
`response::CONTENT_TYPE_OFFICIAL;` | `Document::CONTENT_TYPE_OFFICIAL;`
`response::CONTENT_TYPE_DEBUG;` | `Document::CONTENT_TYPE_DEBUG;`
`response::CONTENT_TYPE_JSONP ` | `Document::CONTENT_TYPE_JSONP;`
`response::ENCODE_DEFAULT;`<br><br>&nbsp; | Use `$options['encodeOptions'=>JSON_UNESCAPED_SLASHES \| JSON_UNESCAPED_UNICODE]`<br>on `sendResponse()` or `toJson()`
`response::ENCODE_DEBUG;`<br><br>&nbsp; | Use `$options['encodeOptions'=>JSON_UNESCAPED_SLASHES \| JSON_UNESCAPED_UNICODE \| JSON_PRETTY_PRINT]`<br>on `sendResponse()` or `toJson()`
`response::JSONP_CALLBACK_DEFAULT`<br>&nbsp; | Use `$options['jsonpCallback'=>'...']`<br>on `sendResponse()` or `toJson()`
`response::$send_status_headers` | Removed, instead don't call `sendResponse()`
`$response = new response();` | `$metaDocument = new MetaDocument();`
`$response->__toString();` | `$metaDocument->toJson();`
`$response->get_json();`<br>&nbsp; | `$metaDocument->toJson();`<br>See [Change logic via single options argument](#change-logic-via-single-options-argument).
`$response->send_response();`<br>&nbsp; | `$metaDocument->sendResponse();`<br>See [Change logic via single options argument](#change-logic-via-single-options-argument).
`$response->set_http_status();` | `$metaDocument->setHttpStatusCode();`
`$response->set_redirect_location();` | Removed, set redirect headers manually.
`$response->add_link();` | `$metaDocument->addLink();`
`$response->fill_links();` | `$metaDocument->setLinksObject(LinksObject::fromArray());`
`$response->set_self_link();` | `$resourceDocument->setSelfLink();`
`$response->add_self_link_meta();`<br>&nbsp; | `$resourceDocument->setSelfLink();`<br>Also see [Links are not auto generated anymore](#links-are-not-auto-generated-anymore).
`$response->add_meta();` | `$metaDocument->addMeta();`
`$response->fill_meta();` | `$metaDocument = MetaDocument::fromArray();`
