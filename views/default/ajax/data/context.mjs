import hooks from 'elgg/hooks';
import elgg from 'elgg';

// Elgg 7 exposes the client event bus as the default export of 'elgg/hooks'.
// The old named import { registerHandler } from 'elgg/events' resolved to a
// module that does not exist in the importmap, so the browser raised
// "Failed to resolve module specifier" on every page this plugin touched.
hooks.register('ajax_request_data', 'all', function (hook, type, params, data) {
	data.__context = elgg.data.context;

	return data;
});
