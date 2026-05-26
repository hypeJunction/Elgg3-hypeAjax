<?php

namespace hypeJunction\Ajax;

use Elgg\Hook;

/**
 * Replace a deferred view's output with a placeholder that the client
 * resolves asynchronously via the /ajax/view endpoint.
 */
class DeferViewRendering {

	/**
	 * Defer view rendering by outputing a placeholder
	 *
	 * @elgg_plugin_hook view_vars all
	 *
	 * @param Hook $hook Hook
	 *
	 * @return array
	 */
	public function __invoke(Hook $hook) {

		$vars = $hook->getValue();

		if (empty($vars['deferred'])) {
			return;
		}

		unset($vars['deferred']);

		$placeholder = \elgg_extract('placeholder', $vars);
		unset($vars['placeholder']);

		$vars['__view_output'] = \elgg_view('ajax/placeholder', [
			'view' => $hook->getType(),
			'payload' => $vars,
			'placeholder' => $placeholder,
		]);

		return $vars;
	}
}
