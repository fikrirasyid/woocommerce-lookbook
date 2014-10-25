jQuery(document).ready(function($){
	/**
	* If there's an image already, hide the no-wc-lookbook-image-notice
	*/
	if( $('.wc-lookbook-image-wrap').length > 0 ){
		$('.no-wc-lookbook-image-notice').hide();
	}

	/**
	* Making the order of image sortable
	*/
	$('#lookbook-metabox .images-wrap').sortable();

	/**
	* Adding image mechanism
	*/
	$('body').on( 'click', '.wc-lookbook-image-add', function(e){
		e.preventDefault();

		var file_frame;

		// If the media frame already exists, reopen it.
	    if ( file_frame ) {
	      file_frame.open();
	      return;
	    }

	    // Create the media frame.
	    file_frame = wp.media.frames.file_frame = wp.media({
	      multiple: true  // Set to true to allow multiple files to be selected
	    });
	 
	    // When an image is selected, run a callback.
	    file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachments = file_frame.state().get('selection').toJSON();

			for (var i = attachments.length - 1; i >= 0; i--) {
				var attachment = attachments[i];

				// Check if selected image has been existed
				if( $('.wc-lookbook-image-wrap[data-image-id="'+attachment.id+'"]').length > 0 ){
					alert( wc_lookbook_editor_params.no_duplicate_message.replace( '%filename%', attachment.filename ) );

					continue;
				}
	 
				// Prepare template
				image_wrap = $('#template-wc-lookbook-image-wrap').clone().html();

				// Prepare input name
				var name_image_id 		= "lookbook[]['"+attachment.id+"']['image_id']";
				var name_image_caption 	= "lookbook[]['"+attachment.id+"']['image_caption']";

				// Append
				$('.images-wrap').append( image_wrap );

				// Modify data
				$('.images-wrap .wc-lookbook-image-wrap:last, .images-wrap .wc-lookbook-image-wrap:last .wc-lookbook-image-tags').attr({ 'data-image-id' : attachment.id });
				$('.images-wrap .wc-lookbook-image-wrap:last img').attr({ 'src' : attachment.url, 'alt' : attachment.caption });
				$('.images-wrap .wc-lookbook-image-wrap:last .wc-lookbook-image-id').attr({ 'name' : name_image_id, 'value' : attachment.id });
				$('.images-wrap .wc-lookbook-image-wrap:last .wc-lookbook-image-caption').attr({ 'name' : name_image_caption, 'value' : attachment.caption });

				// Hide no image notice
				$('.no-wc-lookbook-image-notice').hide();

			};			
	    });
	 
	    // Finally, open the modal
	    file_frame.open();
	});

	/**
	* Removing image mechanism
	*/
	$('body').on( 'click', '.wc-lookbook-image-remove', function(e){
		e.preventDefault();

		$(this).parents('.wc-lookbook-image-wrap').remove();

		/**
		* Display no image yet notice if there's no more wc-lookbook-image-wrap
		*/
		if( $('.wc-lookbook-image-wrap').length == 0 ){
			$('.no-wc-lookbook-image-notice').show();
		}
	});

	/**
	* Removing all image mechanism
	*/
	$('body').on( 'click', '.wc-lookbook-image-remove-all', function(e){
		e.preventDefault();

		$('.wc-lookbook-image-wrap').remove();

		/**
		* Display no image yet notice
		*/
		$('.no-wc-lookbook-image-notice').show();
	});

	/**
	* Product finder
	*/
	$('#product-finder').select2({
		id: function(e) { return e.id }, 
	    minimumInputLength: 2,
    	width: '100%',
    	placeholder: wc_lookbook_editor_params.product_finder_placeholder,
	    ajax: {
	    	url 		: wc_lookbook_editor_params.ajax_url,
	    	dataType 	: 'json',
	    	type 		: "POST",
	    	data 		: function( term, page ){
	    		return{
	    			wp_ajax	: true,
	    			action 	: 'wc_lookbook_product_finder',
	    			_n		: wc_lookbook_editor_params.product_finder_nonce,
	    			keyword : term
	    		}
	    	},
	    	results: function( data, page ){
	    		return{
	    			results: data
	    		}
	    	}
	    }
	}).on('select2-selecting', function(e){
		// Prepare variable
		product_finder_wrap = $('#product-finder-wrap');
		tag_x 				= product_finder_wrap.attr( 'data-tag-x' );
		tag_y 				= product_finder_wrap.attr( 'data-tag-y' );
		product_id 			= e.object.id;
		product_name 		= e.object.text;
		image_wrap 			= $('.wc-lookbook-image-wrap.active');
		image_id 			= image_wrap.attr('data-image-id');

		// Prepare template
		image_tag 		= $('#template-wc-lookbook-image-tag').clone().html();
		image_tag_field = $('#template-wc-lookbook-image-tag-field').clone().html();

		// Append image tag
		image_wrap.find('.wc-lookbook-image-tags').append( image_tag );

			// Modify image tag param
			tag 				= image_wrap.find( '.wc-lookbook-image-tags .tag:last');		
			tag.find('.name').text( product_name );

			// Tag positioning
			image_wrap_width	= image_wrap.width();
			tag_width			= tag.outerWidth() + 2;
			tag_x_adjustment	= 0 - Math.ceil( ( tag_width / 2 ) );
			tag.css({ 
				'top' : tag_y + '%', 
				'left' : tag_x + '%', 
				'width' : tag_width + 'px', 
				'margin-left' : tag_x_adjustment, 
				'margin-top' : 5 
			}).attr({ 'data-product-id' : product_id });

		// Append image tag field
		image_wrap.find('.wc-lookbook-image-fields').append( image_tag_field );

			// Modify image tag field
			tag_field 		= image_wrap.find('.wc-lookbook-image-field-tag:last' );
			
			tag_field.attr({ 'data-image-id' : image_id , 'data-product-id' : product_id });
			tag_field.find('.product-id').attr({ 'name' : "lookbook[]['"+image_id+"']['tags']['"+product_id+"']['product_id']", value : product_id });
			tag_field.find('.offset-x').attr({ 'name' : "lookbook[]['"+image_id+"']['tags']['"+product_id+"']['offset_x']", value : tag_x });
			tag_field.find('.offset-y').attr({ 'name' : "lookbook[]['"+image_id+"']['tags']['"+product_id+"']['offset_y']", value : tag_y });

		product_finder_hide();
	});

	/**
	* Adding tag: display product finder to tag product on lookbook image
	*/
	$('body').on( 'click', '.wc-lookbook-image-mousetrap', function(e){
		e.preventDefault();

		var mousetrap 		 		= $(this),
			mousetrap_width 		= mousetrap.outerWidth(),
			mousetrap_height 		= mousetrap.outerHeight(),
			mousetrap_offset 		= mousetrap.offset(), 
			doc_x 			 		= mousetrap_offset.left,
			doc_y 			 		= mousetrap_offset.top,
			x 	  			 		= ( ( ( e.pageX - doc_x ) / mousetrap_width ) * 100 ).toFixed( 2 ), // top percentage relative to the mousetrap
			y 	 			 		= ( ( ( e.pageY - doc_y ) / mousetrap_height ) * 100 ).toFixed( 2 ), // left percentage relative to the mousetrap
			window_scrolltop 		= $(window).scrollTop(),
			product_finder_height 	= $('#product-finder-wrap').height(),
			product_finder_width 	= $('#product-finder-wrap').width(),
			product_finder_x 		= doc_x + ( ( mousetrap_width / 100 ) * x ) - ( product_finder_width / 2 ),
			product_finder_y 		= ( doc_y - window_scrolltop ) + ( ( mousetrap_height / 100 ) * y );

		// Display product finder
		product_finder_show( product_finder_x, product_finder_y, mousetrap, x, y );
	});

	/**
	* Close product finder tag mechanism
	*/
	$('body').on( 'click', '#product-finder-modal', function(e){
		e.preventDefault();

		product_finder_hide();
	})

	function product_finder_show( x, y, mousetrap, tag_x, tag_y ){
		$('body').css({ 'overflow' : 'hidden' });
		$('#product-finder-wrap').show().css({ 'top' : y, 'left' : x, 'margin-top' : 6, 'margin-left' : -5 }).attr({ 'data-tag-x' : tag_x, 'data-tag-y' : tag_y });
		$('#product-finder-modal').show();
		$('#product-finder').select2( 'open' );

		mousetrap.parents('.wc-lookbook-image-wrap').addClass('active');
	}

	function product_finder_hide(){
		$('body').css({ 'overflow' : 'auto' });
		$('#product-finder-wrap').hide().removeAttr('data-tag-x data-tag-y');
		$('#product-finder-modal').hide();
		$('#product-finder').select2( 'val', '' );

		$('.wc-lookbook-image-wrap.active').removeClass('active');
	}

	/**
	* Removing tag
	*/
	$('body').on( 'click', '.wc-lookbook-tag-remove', function(e){
		e.preventDefault();

		// Preparing variables
		var click 		= $(this);
		var product_id 	= click.parents( '.tag' ).attr( 'data-product-id' );
		var image_wrap 	= click.parents('.wc-lookbook-image-wrap');

		// Remove tag and field tag
		image_wrap.find('.tag[data-product-id="'+product_id+'"]').remove();
		image_wrap.find('.wc-lookbook-image-field-tag[data-product-id="'+product_id+'"]').remove();
	});
});