
(function($) {

	// Language strings
	Symphony.Language.add({
		'Show': false,
		'Hide': false
	});
	
	// Documenter
	var documenter = {
		
		drawer: null,
		storage: 'symphony.drawer.drawer-documenter.blocks',
	
		init: function() {
			documenter.drawer = $('#documenter-drawer');
			
			// Prepare content toggling
			documenter.drawer.find('h3').each(documenter.buildBlock);
			
			// Toggle content blocks
			documenter.drawer.on('click.documenter', 'h3', documenter.toggle);
		},
		
		buildBlock: function() {
			var headline = $(this),
				blocks = [];

			// Load storage
			if(Symphony.Support.localStorage) {
				blocks = $.parseJSON(localStorage.getItem(documenter.storage));
			}
			
			// Set strings
			headline.attr('data-show', Symphony.Language.get('Show'));
			headline.attr('data-hide', Symphony.Language.get('Hide'));

			// Wrap content blocks
			headline.nextUntil('h3, div.note').wrapAll('<div class="block" />');
			
			// Restore state
			if($.inArray(headline.text(), blocks) >= 0) {
				headline.addClass('open');
				headline.next('.block').addClass('open').show();
			}
			else {
				headline.next('.block').hide();
			}
		},
		
		toggle: function(event) {
			var headline = $(this),
				blocks = [];

			// Toggle content block
			headline.toggleClass('open');
			headline.next('div.block').toggleClass('open').slideToggle('fast');
			
			// Store open content blocks
			documenter.drawer.find('h3.open').each(function() {
				var text = $(this).text();
				blocks.push(text);
			});
			
			if(Symphony.Support.localStorage) {
				localStorage.setItem(documenter.storage, JSON.stringify(blocks));
			}
		}
	};
	
	// Initialisation
	$(document).on('ready.documenter', function ready() {
		documenter.init();
	});

})(jQuery.noConflict());
