<?php

return [

	'plugin' => [
		'id' => 'hypeajax',
		'name' => 'hypeAjax',
		'version' => '6.0.0',
		'description' => 'Ajax utilities for Elgg plugins.',
		'author' => 'Ismayil Khayredinov',
		'category' => 'utility',
	],

	'bootstrap' => \hypeJunction\Ajax\Bootstrap::class,

	'routes' => [
		'ajax:deferred' => [
			'path' => '/_deferred/{view}',
			'controller' => \hypeJunction\Ajax\DeferredViewController::class,
			'requirements' => [
				'view' => '.+',
			],
			'middleware' => [
				\Elgg\Router\Middleware\AjaxGatekeeper::class,
			]
		],
	],
];
