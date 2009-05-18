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
	var inverter = $('<span class="icon icon-invert" title="Invert" />');
	
	$(this).append(inverter);
});

$("table.grid tr.header th.checker span.icon-invert").live('click', function() {
	$(this).parents("table.grid").find("td.checker input:checkbox").click();
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




