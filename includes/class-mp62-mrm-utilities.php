<?php
class mp62_mrm_Utilities {
	/**
	 * Add 'Duplicate' link to a post
	 */
	public static function add_custom_action_link( $actions, $post ) {
		if ( in_array( get_post_type($post), array( 'mp62_mrm_item', 'mp62_mrm_menu' ) ) ) {
			$actions['duplicate'] = '<a href="' . admin_url('admin.php?action=duplicate_post&post=' . $post->ID) . '" title="' . __('Duplicate this item', MP62_TEXTDOMAIN) . '">' .  __('Duplicate', MP62_TEXTDOMAIN) . '</a>';
		}
		if ( in_array( get_post_type($post), array( 'mp62_mrm_menu' ) ) ) {
			$actions['generate_pdf'] = '<a href="' . admin_url('admin.php?action=generate_pdf&post=' . $post->ID) . '" title="' . __('Generate PDF', MP62_TEXTDOMAIN) . '">' .  __('Generate PDF', MP62_TEXTDOMAIN) . '</a>';
		}
		return $actions;
	}
	/**
	 * Start duplicate (action)
	 */
	public static function make_duplicate_post() {
		if (! ( isset( $_GET['post'] ) || isset( $_POST['post'] )  || ( isset( $_REQUEST['action'] ) && 'duplicate_post' == $_REQUEST['action'] ) ) ) {
			wp_die( __('No post to duplicate has been supplied!', MP62_TEXTDOMAIN) );
		}
		// Get the original post
		$id = ( isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post'] );
		$post = get_post($id);

		// Copy the post and insert it
		if ( isset($post) && $post != null ) {
			$status = '';
			$new_id = self::duplicate_post( $post, $status );
			self::duplicate_post_taxonomies( $new_id, $post );
			self::duplicate_post_meta_info( $new_id, $post );
			self::duplicate_post_children( $new_id, $post );
			if ( $status == '' ) {
				// Redirect to the post list screen
				wp_redirect( admin_url( 'edit.php?post_type=' . $post->post_type ) );
			} else {
				// Redirect to the edit screen for the new draft post
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
			}
			exit;
		} else {
			$post_type_obj = get_post_type_object( $post->post_type );
			wp_die( esc_attr( __( 'Copy creation failed, could not find original:', MP62_TEXTDOMAIN ) ) . ' ' . $id );
		}
	}

	/**
	 * Create a duplicate from a post
	 */
	public static function duplicate_post( $post, $status = '', $parent_id = '' ) {
		// We don't want to duplicate revisions
		if ( $post->post_type == 'revision' ) return;

		if ( $post->post_type != 'attachment' ){
			$prefix = __('(Copy) ', MP62_TEXTDOMAIN);
			$suffix = '';
		}
		$new_post = array(
			'menu_order'     => $post->menu_order,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $post->post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_mime_type' => $post->post_mime_type,
			'post_parent'    => $new_post_parent = empty($parent_id) ? $post->post_parent : $parent_id,
			'post_password'  => $post->post_password,
			'post_status'    => $new_post_status = (empty($status)) ? $post->post_status : $status,
			'post_title'     => $prefix . $post->post_title . $suffix,
			'post_type'      => $post->post_type,
		);
		$new_post_id = wp_insert_post( $new_post );

		delete_post_meta( $new_post_id, '_dp_original' );
		add_post_meta( $new_post_id, '_dp_original', $post->ID );

		// If the copy is published or scheduled, we have to set a proper slug.
		if ( $new_post_status == 'publish' || $new_post_status == 'future' ){
			$post_name = wp_unique_post_slug( $post->post_name, $new_post_id, $new_post_status, $post->post_type, $new_post_parent );

			$new_post = array();
			$new_post['ID'] = $new_post_id;
			$new_post['post_name'] = $post_name;

			// Update the post into the database
			wp_update_post( $new_post );
		}
		return $new_post_id;
	}
	/**
	 * Copy the taxonomies of a post to another post
	 */
	public static function duplicate_post_taxonomies( $new_id, $post ) {
		global $wpdb;
		if ( isset( $wpdb->terms ) ) {
			// Clear default category (added by wp_insert_post)
			wp_set_object_terms( $new_id, NULL, 'category' );
			$post_taxonomies = get_object_taxonomies( $post->post_type );
			foreach ( $post_taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post->ID, $taxonomy, array( 'orderby' => 'term_order' ) );
				$terms = array();
				for ($i=0; $i<count($post_terms); $i++) {
					$terms[] = $post_terms[$i]->slug;
				}
				wp_set_object_terms( $new_id, $terms, $taxonomy );
			}
		}
	}
	/**
	 * Copy the meta information of a post to another post
	 */
	public static function duplicate_post_meta_info( $new_id, $post ) {
		$post_meta_keys = get_post_custom_keys( $post->ID );
		if ( empty( $post_meta_keys ) ) {
			return;
		}
		foreach ( $post_meta_keys as $meta_key ) {
			$meta_values = get_post_custom_values( $meta_key, $post->ID );
			foreach ($meta_values as $meta_value) {
				$meta_value = maybe_unserialize( $meta_value );
				add_post_meta( $new_id, $meta_key, $meta_value );
			}
		}
	}
	/**
	 * Copy the attachments
	 * It simply copies the table entries, actual file won't be duplicated
	 */
	public static function duplicate_post_children( $new_id, $post ){
		// get children
		$children = get_posts( array( 'post_type' => 'any', 'numberposts' => -1, 'post_status' => 'any', 'post_parent' => $post->ID ) );
		// duplicate old attachments
		foreach( $children as $child ){
			self::duplicate_post( $child, '', $new_id );
		}
	}

