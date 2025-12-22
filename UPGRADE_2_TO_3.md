# Upgrade from library v2 to v3

## Interfaces

When extending interfaces, you'll have to add method argument types and return types.

Check [all interfaces](/src/interfaces) for the correct typing.

## Enums

Content types:
- `Document::CONTENT_TYPE_OFFICIAL` to `ContentTypeEnum::Official`
- `Document::CONTENT_TYPE_DEBUG` to `ContentTypeEnum::Debug`
- `Document::CONTENT_TYPE_JSONP` to `ContentTypeEnum::Jsonp`

Jsonapi versions:
- `Document::JSONAPI_VERSION_1_0` to `JsonapiVersionEnum::V_1_0`
- `Document::JSONAPI_VERSION_1_1` to `JsonapiVersionEnum::V_1_1`
- `Document::JSONAPI_VERSION_LATEST` to `JsonapiVersionEnum::Latest`

Document levels:
- `Document::LEVEL_ROOT` to `DocumentLevelEnum::Root`
- `Document::LEVEL_JSONAPI` to `DocumentLevelEnum::Jsonapi`
- `Document::LEVEL_Resource` to `DocumentLevelEnum::Resource`

Sorting orders:
- `RequestParser->getSortFields()` returns a `SortOrderEnum` instead of `string` for the `order` field

Relationship types:
- `RelationshipObject::TO_ONE` to `RelationshipTypeEnum::ToOne`
- `RelationshipObject::TO_MANY` to `RelationshipTypeEnum::ToMany`

## Internal

Enums:
- `RequestParser::SORT_*` to `SortOrderEnum::*`
- `Validator::OBJECT_CONTAINER_*` to `ObjectContainerEnum::*`
