jQuery.extend({
	//grafický efekt u překreslení všech snippetů
	updateSnippet: function (id, html) {
		$("#" + id).animate({ opacity: 0.5 }, "fast", "swing", function () {
			$(this).html(html).animate({ opacity: 1 }, "fast", "swing");
			
			jQuery.registerAfterUpdate();
		});
	},
	
	// akce, které je nutno provést i po každém překreslení snippetů
	registerAfterUpdate: function() {
		// skryj efektivně všechny flash zprávičky po pěti sekundách
		$(".flash").fadeTo(5000, 0.9, function () {
			$(this).animate({"opacity": 0}, 2000);
			$(this).slideUp(1000);
		});
		
		// schovat odesílací tlačítka formulářů
		$("form.ajaxform :submit").hide();
		
		// připoj datepicker na inputy
		$("input.datepicker").datepicker({duration: 'fast'});
		
		// zruš funkčnost datepickeru nad readonly inputy
		$("input[readonly].datepicker").datepicker('destroy');
		
		
		/******** DataGrid ******/

		// pokud se mají vybrané řádky datagridu znovu označit třídou selected po překreslení snippetu
		$("table.grid tr td.checker input:checked").parent().parent().addClass("selected");		
		// nebo pokud se mají vybrané řádky datagridu odznačit po překreslení snippetu
		//$("table.grid tr td.checker input:checked").removeAttr("checked");
		
		// vložit a zobrazit ikonku invertoru výběru (pro každý datagrid jen jedna ikonka)
		$('<span class="icon icon-invert" title="Invertovat výběr" />').appendTo('table.grid tr.header th.checker');
		$('table.grid').find('tr.header th.checker span.icon-invert:not(:first)').remove();
		
		// přesunu odesílací tlačítko tak, aby bylo první ve formuláři, aby se na formulář po odeslání ENTEREM aplikovaly filtry
		// (je nutné, jen pokud filtrační tlačítko není první tlačítko formuláře)
		$("form.gridform table.grid tr.filters td.actions input:submit[name=filterSubmit]").prependTo("form.gridform").hide();
		
		// a na jeho místo vložím odesílací ikonku / odkaz nahrazující jeho úlohu (pro každý datagrid jen jednu)
		$('<a href="#" class="filter" title="Filtrovat">Filtrovat</a>').click(function () {
			$(this).parents("form.gridform").find("input:submit[name=filterSubmit]").netteAjaxSubmit();
			return false;
		}).appendTo("form.gridform table.grid tr.filters td.actions");
		$('form.gridform table.grid').find('tr.filters td.actions a.filter:not(:first)').remove();
	}
});




$(function () {
	$.registerAfterUpdate();

	// přiřaď všem současným i budoucím odkazům s třídou ajaxlink po kliknutí tuto funkci
	$("a.ajaxlink").live("click", function () {
		$.netteAjax(this.href);
		return false;
	});
	
	// ajaxové odeslání na všech současných i budoucích formulářích
	$("form.ajaxform").live('submit', function() {
		$(this).netteAjaxSubmit();
		return false;
	});
	
	// ajaxové odeslání pomocí tlačítka na všech současných i budoucích formulářích
	$("form.ajaxform :submit").live('click', function(e) {
		$(this).netteAjaxSubmit();
		return false;
	});
	
	// ajaxové odeslání všech současných i budoucích formulářů i pomocí změny selectboxu
	$("form.ajaxform select").live('change', function(e) {
		$(this.form).netteAjaxSubmit();
		return false;
	});
	
	// ajax-loader
	$('<div id="ajax-spinner"></div>').ajaxStart(function () {
		$(this).show();
	}).ajaxStop(function () {
		$(this).hide();
	}).appendTo("body").hide();
	
	
	/******** DataGrid ******/
	
	// ajaxové odeslání pomocí tlačítka na všech současných i budoucích formulářích datagridů
	$("form.gridform").live('submit', function () {
		$(this).netteAjaxSubmit();
		return false;
	});
	
	// ajaxové odeslání pomocí tlačítka na všech současných i budoucích formulářích datagridů
	$("form.gridform :submit").live('click', function() {
    	$(this).netteAjaxSubmit();
    	return false;
	});
	
	// ajaxové odeslání formulářích datagridů pomocí změny hodnoty selectboxu nebo checkboxu (aplikace filtrů)
	$("form.gridform table.grid tr.filters select, form.gridform table.grid tr.filters input[type=checkbox]").live('change', function() {
		$(this).parents("form.gridform").find("input:submit[name=filterSubmit]").netteAjaxSubmit();
		return false;
	});
	
	// zvýraznění řádku tabulky gridu včetně zatržení checkboxu gridu na kliknutí a zrušení na druhé kliknutí
	var previous = null; // index from
	$("table.grid tr td:not(table.grid tr td.checker input:checkbox, table.grid tr.filters td)").live('click', function(e) {
		var row = $(this).parent();
		
		// výběr více řádků při držení klavesy SHIFT nebo CTRL
		if ((e.shiftKey || e.ctrlKey) && previous) {			
			var current = $(this).parents("table.grid").find('tr').index($(this).parents('tr')); // index to
			if (previous > current) {
				var tmp = current;
				current = previous; previous = tmp;
			}
			current++;
			row = $(this).parents("table.grid").find('tr').slice(previous, current);
			
		} else {
			previous = $(this).parents("table.grid").find('tr').index($(this).parents('tr'));
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
	
	// invertor vybraných řádků v gridu
	$("table.grid tr.header th.checker span.icon-invert").live('click', function () {
		var table = $(this).parents("table.grid");
		var selected = table.find("tr.selected");
		var unselected = table.find("tr").filter(":not(.selected)");
		
		selected.removeClass("selected");
		selected.find("td.checker input:checkbox").removeAttr("checked");
		unselected.addClass("selected");
		unselected.find("td.checker input:checkbox").attr("checked", "checked");
	});

});