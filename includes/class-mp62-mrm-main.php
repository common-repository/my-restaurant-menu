<?php
class mp62_mrm_Main {
	const min_php_version = '5.3.0';
	const min_wp_version = '3.9.1';
	const min_mysql_version = '5.1.72';
	/**
	* The main function for this plugin, similar to __construct()
	*/
	public static function initialize() {
		load_plugin_textdomain( MP62_TEXTDOMAIN, false, MP62_MRM_PLUGIN_PATH . '/lang/' );
		add_image_size( 'mp62_mrm_icon_size', 40, 40, true );
	}
	public static function activate() {
		global $wpdb, $wp_version;
		$mysql_version = $wpdb->db_version();
		$msg = '';
		if (version_compare( PHP_VERSION, self::min_php_version, '<')) {
			$msg .= sprintf(__(' - PHP version %1$s or higher. You are running version %2$s.<br>', MP62_TEXTDOMAIN), self::min_php_version, PHP_VERSION);
		}
		if (version_compare( $wp_version, self::min_wp_version, '<')) {
			$msg .= sprintf(__(' - WordPress version %1$s or higher. You are running version %2$s.<br>', MP62_TEXTDOMAIN), self::min_wp_version, $wp_version);
		}
		if (version_compare( $mysql_version, self::min_mysql_version, '<')) {
			$msg .= sprintf(__(' - MySQL version %1$s or higher. You are running version %2$s.<br>', MP62_TEXTDOMAIN), self::min_mysql_version, $mysql_version);
		}
		if ($msg != '') {
			deactivate_plugins( basename( __FILE__ ) );
			$header = __('You cannot install because <strong>My-Restaurant-Menu</strong> plugin requires:<br>', MP62_TEXTDOMAIN);
			wp_die($header . $msg, __('Plugin Activation Error', MP62_TEXTDOMAIN), array('response'=>200, 'back_link'=>TRUE));
		}
		// per ricreare in automatico le immagini resized,
		// guarda http://codex.wordpress.org/Function_Reference/wp_generate_attachment_metadata
		self::register_post_types();
		self::register_taxonomies();
		flush_rewrite_rules();
		self::set_default_options();
	}
	public static function deactivate() {
		flush_rewrite_rules();
	}
	public static function enqueue_admin_scripts() {
		wp_enqueue_media();

		wp_enqueue_script( 'wp-color-picker' ); 
		wp_enqueue_style( 'wp-color-picker' );          

		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' );
		wp_enqueue_script( 'my-restaurant-menu-js', MP62_MRM_PLUGIN_URL . '/js/my-restaurant-menu.js', array( 'jquery' ) );

		wp_enqueue_script( 'jquery-numberMask', MP62_MRM_PLUGIN_URL . '/js/jquery.numberMask.js', array( 'jquery' ) );

		$mrm_strings['label'] = __('Choose Image', MP62_TEXTDOMAIN);
		$mrm_strings['button'] = __('Choose an Image', MP62_TEXTDOMAIN);
		$mrm_strings['noimage_url'] = MP62_MRM_PLUGIN_URL . '/images/no-image.png';
		$mrm_strings['free_label'] = __('Insert your section name', MP62_TEXTDOMAIN);
		wp_localize_script( 'my-restaurant-menu-js', 'mp62_mrm_strings', $mrm_strings );

		wp_enqueue_style( 'mrm-admin-css', MP62_MRM_PLUGIN_URL . '/css/my-restaurant-menu-admin.css' );
		wp_enqueue_style( 'mrm-admin-fonts', MP62_MRM_PLUGIN_URL . '/css/my-restaurant-menu-fonts.css' );
	}
	public static function enqueue_frontend_scripts() {
		wp_enqueue_style( 'mrm-frontend-css', MP62_MRM_PLUGIN_URL . '/css/my-restaurant-menu-frontend.css' );
		wp_enqueue_style( 'mrm-admin-fonts', MP62_MRM_PLUGIN_URL . '/css/my-restaurant-menu-fonts.css' );
	}
	public static function register_post_types() {
		register_post_type('mp62_mrm_item', 
			array(
				'labels' => array(
					'name'				=> __('Items', MP62_TEXTDOMAIN),
					'singular_name'		=> __('Item', MP62_TEXTDOMAIN),
					'menu_name'			=> __('MRM', MP62_TEXTDOMAIN),
					'name_admin_bar'	=> __('name_admin_bar', MP62_TEXTDOMAIN),
					'all_items'			=> __('All items', MP62_TEXTDOMAIN),
					'add_new'			=> _x('Add New', 'mp62_mrm_item', MP62_TEXTDOMAIN),
					'add_new_item'		=> __('Add New Item', MP62_TEXTDOMAIN),
					'edit_item'			=> __('Edit Item', MP62_TEXTDOMAIN),
					'new_item'			=> __('New Item', MP62_TEXTDOMAIN),
					'view_item'			=> __('View Item', MP62_TEXTDOMAIN),
					'search_items'		=> __('Search Items', MP62_TEXTDOMAIN),
					'not_found'			=> __('No Items Found', MP62_TEXTDOMAIN),
					'not_found_in_trash'=> __('Not Found in Trash', MP62_TEXTDOMAIN),
				),
				'public'				=> true,
				'show_ui'				=> true,
				'capability_type'		=> 'post',
				'hierarchical'			=> false,
				'rewrite'				=> array( 'slug' => 'myrestaurantitems' ),
				'query_var'				=> true,
				'show_in_nav_menus'		=> false,
				'supports'				=> array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
				'taxonomies'			=> array( 'mp62_mrm_item_section', 'mp62_mrm_item_need' ),
				'register_meta_box_cb'	=> 'mp62_mrm_Items::create_meta_box',
				'menu_icon'				=> 'dashicons-smiley',
			)
		);
		register_post_type('mp62_mrm_menu', 
			array(
				'labels' => array(
					'name'				=> __('Menus', MP62_TEXTDOMAIN),
					'singular_name'		=> __('Menu', MP62_TEXTDOMAIN),
					'add_new' 			=> _x('Add New', 'mp62_mrm_menu', MP62_TEXTDOMAIN),
					'add_new_item'		=> __('Add New Menu', MP62_TEXTDOMAIN),
					'edit_item'			=> __('Edit Menu', MP62_TEXTDOMAIN),
					'new_item'			=> __('New Menu', MP62_TEXTDOMAIN),
					'view_item'			=> __('View Menu', MP62_TEXTDOMAIN),
					'search_items'		=> __('Search Menus', MP62_TEXTDOMAIN),
					'not_found'			=> __('No Menus Found', MP62_TEXTDOMAIN),
					'not_found_in_trash'=> __('Not Found in Trash', MP62_TEXTDOMAIN),
					'all_items'			=> __('Menus', MP62_TEXTDOMAIN),
				),
				'public'				=> true,
				'show_ui'				=> true,
				'hierarchical'			=> false,
				'rewrite'				=> array( 'slug' => 'myrestaurantmenu' ),
				'query_var'				=> true,
				'show_in_nav_menus'		=> false,
				'supports'				=> array('title', 'thumbnail', 'page-attributes'),
				'register_meta_box_cb'	=> 'mp62_mrm_Menus::create_meta_box',
				'show_in_menu'			=> 'edit.php?post_type=mp62_mrm_item',
			)
		);
	}
	public static function register_taxonomies() {
		register_taxonomy('mp62_mrm_item_section', array('mp62_mrm_item'),
			array(
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'hierarchical' => true,
				'show_in_admin_bar' => true,
				//'show_admin_column' => true,
			)
		);
		register_taxonomy('mp62_mrm_item_need', array('mp62_mrm_item'),
			array(
				'labels' => array(
					'name'					=> __('Item Needs', MP62_TEXTDOMAIN),
					'singular_name'			=> __('Item Need', MP62_TEXTDOMAIN),
					'menu_name'				=> __('Item need', MP62_TEXTDOMAIN),
					'all_items'				=> __('Item Needs', MP62_TEXTDOMAIN),
					'edit_item'				=> __('Edit Item Need', MP62_TEXTDOMAIN),
					'view_item'				=> __('View Item Need', MP62_TEXTDOMAIN),
					'update_item'			=> __('Update Item Need', MP62_TEXTDOMAIN),
					'add_new_item'			=> __('Add New Item Need', MP62_TEXTDOMAIN),
					'new_item_name'			=> __('New Item Need name', MP62_TEXTDOMAIN),
					'parent_item'			=> __('Parent Item Need', MP62_TEXTDOMAIN),
					'parent_item_colon'		=> __('Parent Item Need:', MP62_TEXTDOMAIN),
					'search_items'			=> __('Search Item Needs', MP62_TEXTDOMAIN),
				),
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'hierarchical' => false,
				//'show_admin_column' => true,
			)
		);
	}
	public static function add_menu_pages() {
		add_submenu_page( 'edit.php?post_type=mp62_mrm_item', __('Settings', MP62_TEXTDOMAIN), __('Settings', MP62_TEXTDOMAIN), 'manage_options', 'my-restaurant-menu-settings', 'mp62_mrm_Settings::create_admin_page' );
	}
	public static function sort_items( $query ) {
		if($query->is_admin) {
			if ($query->get('post_type') == 'mp62_mrm_item') {
				$query->set('orderby', 'menu_order');
				$query->set('order', 'ASC');
			}
			if ($query->get('post_type') == 'mp62_mrm_menu') {
				$query->set('orderby', 'menu_order');
				$query->set('order', 'ASC');
			}
		}
		return $query;
	}
	public static function save_image( $term_id ) {
		$attachment_id = isset( $_POST['mp62_mrm_taxonomy_image_id'] ) ? (int) $_POST['mp62_mrm_taxonomy_image_id'] : null;
		if ( !is_null( $attachment_id ) && $attachment_id > 0 && !empty( $attachment_id ) ) {
			update_option( 'mp62_mrm_taxonomy_image_' . $term_id, $attachment_id );
		} else {
			delete_option( 'mp62_mrm_taxonomy_image_' . $term_id );
		}
	}
	public static function admin_init() {
		add_action( 'mp62_mrm_item_section_add_form_fields'  , 'mp62_mrm_Main::show_add_taxonomy_field' );
		add_action( 'mp62_mrm_item_section_edit_form_fields' , 'mp62_mrm_Main::show_edit_taxonomy_field' );

		add_action( 'mp62_mrm_item_need_add_form_fields'  , 'mp62_mrm_Main::show_add_taxonomy_field' );
		add_action( 'mp62_mrm_item_need_edit_form_fields' , 'mp62_mrm_Main::show_edit_taxonomy_field' );
	}
	public static function show_add_taxonomy_field( $taxonomy ) {
		if ( $taxonomy->term_id ) {
			$attachment_id = get_option('mp62_mrm_taxonomy_image_' . $taxonomy->term_id);
		} else {
			$attachment_id = 0;
		}
		?>
		<div class="form-field">
			<label for="taxonomy_image"><?php _e('Image', MP62_TEXTDOMAIN); ?></label>
			<input type="hidden" name="mp62_mrm_taxonomy_image_id" id="mp62_mrm_taxonomy_image_id" value="<?php echo $attachment_id; ?>">
			<span id="mp62_mrm_imageholder"><?php echo mp62_mrm_Utilities::show_image_box($attachment_id, 'mp62_mrm_icon_size', true); ?></span>
			<span id="mp62_mrm_buttons">
				<input id="mp62_mrm_upload_button" class="button" type="button" value="<?php _e('Choose or upload an image', MP62_TEXTDOMAIN); ?>" />
				<input id="mp62_mrm_delete_button" class="button" type="button" value="<?php _e('Delete this image', MP62_TEXTDOMAIN); ?>" />
			</span>
		</div>
		<div class="clear"></div>
		<?php
	}
	public static function show_edit_taxonomy_field( $taxonomy ) {
		if ( $taxonomy->term_id ) {
			$attachment_id = get_option('mp62_mrm_taxonomy_image_' . $taxonomy->term_id);
		} else {
			$attachment_id = 0;
		}
		?>
		<tr class="form-field">
			<th scope="row"><label for="taxonomy_image"><?php _e('Image', MP62_TEXTDOMAIN); ?></label></th>
			<td>
				<input type="hidden" name="mp62_mrm_taxonomy_image_id" id="mp62_mrm_taxonomy_image_id" value="<?php echo $attachment_id; ?>">
				<span id="mp62_mrm_imageholder"><?php echo mp62_mrm_Utilities::show_image_box($attachment_id, 'mp62_mrm_icon_size', true); ?></span>
				<span id="mp62_mrm_buttons">
					<input id="mp62_mrm_upload_button" class="button" type="button" value="<?php _e('Choose or upload an image', MP62_TEXTDOMAIN); ?>" />
					<input id="mp62_mrm_delete_button" class="button" type="button" value="<?php _e('Delete this image', MP62_TEXTDOMAIN); ?>" />
				</span>
				<div class="clear"></div>
			</td>
		</tr>
		<?php
	}
	public static function add_taxonomy_columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name'),
			'tax_icon' => __('Image', MP62_TEXTDOMAIN ),
			'description' => __('Description'),
			'slug' => __('Slug'),
			'posts' => __('Posts')
			);
		return $new_columns;
	}
	public static function manage_taxonomy_column( $out, $column, $term_id ) {
		switch ( $column ) {
			case 'tax_icon':
				$attachment_id = get_option('mp62_mrm_taxonomy_image_' . $term_id);
				$out .= mp62_mrm_Utilities::show_image_box($attachment_id, 'mp62_mrm_icon_size', true);
				break;
			default:
				break;
		}
		return $out;    
	}
	public static function set_default_options() {
		$fonts_array = array(
			'Courier' => array('courier', 'internal'),
			'Helvetica' => array('helvetica', 'internal'),
			//'Symbol' => array('symbol', 'internal'),
			'Times-Roman' => array('times-roman', 'internal'),
			//'ZapfDingbats' => array('zapfdingbats', 'internal'),
		);
		add_option( 'mp62_mrm_fonts_list', $fonts_array );
	}
}
?>
