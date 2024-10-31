<?php
class mp62_mrm_Settings {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	public static $options;

	/**
	 * Options page callback
	 */
	public static function create_admin_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e('MRM Settings', MP62_TEXTDOMAIN); ?></h2>
			<?php settings_errors(); ?>
			
			<form method="post" action="options.php" id="mp62_mrm_settings_form">
				<ul>
					<li><a href="#mp62_wpb_table1"><?php _e('Fonts Settings', MP62_TEXTDOMAIN); ?></a></li>
				</ul>
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'mp62_mrm_option_group' );
				mp62_mrm_Settings::custom_do_settings_sections( 'mp62-wpb-setting-admin' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}
	/**
	 * Register and add settings
	 */
	public static function page_init() {
		$fonts_array = get_option( 'mp62_mrm_fonts_list' );
		register_setting(
			'mp62_mrm_option_group', // Option group
			'mp62_mrm_fonts_list', // Option name
			'mp62_mrm_Settings::sanitize' // Sanitize
		);
		add_settings_section(
			'fonts_section_id', // ID
			null, // Title
			null, // Callback
			'mp62-wpb-setting-admin' // Page
		);
		foreach ( $fonts_array as $label => $data ) {
			add_settings_field(
				$label, // ID
				null, // Title
				'mp62_mrm_Settings::font_callback', // Callback
				'mp62-wpb-setting-admin', // Page
				'fonts_section_id', // Section
				array( 'id' => $label, 'value' => $data, 'label' =>  $label )
			);
		}
		add_settings_field(
			'mp62_mrm_null', // ID
			null, // Title
			'mp62_mrm_Settings::null_callback', // Callback
			'mp62-wpb-setting-admin', // Page
			'fonts_section_id' // Section
		);
		add_settings_field(
			'mp62_mrm_font_url', // ID
			null, // Title
			'mp62_mrm_Settings::textlong_callback', // Callback
			'mp62-wpb-setting-admin', // Page
			'fonts_section_id', // Section
			array( 'id' => 'mp62_mrm_font_url', 'label' => __('Font URL', MP62_TEXTDOMAIN) )
		);
	}
	public static function font_callback( $args ) {
		$id = $args['id'];
		$value = $args['value'];
		$label = esc_attr( $args['label'] );
		?>
		<div class="mp62_mrm_fontcard">
			<span style="font-family: '<?php echo $label; ?>';"><?php echo $label; ?></span>&nbsp;
			<input type="hidden" name="mp62_mrm_fonts_list[<?php echo $id; ?>][]" value="<?php echo $value[0]; ?>" />
			<input type="hidden" name="mp62_mrm_fonts_list[<?php echo $id; ?>][]" value="<?php echo $value[1]; ?>" />
			<div class="mp62_mrm_menu_icon_remove dashicons dashicons-dismiss"></div>
		</div>
		<?php
	}
	public static function textlong_callback( $args ) {
		$id = $args['id'];
		$label = esc_attr( $args['label'] );
		?>
		<div>
			<label class="mp62_mrm_settings_label"><?php echo $label; ?></label>
			<input type="text" name="mp62_mrm_fonts_list[<?php echo $id; ?>]" value="" size="70" />
		</div>
		<div>
			<span class="mp62_mrm_settings_label">&nbsp;</span>
			<span class="description">
				<?php _e('Google Font Url, something like <code>http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic</code>', MP62_TEXTDOMAIN); ?>
			</span>
		</div>
		<div>
			<span class="mp62_mrm_settings_label">&nbsp;</span>
			<span class="description">
				<?php _e('Font weight up to 500 is considered <code>normal</code>, after 500 is considered <code>bold</code>.', MP62_TEXTDOMAIN); ?>
			</span>
		</div>
		<div>
			<span class="mp62_mrm_settings_label">&nbsp;</span>
			<span class="description">
				<?php _e('If <code>bold</code>, <code>italic</code> or <code>bold italic</code> is not specified, it is used the <code>normal</code> style.', MP62_TEXTDOMAIN); ?>
			</span>
		</div>
		<div>
			<span class="mp62_mrm_settings_label">&nbsp;</span>
			<span class="description">
				<?php _e('When saving, the fonts are downloaded and the metrics for the PDF generated, so please be patient :-).', MP62_TEXTDOMAIN); ?>
			</span>
		</div>

		<?php
	}
	public static function null_callback( $args ) {
		?>
		<div><br><br></div>
		<?php
	}
	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public static function sanitize( $input ) {
		$new_url  = $input['mp62_mrm_font_url'];
		unset( $input['mp62_mrm_font_url'] );
		if ( $new_url != '' ) {
			$font_detail = self::extract_data_from_google_fonts( $new_url );
			$new_name = $font_detail['font-family'];
			unset( $font_detail['font-family'] );

			// Create font metric for PDF
			require_once( MP62_MRM_PLUGIN_PATH . '/dompdf/dompdf_config.inc.php' );
			Font_Metrics::init();
			$entry = array();
			foreach ( $font_detail as $style => $data ) {
				$font = Font::load( $data['filename'] . '.' . $data['extension'] );
				$font->parse();
				$font->saveAdobeFontMetrics( $data['filename'] . '.ufm' );
				$entry[$style] = DOMPDF_FONT_DIR . basename( $data['filename'] );
			}
			// Check if defined all 4 style, otherwise fill with default
			foreach ( array('normal', 'bold', 'italic', 'bold_italic') as $style ) {
				if ( !isset($entry[$style]) ) {
					$entry[$style] = $entry['normal'];
				}
			}
			Font_Metrics::set_font_family($new_name, $entry);
			Font_Metrics::save_font_families();
		}
		if ( $new_name != '' and $new_url != '' ) {
			$input[$new_name] = array(
				strtolower( $new_name ),
				$new_url
			);
		}
		$handle = fopen( MP62_MRM_PLUGIN_PATH . '/css/my-restaurant-menu-fonts.css', 'wt' );
		foreach ( $input as $label => $data ) {
			if ( $data[1] != 'internal' ) {
				fwrite( $handle, '@import url(' . $data[1] . ');' . "\n" );
			}
		}
		fclose( $handle );
		return $input;
	}
	public static function custom_do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[$page] ) )
			return;

		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			if ( $section['title'] )
				echo "<h3>{$section['title']}</h3>\n";

			if ( $section['callback'] )
				call_user_func( $section['callback'], $section );

			if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
				continue;
			mp62_mrm_Settings::custom_do_settings_fields( $page, $section['id'] );
		}
	}
	public static function custom_do_settings_fields($page, $section) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[$page][$section] ) )
			return;

		foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
			call_user_func($field['callback'], $field['args']);
		}
	}
	public static function extract_data_from_google_fonts($url) {
		// extract data from google font url for PDF
		$data = file_get_contents( $url );
		$data = str_replace( array("\n", "\t", "\r"), '', $data );
		preg_match_all( "/{([^{|}]*)}/", $data, $fontfaces );
		$result = array();
		foreach ( $fontfaces[1] as $fontface ) {
			$tokens = explode( ';', $fontface );
			$tokens = array_map( 'trim', $tokens );
			$partial = array();
			foreach ( $tokens as $token ) {
				if ( $token != '' ) {
					$tmp = explode( ':', $token, 2 );
					$partial[$tmp[0]] = trim($tmp[1], " '\t\n\r\0\x0B");
				}
			}
			if ( $partial['font-weight'] > 500 ) {
				$partial['font-weight'] = 'bold';
			} else {
				$partial['font-weight'] = 'normal';
			}
			preg_match_all( "/\((.*?)\)/", $partial['src'], $tmp );
			$partial['local1'] = trim( $tmp[1][0], "'" );
			$partial['local2'] = trim( $tmp[1][1], "'" );
			$partial['src'] = $tmp[1][2];
			$extension = substr( strrchr( $partial['src'], '.' ), 1 );
			$partial['filename'] = MP62_MRM_PLUGIN_PATH . '/dompdf/lib/fonts/' . $partial['local2'];
			$local_path = $partial['filename'] . '.' . $extension;
			$response = wp_safe_remote_get( $partial['src'], array( 'timeout' => 300, 'stream' => true, 'filename' => $local_path ) );
			$result['font-family'] = $partial['font-family'];
			if ( $partial['font-style'] == 'normal' && $partial['font-weight'] == 'normal' ) {
				$key = 'normal';
			} elseif ( $partial['font-style'] == 'normal' && $partial['font-weight'] == 'bold' ) {
				$key = 'bold';
			} elseif ( $partial['font-style'] == 'italic' && $partial['font-weight'] == 'normal' ) {
				$key = 'italic';
			} elseif ( $partial['font-style'] == 'italic' && $partial['font-weight'] == 'bold' ) {
				$key = 'bold_italic';
			} else {
				$key = 'ERROR';
			}
			$result[ $key ] = array(
				'src' => $partial['src'],
				'local1' => $partial['local1'],
				'local2' => $partial['local2'],
				'filename' => $partial['filename'],
				'extension' => $extension,
			);
		}
		return $result;
	}
}
?>
