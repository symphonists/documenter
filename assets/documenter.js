jQuery(document).ready(function() {

	$ = jQuery;

	var origheight = $(document).height();
		
	$(".docs-closed").live("click", function(e){
		e.preventDefault();
		$(this).removeClass("docs-closed").addClass("docs-expanded");
		$("#docs").show("normal");
	}).mouseup(function(e){
		$("form").css("height", $("#docs").height() + 160);
	});
	$(".docs-expanded").live("click", function(e){
		e.preventDefault();
		$(this).removeClass("docs-expanded").addClass("docs-closed");
		$("#docs").hide("normal");
		$("form").css("height", origheight);
	});
});
