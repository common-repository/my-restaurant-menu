<?php
class mp62_mrm_Items {
	/**
	* Create the new Custom Fields meta box.
	*/
	public static function create_meta_box() {
		add_meta_box('item-first-metabox', __('Ingredients', MP62_TEXTDOMAIN), 'mp62_mrm_Items::print_first_metabox', 'mp62_mrm_item', 'normal', 'default');
		add_meta_box('item-second-metabox', __('Price', MP62_TEXTDOMAIN), 'mp62_mrm_Items::print_second_metabox', 'mp62_mrm_item', 'side', 'default');
	}
	public static function print_first_metabox( $post ) {
		wp_nonce_field('update_mp62_mrm_fields','mp62_mrm_fields_nonce', true, true);
		?>
		<textarea rows="1" cols="40" name="excerpt" id="excerpt"><?php echo $post->post_excerpt; ?></textarea>
		<p><?php _e('A list of ingredients', MP62_TEXTDOMAIN); ?></p>
		<?php
	}
	public static function print_second_metabox( $post ) {
		?>
		<label class="screen-reader-text" for="mp62_mrm_price"><?php _e('Price', MP62_TEXTDOMAIN); ?></label>
		<input name="mp62_mrm_price" id="mp62_mrm_price" type="text" value="<?php echo get_post_meta($post->ID, 'mp62_mrm_price', true); ?>" />
		<?php
	}
	public static function save_custom_fields( $post_id ) {
		if ( !empty($_POST) && check_admin_referer('update_mp62_mrm_fields','mp62_mrm_fields_nonce') ) {
			update_post_meta( $post_id, 'mp62_mrm_price', trim($_POST['mp62_mrm_price']));
		}
	}
	public static function add_custom_columns( $columns ) {
	}
	public static function manage_custom_columns( $column, $post_id ) {
	}
}
?>
