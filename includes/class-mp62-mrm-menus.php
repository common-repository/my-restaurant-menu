<?php
class mp62_mrm_Menus {
	/**
	* Create the new Custom Fields meta box.
	*/
	public static function create_meta_box() {
		add_meta_box('menu-first-metabox',  __('Menu header', MP62_TEXTDOMAIN), 'mp62_mrm_Menus::print_first_metabox', 'mp62_mrm_menu', 'normal', 'high');
		add_meta_box('menu-second-metabox', __('Menu layout', MP62_TEXTDOMAIN), 'mp62_mrm_Menus::print_second_metabox', 'mp62_mrm_menu', 'normal', 'high');
		add_meta_box('menu-third-metabox',  __('Menu footer', MP62_TEXTDOMAIN), 'mp62_mrm_Menus::print_third_metabox', 'mp62_mrm_menu', 'normal', 'high');
		add_meta_box('menu-fourth-metabox', __('Shortcode', MP62_TEXTDOMAIN), 'mp62_mrm_Menus::print_fourth_metabox', 'mp62_mrm_menu', 'side', 'high');
		add_meta_box('menu-fifth-metabox', __('Web Menu Customization', MP62_TEXTDOMAIN), 'mp62_mrm_Menus::print_fifth_metabox', 'mp62_mrm_menu', 'normal', 'high');
		add_meta_box('menu-sixth-metabox', __('PDF Menu Customization', MP62_TEXTDOMAIN), 'mp62_mrm_Menus::print_sixth_metabox', 'mp62_mrm_menu', 'normal', 'high');
	}
	public static function print_first_metabox( $post ) {
		wp_nonce_field( 'update_mp62_mrm_fields','mp62_mrm_fields_nonce', true, true );
		$settings = array( 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => 6, 'teeny' => false );
		$content = get_post_meta( $post->ID, 'mp62_mrm_menu_header', true );
		wp_editor( $content, 'mp62_mrm_menu_header', $settings );
	}
	public static function print_second_metabox( $post ) {
		?>
		<div id="mp62_mrm_menu_master">
			<div id="mp62_mrm_menu_items">
				<h4><?php _e('Available items', MP62_TEXTDOMAIN); ?></h4>
				<div id="mp62_mrm_items_free_inside">
					<div class="mp62_mrm_items_item mp62_mrm_items_free mp62_mrm_container">
						<span><?php _e('Add a section', MP62_TEXTDOMAIN); ?></span>
					</div>
				</div>
				<div id="mp62_mrm_menu_items_inside">
					<?php
					$terms = get_terms( 'mp62_mrm_item_section' );
					foreach ( $terms as $term ) {
						echo '<h3 class="mp62_mrm_accordion">' . $term->name . '</h3>';
						echo '<div>';
							$args = array(
								'post_type' => 'mp62_mrm_item',
								'orderby' => 'menu_order title',
								'order' => 'ASC',
								'post_per_page' => -1,
								'nopaging' => true,
								'tax_query' => array(
									array(
										'taxonomy' => 'mp62_mrm_item_section',
										'field' => 'slug',
										'terms' => $term->slug,
									)
								),
							);
							$items = get_posts( $args );
							foreach ( $items as $item ) {
								//print_r($item);
								$img = get_the_post_thumbnail( $item->ID, 'mp62_mrm_icon_size' );
								echo '<div id="item_' . $item->ID . '" class="mp62_mrm_items_item">';
								echo '<span>' . $img . $item->post_title . '</span>';
								echo '</div>';
							}
						echo '</div>';
					}
					?>
				</div>
			</div>
			<div id="mp62_mrm_menu_main">
				<?php for ($n = 1; $n <= 2; $n++) { ?>
				<div id="mp62_mrm_menu_col<?php echo $n; ?>">
					<h4><?php _e('Menu column ' . $n, MP62_TEXTDOMAIN); ?></h4>
					<div id="mp62_mrm_menu_col<?php echo $n; ?>_inside" class="mp62_mrm_container">
						<?php
						$col_items = get_post_meta( $post->ID, 'mp62_mrm_menu_col' . $n . '_values', true );
						if ( !empty( $col_items ) ) {
							$old_section = 'mp62_mrm_menu_col' . $n . '_inside';
							$levels = 0;
							foreach ( $col_items as $pos => $col_item ) {
								if ( $old_section != $col_item[0] ) {
									echo '</div>';
									$levels--;
								}
								if ( 'item_' == substr( $col_item[1], 0, 5 ) ) {
									$item = get_post( substr( $col_item[1], 5) );
									$img = get_the_post_thumbnail( $item->ID, 'mp62_mrm_icon_size' );
									echo '<div id="item_' . $item->ID . '" class="mp62_mrm_items_item">';
									echo '<div class="mp62_mrm_menu_icon_remove dashicons dashicons-dismiss"></div>';
									echo '<span>' . $img . $item->post_title . '</span>';
									echo '</div>';
									$old_section = $col_item[0];
								} else {
									echo '<div class="mp62_mrm_items_item mp62_mrm_items_free mp62_mrm_container" id="' . $col_item[1] . '">';
									echo '<span>' . __($col_item[2], MP62_TEXTDOMAIN) . '</span>';
									echo '<div class="mp62_mrm_menu_icon_remove dashicons dashicons-dismiss"></div>';
									echo '<div class="mp62_mrm_menu_icon_edit dashicons dashicons-edit"></div>';
									$old_section = $col_item[1];
									$levels++;
									//echo '</div>';
								}
							}
							for ($l = 1; $l <= $levels; $l++) {
								echo '</div>';
							}
						}
						?>
					</div>
				</div>
				<?php } ?>
			</div>
			<div class="clear"></div>
		</div>
		<input type="hidden" id="mp62_mrm_menu_col1_values" name="mp62_mrm_menu_col1_values" value="<?php echo htmlspecialchars( json_encode( $col1_items ) ); ?>">
		<input type="hidden" id="mp62_mrm_menu_col2_values" name="mp62_mrm_menu_col2_values" value="<?php echo htmlspecialchars( json_encode( $col2_items ) ); ?>">
		<p><?php _e('SPIEGA', MP62_TEXTDOMAIN); ?></p>
		<?php
	}
	public static function print_third_metabox( $post ) {
		$settings = array( 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => 6, 'teeny' => false );
		$content = get_post_meta( $post->ID, 'mp62_mrm_menu_footer', true );
		wp_editor( $content, 'mp62_mrm_menu_footer', $settings );
	}
	public static function print_fourth_metabox( $post ) {
		$status = get_post_status( $post->ID );
		if ( $status != 'publish' ) {
			echo '<p>' . __('Once this menu is published, look here to find the shortcode you will use to display this menu in any post or page.', MP62_TEXTDOMAIN) . '</p>';
		} else {
			echo '<p>' . __('Copy and paste the snippet below into any post or page in order to display this menu.', MP62_TEXTDOMAIN) . '</p>';
			echo '<input id="mp62_mrm_menu_shortcode" type="text" value="[mrm menu=&quot;' . $post->ID . '&quot;]" readonly />';
		}
	}
	public static function show_css_boxes( $row_label, $field_prefix, $fields, $values ) {
		$fonts_array = get_option( 'mp62_mrm_fonts_list' );
		$font_family_list = array();
		foreach ( $fonts_array as $label => $data ) {
			$font_family_list[ $data[0] ] = $label;
		}
		$font_style_list = array(
			'normal' => __('Normal', MP62_TEXTDOMAIN),
			'italic' => __('Italic', MP62_TEXTDOMAIN),
			'bold' => __('Bold', MP62_TEXTDOMAIN),
			'bold_italic' => __('Bold Italic', MP62_TEXTDOMAIN),
		);
		$paper_size_list = array(
			'A3' => 'A3',
			'A4' => 'A4',
			'A5' => 'A5',
			'LETTER' => 'Letter',
			'LEGAL' => 'Legal',
			'8.5X11' => '8.5X11',
			'8.5X14' => '8.5X14',
			'11X17' => '11X17',
		);
		$orientation_list = array(
			'portrait' => __('Portrait', MP62_TEXTDOMAIN),
			'landscape' => __('Landscape', MP62_TEXTDOMAIN),
		);
		$format_default = array(
			'background-color' => array(
				'type' => 'color',
				'label' => __('Background Color', MP62_TEXTDOMAIN)
			),
			'color' => array(
				'type' => 'color',
				'label' => __('Color', MP62_TEXTDOMAIN)
			),
			'font-family' => array(
				'type' => 'select',
				'label' => __('Font Family', MP62_TEXTDOMAIN),
				'extra' => $font_family_list,
			),
			'font-style' => array(
				'type' => 'select',
				'label' => __('Font Style', MP62_TEXTDOMAIN),
				'extra' => $font_style_list,
			),
			'font-size' => array(
				'type' => 'num_imput',
				'label' => __('Font Size', MP62_TEXTDOMAIN),
			),
			'layout_id' => array(
				'type' => 'hidden',
			),
			'paper_size' => array(
				'type' => 'select',
				'label' => __('Paper Size', MP62_TEXTDOMAIN),
				'extra' => $paper_size_list,
			),
			'orientation' => array(
				'type' => 'select',
				'label' => __('Orientation', MP62_TEXTDOMAIN),
				'extra' => $orientation_list,
			),
		);
		?>
		<div class="mp62_mrm_css_customization_row">
			<div class="mp62_mrm_css_customization_left"><h4><?php echo $row_label; ?></h4></div>
			<div class="mp62_mrm_css_customization_right">
				<?php
				foreach ( $fields as $field ) {
					$format = $format_default[$field['name']];
				?>
				<div class="mp62_mrm_css_customization_right_cell">
					<p><?php echo $format['label']; ?></p>
					<?php
					if ( 'layout' == $field['selector'] ) {
						$name = $field_prefix . '[' . $field['selector'] . '][' . $field['name'] . ']';
						$value = $values[$field['selector']][$field['name']];
					} else {
						$name = $field_prefix . '[css][' . $field['selector'] . '][' . $field['name'] . ']';
						$value = $values['css'][$field['selector']][$field['name']];
					}
					switch ( $format['type'] ) {
						case 'color':
							echo '<input type="text" class="mp62_mrm_color_picker" name="' . $name . '" value="' . $value . '" />';
							break;
						case 'select':
							echo '<select name="' . $name . '">';
							mp62_mrm_Utilities::create_select( $value, $format['extra'] );
							echo '</select>';
							break;
						case 'num_imput':
							echo '<input type="text" size="3" class="mp62_mrm_input_int" name="' . $name . '" value="' . $value . '" />';
							break;
						case 'hidden':
							echo '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
							break;
					}
					?>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}
	public static function print_fifth_metabox( $post ) {
		$values = self::get_post_meta_with_default( $post->ID, 'mp62_mrm_rendering_html' );

		echo '<div id="mp62_mrm_css_customization">';
		$fields = array(
			array( 'selector' => '#mp62_mrm_render_main', 'name' => 'background-color' ),
			array( 'selector' => 'layout', 'name' => 'layout_id' ),
		);
		self::show_css_boxes( __('Layout', MP62_TEXTDOMAIN), 'mp62_mrm_rendering_html', $fields, $values );

		$fields = array(
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'font-family' ),
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'font-style' ),
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'font-size' ),
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'color' ),
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'background-color' ),
		);
		self::show_css_boxes( __('Section', MP62_TEXTDOMAIN), 'mp62_mrm_rendering_html', $fields, $values );

		$fields = array(
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'font-family' ),
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'font-style' ),
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'font-size' ),
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'color' ),
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'background-color' ),
		);
		self::show_css_boxes( __('Item title', MP62_TEXTDOMAIN), 'mp62_mrm_rendering_html', $fields, $values );

		$fields = array(
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'font-family' ),
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'font-style' ),
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'font-size' ),
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'color' ),
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'background-color' ),
		);
		self::show_css_boxes( __('Item price', MP62_TEXTDOMAIN), 'mp62_mrm_rendering_html', $fields, $values );
		echo '</div>';
	}
	public static function print_sixth_metabox( $post ) {
		$values = self::get_post_meta_with_default( $post->ID, 'mp62_mrm_rendering_pdf' );

		echo '<div id="mp62_mrm_css_customization">';
		$fields = array(
			array( 'selector' => '#mp62_mrm_render_main', 'name' => 'background-color' ),
			array( 'selector' => 'layout', 'name' => 'paper_size' ),
			array( 'selector' => 'layout', 'name' => 'orientation' ),
			array( 'selector' => 'layout', 'name' => 'layout_id' ),
		);
		self::show_css_boxes( __('Layout', MP62_TEXTDOMAIN), 'mp62_mrm_rendering_pdf', $fields, $values );

		$fields = array(
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'font-family' ),
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'font-style' ),
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'font-size' ),
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'color' ),
			array( 'selector' => '.mp62_mrm_render_section', 'name' => 'background-color' ),
		);
		self::show_css_boxes( __('Section', MP62_TEXTDOMAIN), 'mp62_mrm_rendering_pdf', $fields, $values );

		$fields = array(
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'font-family' ),
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'font-style' ),
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'font-size' ),
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'color' ),
			array( 'selector' => '.mp62_mrm_render_item_title', 'name' => 'background-color' ),
		);
		self::show_css_boxes( __('Item title', MP62_TEXTDOMAIN), 'mp62_mrm_rendering_pdf', $fields, $values );

		$fields = array(
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'font-family' ),
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'font-style' ),
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'font-size' ),
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'color' ),
			array( 'selector' => '.mp62_mrm_render_item_price', 'name' => 'background-color' ),
		);
		self::show_css_boxes( __('Item price', MP62_TEXTDOMAIN), 'mp62_mrm_rendering_pdf', $fields, $values );
		echo '</div>';
	}
	public static function save_custom_fields( $post_id ) {
		if ( !empty($_POST) && check_admin_referer('update_mp62_mrm_fields','mp62_mrm_fields_nonce') ) {
			update_post_meta( $post_id, 'mp62_mrm_menu_header', trim( $_POST['mp62_mrm_menu_header'] ) );

			$tmp_array = json_decode( stripslashes( $_POST['mp62_mrm_menu_col1_values'] ) );
			update_post_meta( $post_id, 'mp62_mrm_menu_col1_values', $tmp_array );

			$tmp_array = json_decode( stripslashes( $_POST['mp62_mrm_menu_col2_values'] ) );
			update_post_meta( $post_id, 'mp62_mrm_menu_col2_values', $tmp_array );

			update_post_meta( $post_id, 'mp62_mrm_menu_footer', trim( $_POST['mp62_mrm_menu_footer'] ) );

			update_post_meta( $post_id, 'mp62_mrm_rendering_html', $_POST['mp62_mrm_rendering_html'] );
			update_post_meta( $post_id, 'mp62_mrm_rendering_pdf', $_POST['mp62_mrm_rendering_pdf'] );
		}
	}
	public static function extract_menu_data( $post_id ) {
		$header = get_post_meta( $post_id, 'mp62_mrm_menu_header', true );
		$how_many_columns = 0;
		$columns = array();
		for ($n = 1; $n <= 2; $n++) {
			$col_items = get_post_meta( $post_id, 'mp62_mrm_menu_col' . $n . '_values', true );
			if ( !empty( $col_items ) ) {
				$how_many_columns += $n;
				foreach ( $col_items as $pos => $col_item ) {
					$tmp_item = new stdClass;
					if ( 'item_' == substr( $col_item[1], 0, 5 ) ) {
						$item = get_post( substr( $col_item[1], 5) );
						$tmp_item->ID = $item->ID;
						$tmp_item->title = $item->post_title;
						$tmp_item->image = get_the_post_thumbnail( $item->ID, 'thumbnail', array( 'class' => 'mp62_mrm_render_item_image' ) );
						$tmp_item->content = $item->post_content;
						$tmp_item->price = get_post_meta($item->ID, 'mp62_mrm_price', true);;
					} else {
						$tmp_item->ID = 0;
						$tmp_item->title = $col_item[2];
					}
					$columns[$n][] = $tmp_item;
				}
			}
		}
		$footer = get_post_meta( $post_id, 'mp62_mrm_menu_footer', true );
		return array( 'header' => $header, 'columns' => $columns, 'footer' => $footer );
	}
	public static function show_menu( $content ) {
		global $post;
		if ( 'mp62_mrm_menu' != $post->post_type ) {
			return $content;
		}
		$layout = self::get_post_meta_with_default( $post->ID, 'mp62_mrm_rendering_html' );
		$layout_id = $layout['layout']['layout_id'];
		$data = self::extract_menu_data( $post->ID );
		$data['layout_id'] = $layout_id;
		$content .= self::create_css_block( $post->ID, 'mp62_mrm_rendering_html', 'my-restaurant-menu-frontend.css' );
		$content .= mp62_mrm_Utilities::render( $layout_id . 'menu.php', $data );
		return $content;
	}
	public static function menu_shortcode( $atts ) {
		$args = shortcode_atts( array(
			'menu' => '0',
		), $atts );
		$layout = self::get_post_meta_with_default( $args['menu'], 'mp62_mrm_rendering_html' );
		$layout_id = $layout['layout']['layout_id'];
		$data = self::extract_menu_data( $args['menu'] );
		$data['layout_id'] = $layout_id;

		$output = self::create_css_block( $args['menu'], 'mp62_mrm_rendering_html', 'my-restaurant-menu-frontend.css' );
		$output .= mp62_mrm_Utilities::render( $layout_id . 'menu.php', $data );
		return $output;
	}
	public static function generate_pdf() {
		if (! ( isset( $_GET['post'] ) || isset( $_POST['post'] )  || ( isset( $_REQUEST['action'] ) && 'generate_pdf' == $_REQUEST['action'] ) ) ) {
			wp_die( __('No post to generate PDF has been supplied!', MP62_TEXTDOMAIN) );
		}
		// Get the original post
		$post_id = ( isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post'] );
		$post = get_post($post_id);
		$filename = sanitize_file_name( $post->post_name . '.pdf' );

		//extract( self::extract_menu_data( $post_id ) );
		$layout = self::get_post_meta_with_default( $post_id, 'mp62_mrm_rendering_pdf' );
		$layout_id = $layout['layout']['layout_id'];
		$paper_size = $layout['layout']['paper_size'];
		$orientation = $layout['layout']['orientation'];
		$data = self::extract_menu_data( $post->ID );
		$data['layout_id'] = $layout_id;

		require_once( MP62_MRM_PLUGIN_PATH . '/dompdf/dompdf_config.inc.php' );
		$html = '<html>';
		$html .= '<head>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">';
		$html .= self::create_css_block( $post->ID, 'mp62_mrm_rendering_pdf', 'my-restaurant-menu-frontend-pdf.css' );
		$html .= '</head>';
		$html .= '<body>';
		$html .= '<div class="mp62_mrm_menu">';
		$html .= mp62_mrm_Utilities::render( $layout_id . 'menu.php', $data );
		$html .= '</div>';
		$html .= '</body>';
		$html .= '</html>';
//		file_put_contents( MP62_MRM_PLUGIN_PATH . '/tmp.html', $html );
		$dompdf = new DOMPDF();
		$dompdf->load_html( $html );
		$dompdf->set_paper( $paper_size, $orientation );
		$dompdf->render();
		$dompdf->stream($filename, array('Attachment' => 0));
	}
	public static function get_post_meta_with_default( $post_id, $meta ) {
		$defaults = array(
			'layout' => array(
				'layout_id' => '01',
				'paper_size' => 'A4',
				'orientation' => 'portrait',
			),
			'css' => array(
				'#mp62_mrm_render_main' => array(
					'background-color' => '#FFFFFF',
				),
				'.mp62_mrm_render_section' => array(
					'font-family' => 'Helvetica',
					'font-style' => 'bold',
					'font-size' => '30',
					'color' => '#000000',
					'background-color' => '#FFFFFF',
				),
				'.mp62_mrm_render_item_title' => array(
					'font-family' => 'Helvetica',
					'font-style' => 'bold',
					'font-size' => '18',
					'color' => '#000000',
					'background-color' => '#FFFFFF',
				),
				'.mp62_mrm_render_item_price' => array(
					'font-family' => 'Helvetica',
					'font-style' => 'normal',
					'font-size' => '18',
					'color' => '#000000',
					'background-color' => '#FFFFFF',
				),
			),
		);
		$values = shortcode_atts( $defaults, get_post_meta( $post_id, $meta, true ) );
		return $values;
	}
	public static function create_css_block( $post_id, $meta, $import_css = false ) {
		$values = self::get_post_meta_with_default( $post_id, $meta );
		$output = '<style type="text/css">' . "\n";
		if ( $import_css ) {
			$output .= file_get_contents( MP62_MRM_PLUGIN_PATH . '/css/' . $import_css );
		}
		foreach ( $values['css'] as $key1 => $sub ) {
			$output .= "$key1 {\n";
			foreach ( $sub as $key2 => $val ) {
				switch ( $key2 ) {
					case 'font-style':
						if ( in_array( $val, array( 'bold', 'bold_italic' ) ) ) {
							$output .= "\tfont-weight: bold !important;\n";
						}
						if ( in_array( $val, array( 'italic', 'bold_italic' ) ) ) {
							$output .= "\tfont-style: italic !important;\n";
						}
						break;
					case 'font-size':
						$output .= "\t$key2: $val" . "px;\n";
						break;
					default:
						$output .= "\t$key2: $val;\n";
						break;
				}
			}
			$output .= "}\n";
		}
		$output .= "</style>\n";
		return $output;
	}
}
