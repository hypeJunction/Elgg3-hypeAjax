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
	public function init(): void {
		elgg_import_esm('ajax/data/context');

		elgg_register_event_handler('elgg.data', 'page', CapturePageContext::class);

		elgg_register_event_handler('view_vars', 'all', DeferViewRendering::class);
	}
}
