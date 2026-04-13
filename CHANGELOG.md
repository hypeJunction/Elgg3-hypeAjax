<a name="1.3.0"></a>
# [1.3.0] — Elgg 4.x migration

### Breaking Changes

* Requires Elgg 4.0+ (was 3.x)
* `PayloadItem` no longer implements `\Serializable`. Callers must use
  `PayloadItem::encode()` / `PayloadItem::decode()` instead of PHP's
  `serialize()` / `unserialize()`.

### Changes

* `Elgg\BadRequestException` → `Elgg\Exceptions\Http\BadRequestException`
* `composer.json`: `elgg/elgg ^4.0`, `php >=7.4`, `composer/installers ^2.0`, psr-4 autoload
* `manifest.xml` removed
* Payload transport switched from PHP serialization to JSON encoding


<a name="1.2.1"></a>
## [1.2.1](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/compare/1.2.0...1.2.1) (2018-08-21)


### Bug Fixes

* **forms:** correctly apply callback function arguments ([7a49bb1](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/commit/7a49bb1))



<a name="1.2.0"></a>
# [1.2.0](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/compare/1.1.0...1.2.0) (2018-07-06)


### Features

* **forms:** adds promise-based ajax form api ([c056cb8](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/commit/c056cb8))



<a name="1.1.0"></a>
# [1.1.0](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/compare/1.0.0...1.1.0) (2018-06-29)


### Bug Fixes

* **ajax:** correctly pass unserialized values to views ([4fb65b7](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/commit/4fb65b7))
* **routes:** restrict route to ajax requests ([13ffee3](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/commit/13ffee3))


### Features

* **core:** updates and fixes ([53228c8](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/commit/53228c8))



<a name="1.0.2"></a>
## [1.0.2](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/compare/1.0.1...1.0.2) (2018-04-26)


### Bug Fixes

* **routes:** restrict route to ajax requests ([13ffee3](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/commit/13ffee3))



<a name="1.0.1"></a>
## [1.0.1](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/compare/1.0.0...1.0.1) (2018-04-26)


### Bug Fixes

* **ajax:** correctly pass unserialized values to views ([4fb65b7](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/commit/4fb65b7))



<a name="1.0.0"></a>
# 1.0.0 (2018-03-19)


### Features

* **releases:** initial commit ([aaa8156](https://github.com/hypeJunctionPro/Elgg3-hypeAjax/commit/aaa8156))



