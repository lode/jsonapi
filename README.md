# jsonapi

Simple and friendly library for api servers (PHP serving out JSON).

It generates json output according to the [jsonapi.org](http://jsonapi.org/) standard,
but aims to be easy to understand for people without knowledge of the jsonapi standard.


## Getting started

A small example:

```php
use alsvanzelf\jsonapi;

$user = new stdClass();
$user->id = 42;
$user->name = 'Zaphod Beeblebrox';
$user->heads = 2;

$jsonapi = new jsonapi\resource($type='user', $user->id);
$jsonapi->fill_data($user);
$jsonapi->send_response();
```

Which will result in:

```json
{
    "links": {
        "self": "/examples/resource.php"
    },
    "data": {
        "type": "user",
        "id": 42,
        "attributes": {
            "name": "Zaphod Beeblebrox",
            "heads": 2
        },
        "links": {
            "self": "/examples/resource.php"
        }
    }
}
```

Examples for all kind of responses are in the [/examples](/examples) directory.


## Installation

[Use Composer](http://getcomposer.org/). Add `alsvanzelf/jsonapi` to your project's `composer.json`:

```json
{
    "require": {
        "alsvanzelf/jsonapi": "dev-master"
    }
}
```


## To Do

Right now, this library handles all the basics:

- generating single resources
- generating resource collections
- handling error responses
- sending out the json response with correct http headers

Plans for the future include:

- import a database array as a collection response
- accept a collection as to-many relation in a resource
- sending out redirect locations and status codes for non-error responses
- handle creating, updating and deleting resources


## Contributing

Pull Requests or issues are welcome!


## Licence

[MIT](/LICENSE)
