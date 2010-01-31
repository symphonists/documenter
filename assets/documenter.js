jQuery(document).ready(function() {

	$ = jQuery;

	var origheight = $(document).height();
		
	$(".closed").live("click", function(e){
		e.preventDefault();
		$(this).removeClass("closed").addClass("expanded");
		$("#docs").show("normal");
	}).mouseup(function(e){
		$("form").css("height", $("#docs").height() + 160);
	});
	$(".expanded").live("click", function(e){
		e.preventDefault();
		$(this).removeClass("expanded").addClass("closed");
		$("#docs").hide("normal");
		$("form").css("height", origheight);
	});
});
