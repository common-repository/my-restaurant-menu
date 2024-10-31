<div id="mp62_mrm_render_header"><?php echo $header; ?></div>
<div id="mp62_mrm_render_main">
	<div id="mp62_mrm_render_col1">
		<?php
		foreach ( (array)$columns[1] as $pos => $item ) {
			if ( $item->ID == 0 ) {
				echo mp62_mrm_Utilities::render( $layout_id . 'menu-section.php', array( 'item' => $item ) );
			} else {
				echo mp62_mrm_Utilities::render( $layout_id . 'menu-item.php', array( 'item' => $item ) );
			}
		}
		?>
	</div>
	<div id="mp62_mrm_render_col2">
		<?php
		foreach ( (array)$columns[2] as $pos => $item  ) {
			if ( $item->ID == 0 ) {
				echo mp62_mrm_Utilities::render( $layout_id . 'menu-section.php', array( 'item' => $item ) );
			} else {
				echo mp62_mrm_Utilities::render( $layout_id . 'menu-item.php', array( 'item' => $item ) );
			}
		}
		?>
	</div>
</div>
<div id="mp62_mrm_render_footer"><?php echo $footer; ?></div>
