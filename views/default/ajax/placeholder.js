define(function (require) {

	var Ajax = require('elgg/Ajax');

	return {
		init: function () {
			$('.ajax-placeholder').each(function () {
				var $elem = $(this);
				var ajax = new Ajax(false);
				var data = $elem.data('src');
				ajax.path(data).done(function (output) {
					$elem.replaceWith($(output));
				}).fail(function () {
					$elem.replaceWith($('<div />'));
				});
			});
		}
	};
});
