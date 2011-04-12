Symphony.Language.add({
	'View Documentation': false,
	'Hide Documentation': false
});

var Documenter = {
	
	init: function() {
		var button = jQuery('li.documenter-button a');
		var text = jQuery('li.documenter-button a').text();
		var docs = jQuery('#documenter-drawer');
		
		// Add close button
		jQuery('<a class="button">×</a>').attr('title', 'Hide Documentation').appendTo(jQuery('#documenter-title'));

		// Show documentation
		button.click(function(event) {
			
			event.preventDefault();
			var target = jQuery(event.target);
		
			// Close documentation
			if(target.hasClass('active')) {
				docs.children().hide();
				docs.animate({
					width: '0'
					}, 'fast');
				jQuery(this).text(text).attr('title','View Documentation');
			}
		
			// Open documentation
			else {
				docs.animate({
					width: '300px'
					}, 'fast');
				docs.children().show();
				jQuery(this).text('×').attr('title', 'Hide Documentation');
			}
		
			// Save current state
			target.toggleClass('active');
		
		});
	
		// When another JS event resizes the page, adjust docs height
		jQuery('body').resize(function(){
			var height = jQuery(this).height();
			docs.css('height',height);
		});
	}
}

jQuery(document).ready(function(){
	Documenter.init();
});
