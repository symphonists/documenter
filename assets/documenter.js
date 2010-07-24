jQuery(document).ready(function($) {

	var button = $('li.docs a');
	var text = $('li.docs a').text();
	var docs = $('#docs');
	
	// Show documentation
	button.click(function(event) {
	
		event.preventDefault();
		var target = $(event.target);
		
		// Close documentation
		if(target.hasClass('active')) {
			docs.children().hide();
			docs.animate({
				width: '0'
				}, 'fast');
			$(this).text(text).attr('title','View Documentation');
		}
		
		// Open documentation
		else {
			docs.animate({
				width: '300px'
				}, 'fast');
			docs.children().show();
			$(this).text('X').attr('title', 'Hide Documentation');
		}
		
		// Save current state
		target.toggleClass('active');
		
	});
	
	// When another JS event resizes the page, adjust docs height
	$('form').resize(function(){
		var height = $(this).height();
		docs.css('height',height);
	});

});