	public static function show_image_box( $id = 0, $size = 'thumbnail' ) {
		global $_wp_additional_image_sizes;
		$width = intval( $_wp_additional_image_sizes[$size]['width'] );
		$height = intval( $_wp_additional_image_sizes[$size]['height'] );

		$id = intval( $id );
		$_post = get_post( $id );
		$html = '';
		if ( empty( $_post ) || ( 'attachment' != $_post->post_type ) || ! $url = wp_get_attachment_url( $_post->ID ) ) {
			$html = '<img id="mp62_mrm_taxonomy_image" width="' . $width . '" height="' . $height . '" src="' . MP62_MRM_PLUGIN_URL . '/images/no-image.png" class="attachment-mp62_mrm_icon_size" alt="">';
		} else {
//			$html = '<a href="' . $url . '">';
			$html .= wp_get_attachment_image( $id, $size, false, array( 'id' => 'mp62_mrm_taxonomy_image' ) );
//			$html .= '</a>';
		}
		return $html;
	}
	/**
	 * Return the template filename from theme if present
	 * otherwise return template filename from plugin
	 * If $check_mobile is true, return the filename prefix with 'm_'
	 */
	public static function get_custom_template( $template, $check_mobile = false ) {
		$template = basename( $template );
		if ( $check_mobile ) {
			if ( wp_is_mobile() ) {
				$template = 'm_' . $template;
			}
		}
		if ( file_exists( get_stylesheet_directory() . '/my-restaurant-menu/' . $template ) ) {
			return get_stylesheet_directory() . '/my-restaurant-menu/' . $template;
		} else {
			return MP62_MRM_PLUGIN_PATH . '/templates/' . $template;
		}
	}
	public static function render( $template, $variables = '' ) {
		extract( $variables );
		ob_start();
		$template_path = mp62_mrm_Utilities::get_custom_template( $template );
		if ( $template_path ) {
			include( $template_path );
		}
		$output = ob_get_clean();
		return $output;
	}
	public static function is_assoc($array) {
		return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
	}
	public static function create_select( $value, $list ) {
		if ( self::is_assoc( $list ) ) {
			foreach ( $list as $k => $i ) {
				if ( $k == $value ) {
					echo '<option selected="selected" value="' . $k . '">' . $i . "</option>\n";
				} else {
					echo '<option value="' . $k . '">' . $i . "</option>\n";
				}
			}
		} else {
			foreach ( $list as $k ) {
				if ( $k == $value ) {
					echo '<option selected="selected" value="' . $k . '">' . $k . "</option>\n";
				} else {
					echo '<option value="' . $k . '">' . $k . "</option>\n";
				}
			}
		}
	}
	public static function mce_buttons( $buttons ) {
		array_unshift( $buttons, 'fontselect' ); // Add Font Select
		array_unshift( $buttons, 'fontsizeselect' ); // Add Font Size Select
		return $buttons;
	}
	public static function mce_google_fonts_array( $initArray ) {
		$fonts_array = get_option( 'mp62_mrm_fonts_list' );
		$font_family_list = array();
		foreach ( $fonts_array as $label => $data ) {
			$font_family_list[] = $label . '=' . $data[0];
		}
		$initArray['font_formats'] = implode( ';', $font_family_list );
		$initArray['content_css'] = MP62_MRM_PLUGIN_PATH . '/css/my-restaurant-menu-fonts.css';
		return $initArray;
	}
}
?>
