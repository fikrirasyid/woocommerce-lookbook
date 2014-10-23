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
});