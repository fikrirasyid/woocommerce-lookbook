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
	      multiple: false  // Set to true to allow multiple files to be selected
	    });
	 
	    // When an image is selected, run a callback.
	    file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();

			// Check if selected image has been existed
			if( $('.wc-lookbook-image-wrap[data-image-id="'+attachment.id+'"]').length > 0 ){
				alert( wc_lookbook_editor_params.no_duplicate_message.replace( '%filename%', attachment.filename ) );

				return;
			}
 
			// Prepare template
			image_wrap = $('#template-wc-lookbook-image-wrap').clone().html();
			image_wrap.replace( '%image_id%', attachment.id );

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
});