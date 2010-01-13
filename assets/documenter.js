jQuery(document).ready(function() {
	jQuery("#doc_link").bind('click', function(e){
		e.preventDefault();
		jQuery("#docs").toggle('normal');
	});
});