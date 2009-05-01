jQuery.fn.extend({
	netteAjaxSubmit: function (callback) {
		var form;
		var sendValues = {};

		// odesláno na tlačítku
		if (this.is(":submit")) {
			form = this.parents("form");
			sendValues[this.attr("name")] = this.val() || "";

		// odesláno na formuláři
		} else if (this.is("form")) {
			form = this;

		// neplatný element, nic nedělat
		} else {
			return null;
		}

		// validace
		if (form.get(0).onsubmit && !form.get(0).onsubmit()) return;

		var values = form.serializeArray();

		for (var i = 0; i < values.length; i++) {
			//var newValue = {};
			var name = values[i].name;

			// multi
			if (name in sendValues) {
				var val = sendValues[name];

				if (!(val instanceof Array)) {
					val = [val];
				}

				val.push(values[i].value);
				sendValues[name] = val;
			} else {
				sendValues[name] = values[i].value;
			}
		}

		// odeslat ajaxový požadavek
		return jQuery.netteAjax(
			form.attr("action"),
			sendValues,
			callback || null,
			form.attr("method") || "get"
		);
	}
});