# hypeAjax — Architecture (Elgg 4.x)

## Summary

hypeAjax provides deferred (lazy) view rendering via AJAX. A view is rendered
server-side on first request, but heavy views can be deferred by rendering a
lightweight placeholder that the browser's `ajax/placeholder` AMD module later
replaces with the full view output. The deferred request carries a signed
context token (HMAC) and an encoded payload so the server can re-establish the
original page context.

## Directory Structure

```
hypeajax/
├── classes/hypeJunction/Ajax/
│   ├── Bootstrap.php              — Plugin bootstrap (extends DefaultPluginBootstrap)
│   ├── Context.php                — HMAC-signed page-context capture and restore
│   ├── DeferredViewController.php — Route controller for /_deferred/{view}
│   └── PayloadItem.php            — JSON encode/decode helper for ElggData payloads
├── tests/
│   ├── phpunit/integration/…/BootstrapTest.php
│   ├── bootstrap.php
│   └── phpunit.xml
├── views/default/ajax/
│   ├── Form.js                    — Promise-based AJAX form submission helper
│   ├── context.js                 — AMD module: page-context utilities
│   ├── placeholder.js             — AMD module: deferred view loader
│   └── placeholder.php            — View: renders the deferred placeholder element
├── composer.json
└── elgg-plugin.php
```

## Registered Hooks/Events (Bootstrap::init)

| Type | Name | Handler |
|------|------|---------|
| Hook | `elgg.data:page` | Injects page context token into `elgg.data` |
| Hook | `view_vars:all` | Injects deferred-view utilities into every view's `$vars` |

The `view_vars:all` hook adds `PayloadItem` availability and deferred-render
helpers so any plugin view can opt into lazy rendering without depending
directly on hypeAjax classes.

## Routes

| Name | Path | Controller | Middleware |
|------|------|------------|------------|
| `ajax:deferred` | `/_deferred/{view}` | `DeferredViewController` | `AjaxGatekeeper` |

The route requires an AJAX request (`AjaxGatekeeper`). It restores context
via the signed `ct` parameter before rendering the requested `{view}`.

## Context Security Model

`Context::capture()` serialises the current page state (user GUID, page-owner
GUID, context stack, input params, viewtype, timestamp) and signs it with
`elgg_build_hmac()`. The token is embedded in placeholder elements as the `ct`
query parameter.

`Context::restore()` re-validates the HMAC on the incoming request and rejects
any request whose token doesn't match — preventing replay and tampering.

## Payload Transport

`PayloadItem::encode()` JSON-encodes an `ElggData` item (storing only id, type,
subtype) or any scalar/array value. `PayloadItem::decode()` re-hydrates the
item using `get_entity()` / `elgg_get_annotation_from_id()` etc. Payloads flow
through URL query parameters (never stored in the database) so no upgrade batch
is required.

## Dependencies

None — hypeAjax is a leaf plugin with no sibling plugin dependencies.

## Migration Notes (3.x → 4.x)

- `start.php` was removed in the previous migration step; Bootstrap class
  was already in place.
- `manifest.xml` removed; `composer.json` is now the sole metadata source.
- `elgg-plugin.php` received the `'plugin'` key.
- `Elgg\BadRequestException` renamed to `Elgg\Exceptions\Http\BadRequestException`.
- `PayloadItem` refactored from PHP `\Serializable` to static `encode()`/
  `decode()` helpers (JSON). The `\Serializable` interface is deprecated in
  PHP 8.1; the refactor is forward-compatible and eliminates `unserialize()`
  from the security-sweep scope.
- `composer/installers` bumped `~1.0` → `^2.0`; `psr-0` autoload replaced
  with `psr-4`.
