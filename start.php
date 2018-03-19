<?php

use hypeJunction\Ajax\CapturePageContext;
use hypeJunction\Ajax\DeferViewRendering;

require_once __DIR__ . '/autoloader.php';

return function () {
	elgg_register_event_handler('init', 'system', function () {

		elgg_extend_view('elgg.js', 'ajax/data/context.js');

		elgg_register_plugin_hook_handler('elgg.data', 'page', CapturePageContext::class);

		elgg_register_plugin_hook_handler('view_vars', 'all', DeferViewRendering::class);

	});
};
