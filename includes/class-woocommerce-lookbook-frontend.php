<?php
/**
 * Prevent file from being accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Lookbook_Frontend{

	public $prefix;

	/**
	 * Constructor function
	 * 
	 * @access public
	 * @since 0.1
	 * @return void
	 */
	public function __construct () {
		$this->prefix = "_wc_lookbook_";

		add_action( 'wp_head',  	array( $this, 'enqueue_scripts' ) );	
		add_filter( 'the_content', 	array( $this, 'append_lookbook' ) );
	} // End __construct()

	/**
	 * Enqueue scripts on lookbook's single template
	 * 
	 * @access public
	 * @since 0.1
	 * @return void
	 */
	public function enqueue_scripts () {
		if( ! is_admin() && is_singular( 'lookbook') ){
			wp_enqueue_style( 'wc-lookbook-frontend', WC_LOOKBOOK_URL . 'css/wc-lookbook-frontend.css', array(), false, 'all' );
		}
	} // End enqueue_scripts()

	/**
	 * Append lookbook to the end of content
	 * 
	 * @access public
	 * @since 0.1
	 * @return void
	 */
	public function append_lookbook( $content ) {

		if( ! is_admin() && is_singular( 'lookbook' ) ){
			global $post;

			$content .= $this->get_the_lookbook( $post->ID );
		}

		return $content;
	} // End append_lookbook()

	/**
	 * Get lookbook
	 * 
	 * @access public
	 * @since 0.1
	 * @return string
	 */
	public function get_the_lookbook ( $post_id = false ){

		/**
		 * If no post_id defined, use global's post ID
		 */
		if( ! $post_id ){
			global $post;

			$post_id = $post->ID;
		}

		/**
		 * Capture lookbook as string
		 */
		ob_start();

		$this->the_lookbook( $post_id );

		$output = ob_get_clean();

		/**
		 * Return the lookbook
		 */
		return $output;
	} // End get_the_lookbook()

	/**
	 * Displaying lookbook
	 * 
	 * @access public
	 * @since 0.1
	 * @return void
	 */
	public function the_lookbook ( $post_id = false ) {
		/**
		 * If no post_id defined, get global's post ID
		 */
		if( ! $post_id ){
			global $post;

			$post_id = $post->ID;
		}

		/**
		 * Get currently saved lookbook
		 */
		$lookbook = get_post_meta( $post_id, "{$this->prefix}data", true );

		if( is_array( $lookbook ) && ! empty( $lookbook ) ) : 
		?>

			<div class="wc-lookbook">
				
				<p class="wc-lookbook-tip"><?php echo apply_filters( 'wc_lookbook_tap_to_hide_tip', __( 'Tip: tap image to hide tag', 'woocommerce-lookbook' ) ); ?></p>

				<?php 
					foreach ( $lookbook as $image ) : 

					/**
					 * Skip the loop if there's no image ID
					 */
					if( ! isset( $image['image_id'] ) )
						continue;

					$image_id = (int)$image['image_id'];

					/**
					 * Get image URL
					 */
					$attachment = wp_get_attachment_image_src( intval( $image_id ), 'full' );

					if( ! $attachment || ! isset( $attachment[0] ) )
						continue;					
				?>
					
					<div class="wc-lookbook-image-wrap">
						
						<div class="wc-lookbook-image">

							<img src="<?php echo esc_attr( $attachment[0] ); ?>" alt="<?php echo ( isset( $image['image_caption'] ) ? $image['image_caption'] : '' ); ?>" class="image">
							
							<?php if( isset( $image['tags'] ) && ! empty( $image['tags'] ) ) : ?>

							<div class="wc-lookbook-image-tags">
								
								<?php foreach ( $image['tags'] as $tag ) : ?>

									<?php  
										/**
										 * product_id, offset_x, and offset_y must exist
										 */
										if( 
											! isset( $tag['product_id'] ) || ! isset( $tag['offset_x'] ) || ! isset( $tag['offset_y'] ) ||
											'' == $tag['product_id'] || '' == $tag['offset_x'] || '' == $tag['offset_y']
										){
											continue;
										}

										/**
										 * Get associated product
										 */
										$product = get_post( $tag['product_id'] );

										if ( ! $product )
											continue;
									?>

									<a href="<?php echo get_permalink( $tag['product_id'] ); ?>" class="wc-lookbook-image-tag" style="top: <?php echo "{$tag['offset_y']}%"; ?>; left: <?php echo "{$tag['offset_x']}%"; ?> ; width: 100px; margin-left: -50px; margin-top: 5px; ">
										<?php echo $product->post_title; ?>
									</a>

								<?php endforeach; ?>

							</div>

							<?php endif; ?>

						</div><!-- .wc-lookbook-image -->

						<?php if( isset( $image['image_caption'] ) ) : ?>

							<div class="wc-lookbook-image-caption"><?php echo esc_attr( $image['image_caption'] );?></div>

						<?php endif; ?>

					</div><!-- .wc-lookbook-image-wrap -->

				<?php endforeach; ?>

			</div><!-- .wc-lookbook -->

			<script type="text/javascript">
				var wc_lookbook = document.querySelector('.wc-lookbook' );				
				wc_lookbook.addEventListener( 'click', function(e){
					if( -1 == wc_lookbook.className.indexOf( ' hide-tags' ) ){
						wc_lookbook.className += ' hide-tags';
					} else {
						wc_lookbook.className = wc_lookbook.className.replace( ' hide-tags', '' );
					}
				});
			</script>			
		<?php 
		endif;
	} // End the_lookbook()
}
new WC_Lookbook_Frontend;