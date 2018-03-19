<?php

$view = elgg_extract('view', $vars);
if (!$view || !elgg_view_exists($view)) {
	return;
}

$placeholder = elgg_extract('placeholder', $vars);
if (!$placeholder && $placeholder !== false) {
	$placeholder = elgg_format_element('div', [
		'class' => 'elgg-ajax-loader',
	]);
}

$filter = function($e) use (&$filter) {
	if ($e instanceof Serializable) {
		$e = serialize($e);
	} else if ($e instanceof ElggData) {
		$e = serialize(new \hypeJunction\Ajax\PayloadItem($e));
	} else if (is_array($e)) {
		$e = array_map($filter, $e);
	}

	return $e;
};

$payload = (array) elgg_extract('payload', $vars, []);
$payload = array_filter($payload, function($e) {
	return !is_null($e);
});
$payload = array_map($filter, $payload);

$href = elgg_http_add_url_query_elements("_deferred/$view", [
	'payload' => $payload,
	'ct' => \hypeJunction\Ajax\Context::capture(),
]);

echo elgg_format_element('div', [
	'class' => 'ajax-placeholder',
	'data-src' => $href,
], $placeholder ? : '');

?>
<script>
	require(['ajax/placeholder'], function(ph) {
		ph.init();
	});
</script>
