<?php

namespace hypeJunction\Ajax;

use Elgg\BadRequestException;
use Elgg\Http\ResponseFactory;
use Elgg\Request;

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
			$unserialized = @unserialize($value);
			if ($unserialized !== false) {
				$payload[$key] = $unserialized;
			}
		}

		$output = elgg_view($view, $payload);

		return elgg_ok_response($output);
	}
}