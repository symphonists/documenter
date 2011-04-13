
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
			drawer = $('#documenter-drawer');
								
	/*---- Events -----------------------------------------------------------*/
		
		// Toggle documentation
		button.click(function(event) {
			
			// Hide documentation
			if(button.is('.active')) {
				hide('fast');
			}
			
			// Show documentation
			else {
				show('fast');
			}
		});
						
		// Detect live notices
		$('#header').bind('DOMSubtreeModified', function() {
			notice();
		});
		$(window).bind('resize', function() {
			notice();
		});
		
	/*---- Functions --------------------------------------------------------*/
		
		// Show documentation
		var show = function(speed) {
			button.fadeOut('fast');
			
			// Open drawer
			drawer.animate({
				width: 300,
				overflow: 'auto'
			}, speed, function() {
				wrapper.addClass('documenter');
				button.text('×').attr('title', Symphony.Language.get('Hide Documentation')).addClass('active').fadeIn('fast');			
			});	
			
			// Store state
			if(localStorage) {
				localStorage.setItem('documenter-' + Symphony.Context.get('root'), 'active');
			}
		};
		
		// Hide documentation
		var hide = function(speed) {
			notice();
		
			// Close drawer
			drawer.animate({
				width: 0,
				overflow: 'hidden'
			}, 'fast', function() {
				wrapper.removeClass('documenter');
				button.text(help).attr('title', Symphony.Language.get('View Documentation')).removeClass('active');
			});
			
			// Store state
			if(localStorage) {
				localStorage.removeItem('documenter-' + Symphony.Context.get('root'));
			}
		};
		
		// Check notices
		var notice = function() {
			var message = $('#notice'),
				height = message.outerHeight();
				
			// System messages active
			if($('#notice').is(':visible')) {
				button.css('top', 16 + height);
				drawer.css('padding-top', height);
			}
			
			// System messages inactive
			else {
				button.css('top', 16);
				drawer.css('padding-top', 0);
			}
		}
			
	/*---- Initialisation ---------------------------------------------------*/
		
		// Check for existing system messages
		notice();
		
		// Restore documenter state
		if(localStorage) {
			if(localStorage.getItem('documenter-' + Symphony.Context.get('root')) == 'active') {
				show(0);
			}
		}
		
	});

})(jQuery.noConflict());	
