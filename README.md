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

For a collection response, data is an array of resources.
Errors can also be send as response, even automatically by exceptions.

Examples for all kind of responses are in the [/examples](/examples) directory.


## Installation

[Use Composer](http://getcomposer.org/). And use require to get the latest stable version:

```
composer require alsvanzelf/jsonapi
```


## To Do

Right now, this library handles all the basics:

- generating single resources
- generating resource collections
- handling error responses
- sending out the json response with correct http headers

Plus some handy tools:

- easy turning thrown exceptions into jsonapi responses
- constants for easy setting http status codes
- sending out redirect locations

Plans for the [near](https://github.com/lode/jsonapi/labels/current%20focus)
and [later](https://github.com/lode/jsonapi/issues?utf8=%E2%9C%93&q=is%3Aopen+-label%3A%22current+focus%22+) future include:

- import a database array as a collection response ([#2](https://github.com/lode/jsonapi/issues/2))
- accept a collection as to-many relation in a resource ([#3](https://github.com/lode/jsonapi/issues/3))
- handle creating, updating and deleting resources ([#5](https://github.com/lode/jsonapi/issues/5))


## Contributing

Pull Requests or issues are welcome!


## Licence

[MIT](/LICENSE)
