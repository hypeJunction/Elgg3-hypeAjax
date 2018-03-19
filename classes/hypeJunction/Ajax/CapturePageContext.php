<?php

namespace hypeJunction\Ajax;

use Elgg\Hook;

class CapturePageContext {

	/**
	 * Store page-related information in the client-site data
	 * This information will be used to restore context
	 * and validate request signature, when /data endpoints are
	 * accessed
	 *
	 * @elgg_plugin_hook elgg.data page
	 *
	 * @param Hook $hook Hook
	 *
	 * @return array
	 */
	public function __invoke(Hook $hook) {
		$return = $hook->getValue();
		$return['context'] = Context::capture();
		return $return;
	}
}
