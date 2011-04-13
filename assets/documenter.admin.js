
(function($) {

	// Language strings
	Symphony.Language.add({
		'View Documentation': false,
		'Hide Documentation': false
	});

	// Documenter
	$(document).ready(function() {
		var wrapper = $('#wrapper'),
			title = $('#documenter-title'),
			button = $('a.documenter.button'),
			help = button.text(),
			docs = $('#documenter-drawer'),
			notice = $('#notice');
			
		// Check for system messages
		if(notice.is(':visible')) {
			docs.addClass('notice');
		}
		
		// Toggle documentation
		button.click(function(event) {
			
			// Hide documentation
			if(button.is('.active')) {
				docs.animate({
					width: 0,
					overflow: 'hidden'
				}, 'fast');
				
				// Store state
				wrapper.removeClass('documenter');
				button.text(help).attr('title', Symphony.Language.get('View Documentation')).removeClass('active');
				if(localStorage) {
					localStorage.removeItem('documenter-' + Symphony.Context.get('root'));
				}
			}
			
			// Show documentation
			else {
				docs.animate({
					width: 300,
					overflow: 'auto'
				}, 'fast');	
				
				// Store state
				wrapper.addClass('documenter');
				button.text('×').attr('title', Symphony.Language.get('Hide Documentation')).addClass('active');
				if(localStorage) {
					localStorage.setItem('documenter-' + Symphony.Context.get('root'), 'active');
				}
			}
		});
		
		// Restore documentation state
		if(localStorage) {
			if(localStorage.getItem('documenter-' + Symphony.Context.get('root')) == 'active') {
				docs.css({
					width: 300,
					overflow: 'auto'
				});
				
				// Store state
				wrapper.addClass('documenter');
				button.text('×').attr('title', Symphony.Language.get('Hide Documentation')).addClass('active');
			}
		}	
	});

})(jQuery.noConflict());	
