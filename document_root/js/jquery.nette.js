jQuery.extend({
	updateSnippet: function (id, html) {
		$("#" + id).html(html);
	},


	ajaxCallback: function (callback) {
		return function (data) {
			// redirect
			if (data.redirect) {
				window.location.href = data.redirect;
			}

			// snipety
			if (data.snippets) {
				for (var i in data.snippets) {
					jQuery.updateSnippet(i, data.snippets[i]);
				}
			}

			// callback
			if (callback) {
				callback(data);
			}
			
		};
	},


	netteAjax: function () {
		var args = jQuery.makeArray(arguments);

		var url = args.shift();
		var type = "get";
		var params = null;
		var callback = null;

		// argumenty funkce
		for (var i = 0; i < args.length; i++) {
			// nastavit callback
			if (jQuery.isFunction(args[i])) {
				callback = args[i];
				continue;
			}
			// nastavit typ
			if (typeof args[i] == "string") {
				if (args[i].toLowerCase() == "post") {
					type = "post";
				}
				continue;
			}
			// nastavit parametry
			if (args[i] instanceof Object) {
				params = args[i];
			}
		}

		return jQuery.ajax({
			type: type,
			url: url,
			data: params,
			success: jQuery.ajaxCallback(callback),
			dataType: "json",
			cache: false
		});
	}
});