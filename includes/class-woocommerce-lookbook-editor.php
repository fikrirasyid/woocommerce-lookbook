<?php
/**
 * Prevent file from being accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Lookbook_Editor{

	var $prefix;

	/**
	 * Construct the class
	 */
	function __construct(){
		$this->prefix = "_wc_lookbook_";

		add_action( 'admin_print_styles', 							array( $this, 'enqueue_scripts' ) );	
		add_action( 'add_meta_boxes', 								array( $this, 'register_meta_box' ) );	
		add_action( 'save_post', 									array( $this, 'save_meta_box' ) );
		add_action( 'wp_ajax_wc_lookbook_product_finder', 			array( $this, 'endpoint_product_finder' ) );
		add_action( 'wp_ajax_nopriv_wc_lookbook_product_finder', 	array( $this, 'endpoint_product_finder' ) );
	}

	/**
	 * Enqueueing scripts for lookbook editor
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function enqueue_scripts(){
		/**
		 * Only enqueue the script on admin & lookbook editor screen
		 * Make sure that get_current_screen exists
		 */
		if( is_admin() && function_exists( 'get_current_screen' ) ){

			$screen = get_current_screen();

			if( 'lookbook' == $screen->post_type ){
		        wp_register_script( 'jquery-select2', WC_LOOKBOOK_URL . 'js/select2.js', array( 'jquery' ), '3.5.1', true );

				wp_enqueue_style( 'wc_lookbook_editor', WC_LOOKBOOK_URL . 'css/wc-lookbook-editor.css', array(), false, 'all' );
		        wp_enqueue_script( 'wc_lookbook_editor', WC_LOOKBOOK_URL . 'js/wc-lookbook-editor.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-select2' ), '0.1', true );
				
				$wc_lookbook_editor_params = array(
					'no_duplicate_message' 			=> __( '%filename% image have been added to this lookbook before. You cannot have one image more than once in a lookbook.', 'woocommerce-lookbook'),
					'no_duplicate_product' 			=> __( '%productname% have been added to this image before. Product cannot be used more than once in an image.', 'woocommerce-lookbook'),
					'ajax_url'						=> admin_url( 'admin-ajax.php' ),
					'product_finder_placeholder'	=> __( 'Search and Select Product', 'woocommerce-lookbook' ),
					'product_finder_nonce'			=> wp_create_nonce( 'product_finder_nonce' )
				);
				wp_localize_script( 'wc_lookbook_editor', 'wc_lookbook_editor_params', $wc_lookbook_editor_params );
			}
		}
	}

	/**
	 * Registering lookbook meta box for configuring lookbook
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function register_meta_box(){
		add_meta_box('lookbook-metabox', __( 'Lookbook', 'woocommerce-lookbook' ), array( $this, 'display_meta_box' ), 'lookbook' );
	}

	/**
	 * Displaying lookbook meta box
	 * 
	 * @access public
	 * 
	 * @return void
	 */
	public function display_meta_box(){
		global $post;

		/**
		 * Get currently saved lookbook
		 */
		$lookbook = get_post_meta( $post->ID, "{$this->prefix}data", true );
		?>
			<div class="images-wrap">
				<?php 
				if( is_array( $lookbook ) && ! empty( $lookbook ) ) :
					foreach ( $lookbook as $image ) :
						/**
						 * Just in case there's no image id
						 */
						if( ! isset( $image['image_id'] ) )
							continue;

						$image_id = (int)$image['image_id'];

						/**
						 * Get image path
						 */
						$attachment = wp_get_attachment_image_src( intval( $image_id ), 'full' );

						if( ! $attachment || ! isset( $attachment[0] ) )
							continue;
						?>

						<div class="wc-lookbook-image-wrap" data-image-id="<?php echo $image['image_id']; ?>">
							<div class="image">
								<div class="wc-lookbook-inside">
									<img src="<?php echo esc_attr( $attachment[0] );?>" alt="">		
								</div>			

								<div class="wc-lookbook-image-tags">
									<?php
										if( isset( $image['tags'] ) && is_array( $image['tags'] ) && ! empty( $image['tags'] ) ):
											foreach ( $image['tags'] as $tag ) :

											/**
											 * product_id, offset_x, and offset_y must exist
											 */
											if( 
												! isset( $tag['product_id'] ) || ! isset( $tag['offset_x'] ) || ! isset( $tag['offset_y'] ) ||
												'' == $tag['product_id'] || '' == $tag['offset_x'] || '' == $tag['offset_y']
											){
												continue;
											}

											$product = get_post( $tag['product_id'] );
											?>
												<div class="tag" data-product-id="<?php echo $tag['product_id']; ?>" style="top: <?php echo "{$tag['offset_y']}%"; ?>; left: <?php echo "{$tag['offset_x']}%"; ?> ; width: 100px; margin-left: -50px; margin-top: 5px;">
													<span class="name"><?php echo esc_attr( $product->post_title ); ?></span>
													<span class="actions">
														<a href="#" class="wc-lookbook-tag-remove"><span class="label">Remove</span></a>
													</span>
												</div>
											<?php
											endforeach;
										endif;
									?>
								</div><!-- .wc-lookbook-image-tags -->						

								<div class="wc-lookbook-image-mousetrap">
								</div><!-- .wc-lookbook-image-tags -->											
							</div><!-- .image -->

							<div class="wc-lookbook-image-fields">
								<input type="number" class="wc-lookbook-image-id" name="lookbook[<?php echo $image_id; ?>][image_id]" value="<?php echo $image_id; ?>" />
									<?php
										if( isset( $image['tags'] ) && is_array( $image['tags'] ) && ! empty( $image['tags'] ) ):
											foreach ( $image['tags'] as $tag ) :

											/**
											 * product_id, offset_x, and offset_y must exist
											 */
											if( 
												! isset( $tag['product_id'] ) || ! isset( $tag['offset_x'] ) || ! isset( $tag['offset_y'] ) ||
												'' == $tag['product_id'] || '' == $tag['offset_x'] || '' == $tag['offset_y']
											){
												continue;
											}

											$product = get_post( $tag['product_id'] );
											?>
												<div class="wc-lookbook-image-field-tag" data-image-id="<?php echo $image_id; ?>" data-product-id="<?php echo $tag['product_id']; ?>">
													<input type="number" class="product-id" name="lookbook[<?php echo $image_id; ?>][tags][<?php echo $tag['product_id']; ?>][product_id]" value="<?php echo $tag['product_id']; ?>" />
													<input type="number" class="offset-x" name="lookbook[<?php echo $image_id; ?>][tags][<?php echo $tag['product_id']; ?>][offset_x]" value="<?php echo $tag['offset_x']; ?>" />
													<input type="number" class="offset-y" name="lookbook[<?php echo $image_id; ?>][tags][<?php echo $tag['product_id']; ?>][offset_y]" value="<?php echo $tag['offset_y']; ?>" /> 					
												</div>
											<?php
											endforeach;
										endif;
									?>
							</div>

							<div class="wc-lookbook-image-actions">
								<div class="wc-lookbook-inside">
									<textarea name="lookbook[<?php echo $image_id; ?>][image_caption]" class="input-text wc-lookbook-image-caption" placeholder="<?php _e( 'Describe this image', 'woocommerce-lookbook' ); ?>"><?php echo ( isset( $image['image_caption'] ) ? esc_attr( $image['image_caption'] ) : '' ); ?></textarea>
									<p class="wc-lookbook-image-tip"><?php _e( 'Tip: click on image to tag product', 'woocommerce-lookbook' ); ?></p>
									<a href="#" class="wc-lookbook-image-remove button"><?php _e( 'Remove', 'woocommerce-lookbook' ); ?></a>
								</div>
							</div>
						</div><!-- .wc-lookbook-image-wrap -->

						<?php 
					endforeach;
				endif; 
				?>
			</div>

			<div class="no-wc-lookbook-image-notice">
				<p><?php _e( "There is no image for this lookbook yet. Click 'Add Image' button below to start", "woocommerce-lookbook" ); ?></p>
			</div>

			<div class="images-actions">
					<a href="#" class="wc-lookbook-image-add button button-large button-primary"><?php _e( 'Add Image', 'woocommerce-lookbook' ); ?></a>				
					<a href="#" class="wc-lookbook-image-remove-all button button-large"><?php _e( 'Remove All Images', 'woocommerce-lookbook' ); ?></a>				
			</div>
			
			<div id="product-finder-wrap">
				<input type="text" id="product-finder" placeholder="<?php _e( 'Find product', 'woocommerce-lookbook' ); ?>">
			</div><!-- #product-finder-wrap -->
			<div id="product-finder-modal"></div>
	
			<!-- Template for wc-lookbook-image-wrap -->
			<script id="template-wc-lookbook-image-wrap" type="text/template">
				<div class="wc-lookbook-image-wrap">
					<div class="image">
						<div class="wc-lookbook-inside">
							<img src="" alt="">				
						</div>			

						<div class="wc-lookbook-image-tags">
						</div><!-- .wc-lookbook-image-tags -->						

						<div class="wc-lookbook-image-mousetrap">
						</div><!-- .wc-lookbook-image-tags -->											
					</div><!-- .image -->

					<div class="wc-lookbook-image-fields">
						<input type="number" class="wc-lookbook-image-id" name="lookbook[%image_id%][image_id]" value="%image_id%" />
					</div>

					<div class="wc-lookbook-image-actions">
						<div class="wc-lookbook-inside">
							<textarea name="lookbook[%image_id%][image_caption]" class="input-text wc-lookbook-image-caption" placeholder="<?php _e( 'Describe this image', 'woocommerce-lookbook' ); ?>"></textarea>							
							<p class="wc-lookbook-image-tip"><?php _e( 'Tip: click on image to tag product', 'woocommerce-lookbook' ); ?></p>
							<a href="#" class="wc-lookbook-image-remove button"><?php _e( 'Remove', 'woocommerce-lookbook' ); ?></a>
						</div>
					</div>
				</div>		
			</script>

			<!-- Template for product tag -->
			<script id="template-wc-lookbook-image-tag" type="text/template">
				<div class="tag">
					<span class="name">%product_name%</span>
					<span class="actions">
						<a href="#" class="wc-lookbook-tag-remove"><span class="label">Remove</span></a>
					</span>
				</div>
			</script>

			<!-- Template for appending product tag -->
			<script id="template-wc-lookbook-image-tag-field" type="text/template">
				<div class="wc-lookbook-image-field-tag" data-image-id="%image_id%" data-product-id="%product_id%">
					<input type="number" class="product-id" name="lookbook[%image_id%][tags][%product_id%][product_id]" value="%product_id%" />
					<input type="number" class="offset-x" name="lookbook[%image_id%][tags][%product_id%][offset_x]" value="%offset_x%" />
					<input type="number" class="offset-y" name="lookbook[%image_id%][tags][%product_id%][offset_y]" value="%offset_y%" /> 					
				</div>
			</script>
		<?php
		wp_nonce_field( "{$this->prefix}meta_box", "{$this->prefix}meta_box" );
	}

	/**
	 * Saving meta box data
	 * 
	 * @access public
	 * 
	 * @param int 		post ID
	 * 
	 * @return void
	 */
	public function save_meta_box( $post_id ){
		/**
		 * If save_post is triggered from front end, there will be no get_current_screen() loaded. Stop the process.
		 */
		if( !function_exists( 'get_current_screen' ) ){
			return;
		}

		$screen = get_current_screen();

		// Only run this on lookbook editor screen
		if ($screen != null && $screen->post_type != 'lookbook') 
			return;

		// Cancel if this is an autosave
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;

		// Verify nonce
		if( !isset( $_POST["{$this->prefix}meta_box"] ) || 
			!wp_verify_nonce( $_POST["{$this->prefix}meta_box"], "{$this->prefix}meta_box" ) ) 
			return;

		// if our current user can't edit this post, bail
		if( !current_user_can( 'edit_posts' ) ) return;		

		// Save lookbook data
		if( ! $_POST['lookbook'] || empty( $_POST['lookbook'] ) ){
			update_post_meta( $post_id, "{$this->prefix}data", array() ); 			
		} else {
			update_post_meta( $post_id, "{$this->prefix}data", $_POST['lookbook'] ); 			
		}
	}

	/**
	 * Product finder endpoint
	 * 
	 * @access public
	 * 
	 * @return void  echoing json output
	 */
	public function endpoint_product_finder(){

		$output = array();

		if( isset( $_REQUEST['keyword'] ) && isset( $_REQUEST['_n'] ) && '' != $_REQUEST['keyword'] ){

			/**
			 * Verify nonce
			 */
			if( wp_verify_nonce( $_REQUEST['_n'], 'product_finder_nonce' ) ){

				$args = array(
					'post_status' 			=> 'publish',
					'post_type'				=> 'product',
					'edit_posts_per_page' 	=> 10,
					's'						=> sanitize_text_field( $_REQUEST['keyword'] )
				);

				$posts = get_posts( $args );

				if( $posts ){

					foreach ( $posts as $post ) {
						$output[] = array(
							'id' 	=> $post->ID,
							'text'	=> $post->post_title
						);
					}
				}
			}
		}

		echo json_encode( $output );

		die();
	}
}
new WC_Lookbook_Editor;