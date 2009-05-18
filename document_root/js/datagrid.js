/**
 * Ajax
 */

// ajax spinner
$(function () {
	// přidám spinner do stránky
	$('<div id="ajax-spinner"></div>').hide().ajaxStart(function () {
		$(this).show();
	}).ajaxStop(function () {
		// nastavení původních vlastností, třeba kvůli odesílání formuláře
		$(this).hide().css({
			position: "fixed",
			left: "50%",
			top: "50%"
		});
	}).appendTo("body");
});

// prolínací efekt při updatu snippetu
jQuery.extend({
	updateSnippet: function (id, html) {
		$("#" + id).fadeOut("fast", function () {
			$(this).html(html).fadeIn("fast");
		});
	}
});

// links
$("a.ajax").live("click", function (event) {
	$.get(this.href);

	// spinner position
	$("#ajax-spinner").css({
		position: "absolute",
		left: event.pageX + 20,
		top: event.pageY + 40
	});

	return false;
});

// form buttons
$("form.ajax :submit, :submit.ajax, form.gridform :submit").live("click", function (event) {
	 $(this).ajaxSubmit();

	// spinner position
	if (event.pageX && event.pageY) {
		$("#ajax-spinner").css({
			position: "absolute",
			left: event.pageX + 20,
			top: event.pageY + 40
		});
	}

	return false;
});

// forms
$("form.ajax, form.gridForm").livequery("submit", function () {
	$(this).ajaxSubmit();
	return false;
});

/**
 * Flash messages
 * zmizení za 5 s
 */
$("div.flash").livequery(function () {
	var el = $(this);
	setTimeout(function () {
		el.slideUp();
	}, 5000);
});

/**
 * Datagrid
 */

/**
 * Výběr
 */

// obarvování zaškrtnutého řádku
function datagridColorRow() {
	var tr = $(this).parent().parent();

	if ($(this).is(":checked")) {
		tr.addClass("selected");
	} else {
		tr.removeClass("selected");
	}
}
// při označení a odznačení
$("table.grid td.checker input:checkbox").live("click", datagridColorRow);
// při načtení
$("table.grid td.checker input:checkbox").livequery(datagridColorRow);

// zaškrtávání celým řádkem
$("table.grid td:not(.checker)").live("click", function () {
	$(this).parent().find("td.checker input:checkbox").click();
});

// invertor
$("table.grid tr.header th.checker").livequery(function () {
	$(this).append($('<span class="icon icon-invert" title="Invert" />').click(function () {
		$(this).parents("table.grid").find("td.checker input:checkbox").click();
	}));
});

/**
 * Filtry
 */

// datepicker
$("input.datepicker:not([readonly])").livequery(function () {
	$(this).datepicker();
});

// ajaxové filtrování formulářů datagridů po stisknutí klávesy <ENTER>
$("form.gridform table.grid tr.filters input[type=text]").livequery("keypress", function (e) {
	if (e.keyCode == 13) {
		$(this).parents("form.gridform").find("input:submit[name=filterSubmit]").ajaxSubmit();
		return false;
	}
});

// ajaxové filtrování formulářů datagridů pomocí změny hodnoty selectboxu nebo checkboxu
$("form.gridform table.grid tr.filters").find("select, input:checkbox").livequery("change", function (e) {
	$(this).parents("form.gridform").find("input:submit[name=filterSubmit]").ajaxSubmit();
	return false;
});

// ajaxová změna stránky formuláře datagridů po stisknutí klávesy <ENTER>
$("form.gridform table.grid tr.footer input[name=pageSubmit]").livequery(function () {
	$(this).hide();
});
$("form.gridform table.grid tr.footer input[name=page]").livequery("keypress", function (e) {
	if (e.keyCode == 13) {
		$(this).parents("form.gridform").find("input:submit[name=pageSubmit]").ajaxSubmit();
		return false;
	}
});