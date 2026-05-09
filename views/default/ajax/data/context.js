import { registerHandler } from 'elgg/events';
import elgg from 'elgg';

registerHandler('ajax_request_data', 'all', function(hook, type, params, data) {
	data.__context = elgg.data.context;
	return data;
});
