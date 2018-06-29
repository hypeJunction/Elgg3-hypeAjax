<?php

namespace hypeJunction\Ajax;

use Elgg\BadRequestException;
use Elgg\Request;

class Context {

	/**
	 * Get context data
	 * @return array
	 */
	public static function capture() {

		$logged_in_user_guid = elgg_get_logged_in_user_guid();
		$page_owner_guid = elgg_get_page_owner_guid();

		$contexts = elgg_get_context_stack();
		$input = _elgg_services()->request->getParams();
		$input = array_filter($input, function ($e) {
			return !is_null($e);
		});
		$viewtype = elgg_get_viewtype();
		$ts = time();

		$data = serialize([$logged_in_user_guid, $page_owner_guid, $contexts, $input, $viewtype, $ts]);
		$mac = elgg_build_hmac($data)->getToken();

		return [
			'user' => $logged_in_user_guid,
			'page_owner' => $page_owner_guid,
			'context_stack' => $contexts,
			'input' => $input,
			'viewtype' => $viewtype,
			'ts' => $ts,
			'mac' => $mac,
		];
	}

	/**
	 * Prevent unsigned requests to data endpoints
	 *
	 * @param Request $request Request
	 * @param string  $name    Name of the query element that contains context info
	 *
	 * @return bool
	 * @throws BadRequestException
	 *
	 * @todo Expire requests after x seconds
	 */
	public static function restore(Request $request, $name = '__context') {

		$logged_in_user_guid = $request->elgg()->session->getLoggedInUserGuid();

		$context = (array) $request->getParam($name, []);

		$page_owner_guid = (int) elgg_extract('page_owner', $context);
		$contexts = (array) elgg_extract('context_stack', $context);
		$input = (array) elgg_extract('input', $context, []);
		$viewtype = elgg_extract('viewtype', $context);
		$ts = (int) elgg_extract('ts', $context);
		$signature = elgg_extract('mac', $context);

		$data = serialize([$logged_in_user_guid, $page_owner_guid, $contexts, $input, $viewtype, $ts]);
		$mac = elgg_build_hmac($data);

		if (!$mac->matchesToken($signature)) {
			throw new BadRequestException("Request signature is invalid");
		}

		elgg_set_context_stack($contexts);
		elgg_set_page_owner_guid($page_owner_guid);

		foreach ($input as $key => $value) {
			if (null === $request->getParam($key)) {
				$request->setParam($key, $value);
			}
		}

		return true;
	}

}