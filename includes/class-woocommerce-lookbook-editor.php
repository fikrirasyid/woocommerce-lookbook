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

		add_action( 'admin_print_styles', 	array( $this, 'enqueue_scripts' ) );	
		add_action( 'add_meta_boxes', 		array( $this, 'register_meta_box' ) );	
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
				wp_enqueue_style( 'wc_lookbook_editor', WC_LOOKBOOK_URL . 'css/wc-lookbook-editor.css', array(), false, 'all' );
		        wp_enqueue_script( 'wc_lookbook_editor', WC_LOOKBOOK_URL . 'js/wc-lookbook-editor.js', array( 'jquery' ), '0.1', true );
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
				<div class="image-wrap">
					<div class="image">
						<img src="http://localhost/hijapedia/store/wp-content/uploads/sites/2/2014/03/MG_8274.jpg" alt="">						

						<div class="image-tags">
							<div class="tag">
								<span class="name">Dress Way</span>
								<span class="actions">
									<a href="#" class="remove"><span class="label">Remove</span></a>
								</span>
							</div>
						</div><!-- .image-tags -->						
					</div><!-- .image -->

					<div class="image-actions">
						<input type="text" class="input-text image-description" placeholder="<?php _e( 'Describe this image', 'woocommerce-lookbook' ); ?>">
						<a href="#" class="image-remove button"><?php _e( 'Remove', 'woocommerce-lookbook' ); ?></a>
					</div>
				</div>				
			</div>

			<div class="images-actions">
					<a href="#" class="image-add button button-large button-primary"><?php _e( 'Add Image', 'woocommerce-lookbook' ); ?></a>				
					<a href="#" class="image-remove-all button button-large"><?php _e( 'Remove All Images', 'woocommerce-lookbook' ); ?></a>				
			</div>
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

	}
}
new WC_Lookbook_Editor;