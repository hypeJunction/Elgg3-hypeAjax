<?php

return [

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
