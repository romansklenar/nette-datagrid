/**
 * Common AJAX setup
 */

// ajax spinner
$(function () {
	$('<div id="ajax-spinner"></div>').hide().ajaxStart(function () {
		$(this).show();
	}).ajaxStop(function () {
		$(this).hide();
	}).appendTo("body");
});

// prolínací efekt při updatu snippetu
jQuery.extend({
	nette: {
		updateSnippet: function (id, html) {
			$("#" + id).fadeTo("fast", 0.3, function () {
				$(this).html(html).fadeTo("fast", 1);
				$.nette.registerAfterUpdate();
			});
		},
	
		registerAfterUpdate: function() { }
	}
});

// skrývání flash zpráviček
$("div.flash").livequery(function () {
	var el = $(this);
	setTimeout(function () {
		el.animate({"opacity": 0}, 2000);
		el.slideUp();
	}, 7000);
});



/**
 * Datagrid AJAX support
 */

// links
$("table.datagrid a.datagrid-ajax").live("click", function () {
	$.get(this.href);
	return false;
});

//form buttons
$("form.datagrid :submit").live("click", function () {
	 $(this).ajaxSubmit();
	return false;
});

// form submit
$("form.datagrid").livequery("submit", function () {
	$(this).ajaxSubmit();
	return false;
});



/**
 * Datagrid JS support
 */

// obarvování zaškrtnutého řádku
function datagridCheckboxClicked() {
	var tr = $(this).parent().parent();
	if ($(this).is(":checked")) tr.addClass("selected");
	else tr.removeClass("selected");
}

// při označení / odznačení a načtení
$("table.datagrid td.checker input:checkbox").livequery(datagridCheckboxClicked)
	.live("click", datagridCheckboxClicked);

// zaškrtávání celým řádkem
var previous = null; // index from
$("table.datagrid tr td:not(.checker)").live("click", function (e) {
	// jen kliknutí levým tlačítkem
	if (e.button != 0) return true;
	  
	var row = $(this).parent("tr");
	
	// výběr více řádků při držení klavesy SHIFT nebo CTRL
	if ((e.shiftKey || e.ctrlKey) && previous) {
		var current = $(this).parents("table.datagrid").find("tr").index($(this).parent("tr")); // index to
		if (previous > current) {
			var tmp = current;
			current = previous;
			previous = tmp;
		}
		current++;
		row = $(this).parents("table.datagrid").find("tr").slice(previous, current);
		
	} else {
		previous = $(this).parents("table.datagrid").find("tr").index($(this).parent("tr"));
	}
	
	// zvýraznění řádku(ů)
	if ($(this).parent().hasClass("selected")) {
		row.removeClass("selected");
		row.find("td.checker input:checkbox").removeAttr("checked");
		
	} else {
		if (row.find("td.checker input:checkbox").is(":checkbox")) {
			row.addClass("selected");
			row.find("td.checker input:checkbox").attr("checked", "checked");
		}
	}
});

// invertor
$("table.datagrid tr.header th.checker").livequery(function () {
	$(this).append($('<span class="icon icon-invert" title="Invert" style="margin: 0pt auto; float: none;" />').click(function () {
		// NOTE: příliš pomalé v Opeře
		//$(this).parents("table.datagrid").find("td.checker input:checkbox").click();
		
		var table = $(this).parents("table.datagrid");
		var selected = table.find("tr.selected");
		var unselected = table.find("tr").filter(":not(.selected)");
		
		selected.removeClass("selected");
		selected.find("td.checker input:checkbox").removeAttr("checked");
		unselected.addClass("selected");
		unselected.find("td.checker input:checkbox").attr("checked", "checked");
	}));
});

// datepicker
$("input.datepicker:not([readonly])").livequery(function () {
	$(this).datepicker();
});

// ajaxové filtrování formulářů datagridů po stisknutí klávesy <ENTER>
$("form.datagrid table.datagrid tr.filters input[type=text]").livequery("keypress", function (e) {
	if (e.keyCode == 13) {
		$(this).parents("form.datagrid").find("input:submit[name=filterSubmit]").ajaxSubmit();
		return false;
	}
});

// ajaxové filtrování formulářů datagridů pomocí změny hodnoty selectboxu nebo checkboxu
$("form.datagrid table.datagrid").find("tr.filters input:checkbox, tr.filters select").livequery("change", function (e) {
	$(this).parents("form.datagrid").find("input:submit[name=filterSubmit]").ajaxSubmit();
	return false;
});

// ajaxová změna stránky formuláře datagridů po stisknutí klávesy <ENTER>
$("form.datagrid table.datagrid tr.footer input[name=pageSubmit]").livequery(function () {
	$(this).hide();
});
$("form.datagrid table.datagrid tr.footer input[name=page]").livequery("keypress", function (e) {
	if (e.keyCode == 13) {
		$(this).parents("form.datagrid").find("input:submit[name=pageSubmit]").ajaxSubmit();
		return false;
	}
});

//ajaxová změna počtu řádků na stránku datagridů pomocí změny hodnoty selectboxu
$("form.datagrid table.datagrid tr.footer input[name=itemsSubmit]").livequery(function () {
	$(this).hide();
});
$("form.datagrid table.datagrid tr.footer select[name=items]").livequery("change", function (e) {
	$(this).parents("form.datagrid").find("input:submit[name=itemsSubmit]").ajaxSubmit();
});