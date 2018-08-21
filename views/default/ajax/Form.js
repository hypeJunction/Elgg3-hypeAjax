define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var Ajax = require('elgg/Ajax');

	var Deferrable = function (selector) {

		var that = this;

		this.$el = $(selector);

		this.ajax = new Ajax();

		this.beforeCallbacks = [];
		this.successCallbacks = [];
		this.errorCallbacks = [];

		this.awaiting = false;

		this.onSubmit = function (callback) {
			that.beforeCallbacks.push(callback);
		};

		this.onSuccess = function (callback) {
			that.successCallbacks.push(callback);
		};

		this.onError = function (callback) {
			that.errorCallbacks.push(callback);
		};

		this.disable = function () {
			that.$el.find('[type="submit"]').prop('disabled', true);
		};

		this.enable = function () {
			that.$el.find('[type="submit"]').prop('disabled', false);
		};

		this.submit = function (e) {
			if (that.awaiting) {
				return;
			}

			e.preventDefault();

			var $form = that.$el;

			that.disable();
			that.awaiting = true;

			var deffereds = [];
			that.beforeCallbacks.map(function (callback) {
				var $d = $.Deferred();
				callback($d.resolve, $d.reject);
				deffereds.push($d.promise());
			});

			var $submitted = $.Deferred();
			$.when.apply($, deffereds)
				.done(function () {
					that.ajax
						.action($form.attr('action'), {
							data: that.ajax.objectify($form)
						})
						.done($submitted.resolve)
						.fail($submitted.reject);
				})
				.fail($submitted.reject);

			$.when($submitted)
				.done(function (data, statusText, xhr) {
					if (that.successCallbacks.length) {
						for (var i in that.successCallbacks) {
							that.successCallbacks[i].apply(that, [data, statusText, xhr]);
						}
					} else {
						$('body').trigger('click'); // hide all popups and lightboxes
						that.ajax.forward(xhr.AjaxData.forward_url || data.forward_url || elgg.normalize_url(''));
					}

					that.awaiting = false;
				})
				.fail(function (statusText, xhr) {
					if (that.errorCallbacks.length) {
						for (var i in that.errorCallbacks) {
							that.errorCallbacks[i].apply(that, [statusText, xhr]);
						}
					}

					that.awaiting = false;
					that.enable();
				});

			return false;
		};

		this.$el.off('submit').on('submit', this.submit);
	};

	return function (selector) {
		if (!$(selector).data('Deferrable')) {
			$(selector).data('Deferrable', new Deferrable(selector));
		}

		this.Deferrable = $(selector).data('Deferrable');

		this.onSubmit = this.Deferrable.onSubmit;
		this.onSuccess = this.Deferrable.onSuccess;
		this.onError = this.Deferrable.onError;
	};
});