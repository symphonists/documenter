
(function($) {

	// Language strings
	Symphony.Language.add({
		'Need help?': false,
		'Close help': false
	});

	// Documenter
	$(document).ready(function() {
		var wrapper = $('#wrapper'),
			title = $('#documenter-title'),
			button = $('#header a.documenter.button'),
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
				}, 'fast', function() {
					
					// Switch label
					if(button.text() == Symphony.Language.get('Close help')) {
						button.text(Symphony.Language.get('Need help?'));
					}
				});
				
				// Store state
				wrapper.removeClass('documenter');
				button.removeClass('active');
				if(localStorage) {
					localStorage.removeItem('documenter-' + Symphony.Context.get('root'));
				}
			}
			
			// Show documentation
			else {
				docs.animate({
					width: 300,
					overflow: 'auto'
				}, 'fast', function() {
					
					// Switch label
					if(button.text() == Symphony.Language.get('Need help?')) {
						button.text(Symphony.Language.get('Close help'));
					}
				});	
				
				// Store state
				wrapper.addClass('documenter');
				button.addClass('active');
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
				button.addClass('active');
				
				// Switch label
				if(button.text() == Symphony.Language.get('Get help?')) {
					button.text(Symphony.Language.get('Close help'));
				}
			}
		}	
	});

})(jQuery.noConflict());	
