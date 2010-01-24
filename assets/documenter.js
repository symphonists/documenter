jQuery(document).ready(function() {

	$ = jQuery;

	var origheight = $(document).height();
		
	$("#doc_link").bind("click", function(e){
		e.preventDefault();
		$("#docs").toggle("normal");
	});
	$("#doc_link").mouseup(function(){
		if($("#docs").height() > origheight - 160){
			$("form").css("height", $("#docs").height() + 160);
		}
		if($("form").height() > origheight){
			$("form").css("class", "adjusted");
		}
	});
});
