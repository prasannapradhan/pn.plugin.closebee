function initJquery(){
	jQuery.expr[':'].contains = function(a, i, m) {
		return jQuery(a).text().toUpperCase()
		      .indexOf(m[3].toUpperCase()) >= 0;
	};					
}

function initDataTables(){
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $($.fn.dataTable.tables(true)).css('width','100%');
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
        $('div.dataTables_wrapper > div').css("width", "100%");
	}); 			
}

function closeModal(modalId, callback){
	$('#' + modalId).modal('hide');
	if(typeof callback !== "undefined"){
		callback();
	}
	return false;
}