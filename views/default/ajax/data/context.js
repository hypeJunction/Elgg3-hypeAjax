require(['elgg'], function(elgg) {

	elgg.register_hook_handler('ajax_request_data', 'all', function(hook, type, params, data) {
		data.__context = elgg.data.context;
		return data;
	});

});
