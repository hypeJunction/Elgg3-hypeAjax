<?php

namespace hypeJunction\Ajax;

use Elgg\Event;

/**
 * Capture page-related context info on every page render so the client
 * can echo it back when calling /data endpoints (used to verify request
 * signatures and rebuild server-side context).
 */
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
	public function __invoke(Event $hook) {
		$return = $hook->getValue();
		$return['context'] = Context::capture();
		return $return;
	}
}
