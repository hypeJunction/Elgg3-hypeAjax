hypeAjax
========

Utilities for AJAX requests

* Deferred view rendering


## Usage

To defer view rendering, simply add `'deferred' => true` to view vars.

```php
echo elgg_view('my_view', [
	// tells the view system to defer view render
	'deferred' => true,
	
	// if set to false, placeholder will not be rendered
	// if set to a value, that value will be used as the placeholder
	// if not set, default ajax loader will be used
	'placeholder' => false,
	
	// you can pass other view vars, as you would with normal views
	// various Elgg data will be serialized and available to the deferred view
	// some of the values may need to be wrapped into a Serializable instance
	'entity' => get_entity(123),
	'user' => get_user(234),
]);
```
