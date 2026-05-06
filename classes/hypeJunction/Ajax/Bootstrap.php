<?php

namespace hypeJunction\Ajax;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap — wires hooks/handlers for hypeAjax.
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		elgg_extend_view('elgg.js', 'ajax/data/context.js');

		elgg_register_plugin_hook_handler('elgg.data', 'page', CapturePageContext::class);

		elgg_register_plugin_hook_handler('view_vars', 'all', DeferViewRendering::class);
	}
}
