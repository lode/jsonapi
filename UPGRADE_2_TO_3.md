# Upgrade from library v2 to v3

## Enums

All constants have been replaced by enums.

Content types:
- `Document::CONTENT_TYPE_OFFICIAL` (`'application/vnd.api+json'`) to `ContentTypeEnum::Official`
- `Document::CONTENT_TYPE_DEBUG` (`'application/json'`) to `ContentTypeEnum::Debug`
- `Document::CONTENT_TYPE_JSONP` (`'application/javascript'`) to `ContentTypeEnum::Jsonp`

Jsonapi versions:
- `Document::JSONAPI_VERSION_1_0` (`'1.0'`) to `JsonapiVersionEnum::V_1_0`
- `Document::JSONAPI_VERSION_1_1` (`'1.1'`) to `JsonapiVersionEnum::V_1_1`
- `Document::JSONAPI_VERSION_LATEST` (`'1.1'`) to `JsonapiVersionEnum::latest()` (still refering to v1.1)

Document levels:
- `Document::LEVEL_ROOT` (`'root'`) to `DocumentLevelEnum::Root`
- `Document::LEVEL_JSONAPI` (`'jsonapi'`) to `DocumentLevelEnum::Jsonapi`
- `Document::LEVEL_Resource` (`'resource'`) to `DocumentLevelEnum::Resource`

Sorting orders (as return value in the `order` field from `RequestParser->getSortFields()`):
- `RequestParser::SORT_ASCENDING` (`'ascending'`) to `SortOrderEnum::Ascending`
- `RequestParser::SORT_DESCENDING` (`'descending'`) to `SortOrderEnum::Descending`

Relationship types:
- `RelationshipObject::TO_ONE` (`'one'`) to `RelationshipTypeEnum::ToOne`
- `RelationshipObject::TO_MANY` (`'many'`) to `RelationshipTypeEnum::ToMany`

Validator object containers (only used internally):
- `Validator::OBJECT_CONTAINER_*` to `ObjectContainerEnum::*`


## Strict type checking

When using the library you shouldn't notice this unless passing values of the wrong type. When extending the library you probably will notice this when overriding methods.

In both cases the upgrade is relatively easy, you can just follow php's type errors. Using phpstan will also help you find typing mismatches.

Some invalid types were already checked at run-time and threw library exceptions. This now changed into php type errors.


## Object properties are uninitialized by default

Objects more often use uninitialized properties. In some cases, `has*()` helper methods have been added to support the new flow. Otherwise, an `isset()` can be used if needed.

_This should only affect you when you extend the libraries classes. Otherwise you can just use the library methods to handle these properties._

- `->links`, on `Document`, `ErrorObject`, `RelationshipObject`, `ResourceObject`, can be checked with `->hasLinks()`
- `Document`: `->httpStatusCode`, `->jsonapi`, `->meta`
- `ErrorObject`: `->id`, `->code`, `->title`, `->detail`, `->meta`, `->httpStatusCode`
- `JsonapiObject`: `->meta`, `->version`
- `LinkObject`: `->href`, `->rel`, `->describedby`, `->title`, `->type`, `->meta`
- `RelationshipObject`: `->meta`, `->resource`, `->type`
- `ResourceDocument`: `->resource`
- `ResourceIdentifierObject`: `->type`, `->id`, `->lid`, `->meta`, `->validator`
- `ResourceObject`: `->attributes`, `->relationships`


## Json encoding and decoding errors

The default encoding options changed to also include `JSON_THROW_ON_ERROR`. Failure to encode already used to throw an exception, but now it will default to a `\JsonException`.


## Removed deprecations

- Old v1-style classes (`base.php`, `collection.php`, `error.php`, `errors.php`, `exception.php`, `resource.php`, `response.php`)
- Array-style links (`->appendLink()`, `->addLinksArray()`, `->appendLinkObject()`, `ErrorObject->appendTypeLink()`, `LinksArray`, `LinksObject->append()`)
- `CursorPaginationProfile->getKeyword()`
- `Converter::mergeProfilesInContentType()`, use `Converter::prepareContentType()` instead
