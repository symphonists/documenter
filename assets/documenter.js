jQuery(document).ready(function() {

	$ = jQuery;
	
	$("#doc_link").bind('click', function(e){
		e.preventDefault();
		$("#docs").toggle('normal');
	});

});
