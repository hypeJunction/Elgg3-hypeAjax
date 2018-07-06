hypeAjax
========

Utilities for AJAX requests

* Deferred view rendering


## Usage

### Deferred view rendering

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

### Ajax Forms

``ajax/Form`` AMD module can be used to chain promise-based submit callbacks and submit the form via Ajax.

```js
var Form = require('ajax/Form');
var form = new Form('.my-form');
var Ajax = require('elgg/Ajax');

form.onSubmit(function(resolve, reject) {
	// execute a long running script, e.g. validate fields via ajax
	var ajax = new Ajax();
	ajax.post('somewhere').done(resolve).fail(reject);
})

form.onSubmit(function(resolve, reject) {
	console.log('hello');
	resolve();
});


// By default, once all promises are resolved, the form will be submitted via ajax,
// and the user will be forwarded to the URL specified by the response
// You can however register custom success callbacks to prevent redirection

form.onSuccess(function(data) {
	console.log(data);
	
	require('elgg/lightbox', function(lightbox) {
		lightbox.close();
	});
	
	$('.my-list').refresh();
});

// You can also add your own error handler

form.onError(function(error) {
	console.log(error);
});

```
