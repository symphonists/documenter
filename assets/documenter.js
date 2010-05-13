jQuery(document).ready(function($) {

	var button = $('li.docs a');
	var docs = $('#docs');
	var text = docs.find('div');
	
	// Show documentation
	button.click(function(event) {
	
		event.preventDefault();
		var target = $(event.target);
		
		// Close documentation
		if(target.hasClass('expanded')) {
			docs.fadeOut('normal');
		}
		
		// Open documentation
		else {
			
			// Set documentation height
			$(window).resize();
			
			// Show documentation
			docs.fadeIn('normal');
		}
		
		// Save current state
		target.toggleClass('expanded');
		
	});
	
	// Adjust documentation height
	$(window).resize(function(event) {
		var height = $(document).height();
		
		// Set documentation height
		docs.css('max-height', height - 125);
		text.css('max-height', height - 250);
	});

});
