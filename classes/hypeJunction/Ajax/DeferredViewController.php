<?php

namespace hypeJunction\Ajax;

use Elgg\Exceptions\Http\BadRequestException;
use Elgg\Http\ResponseFactory;
use Elgg\Request;

/**
 * Render a deferred view server-side after restoring the original page
 * context, decoding any payload items, and returning the HTML to the
 * client.
 */
class DeferredViewController {

	/**
	 * Deferred view controller
	 *
	 * @param Request $request Request
	 *
	 * @return ResponseFactory
	 * @throws BadRequestException
	 */
	public function __invoke(Request $request) {

		Context::restore($request, 'ct');

		$view = $request->getParam('view');
		$payload = $request->getParam('payload');

		foreach ($payload as $key => $value) {
			if (is_string($value) && str_starts_with($value, '{')) {
				$decoded = \hypeJunction\Ajax\PayloadItem::decode($value);
				if ($decoded !== null) {
					$payload[$key] = $decoded;
				}
			}
		}

		$output = \elgg_view($view, $payload);

		return \elgg_ok_response($output);
	}
}
