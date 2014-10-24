jQuery(document).ready(function($){
	/**
	* If there's an image already, hide the no-image-notice
	*/
	if( $('.image-wrap').length > 0 ){
		$('.no-image-notice').hide();
	}

	/**
	* Making the order of image sortable
	*/
	$('#lookbook-metabox .images-wrap').sortable();

	/**
	* Adding image mechanism
	*/
	$('body').on( 'click', '.image-add', function(e){
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
 
				// Prepare template
			image_wrap = $('#template-image-wrap').clone().html();
			image_wrap.replace( '%image_id%', attachment.id );

			// Prepare input name
			var name_image_id 		= "lookbook[]['"+attachment.id+"']['image_id']";
			var name_image_caption 	= "lookbook[]['"+attachment.id+"']['image_caption']";

			// Append
			$('.images-wrap').append( image_wrap );

			// Modify data
			$('.images-wrap .image-wrap:last, .images-wrap .image-wrap:last .image-tags').attr({ 'data-image-id' : attachment.id });
			$('.images-wrap .image-wrap:last img').attr({ 'src' : attachment.url, 'alt' : attachment.caption });
			$('.images-wrap .image-wrap:last .image-id').attr({ 'name' : name_image_id, 'value' : attachment.id });
			$('.images-wrap .image-wrap:last .image-caption').attr({ 'name' : name_image_caption, 'value' : attachment.caption });

			// Hide no image notice
			$('.no-image-notice').hide();
	    });
	 
	    // Finally, open the modal
	    file_frame.open();
	});

	/**
	* Removing image mechanism
	*/
	$('body').on( 'click', '.image-remove', function(e){
		e.preventDefault();

		$(this).parents('.image-wrap').remove();

		/**
		* Display no image yet notice if there's no more image-wrap
		*/
		if( $('.image-wrap').length == 0 ){
			$('.no-image-notice').show();
		}
	});

	/**
	* Removing all image mechanism
	*/
	$('body').on( 'click', '.image-remove-all', function(e){
		e.preventDefault();

		$('.image-wrap').remove();

		/**
		* Display no image yet notice
		*/
		$('.no-image-notice').show();
	});
});