jQuery(document).ready(function($){
	var custom_uploader;
	$('#mp62_mrm_upload_button').click(function(e) {
		e.preventDefault();

		//If the uploader object has already been created, reopen the dialog
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}

		//Extend the wp.media object
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: mp62_mrm_strings.label,
			button: {
				text: mp62_mrm_strings.button,
			},
		    library : {
				type : 'image'
		    },
			multiple: false
		});

		//When a file is selected, grab the URL and set it as the text field's value
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			$('#mp62_mrm_taxonomy_image_id').val( attachment.id );
			$('#mp62_mrm_taxonomy_image').attr( 'src', attachment.sizes.thumbnail.url );
		});

		//Open the uploader dialog
		custom_uploader.open();
	});
	$('#mp62_mrm_delete_button').click(function(e) {
		e.preventDefault();
		$('#mp62_mrm_taxonomy_image_id').val( 0 );
		$('#mp62_mrm_taxonomy_image').attr( 'src', mp62_mrm_strings.noimage_url );
	});
	$('#mp62_mrm_menu_items_inside').accordion({
		collapsible: true,
		heightStyle: 'content',
	});
	var drag_options = {
		helper: 'clone',
		cursor: 'move',
		revert: 'invalid',
		start: function() {
			$source = this.parentElement.id
			console.log( 'SOURCE FROM DRAG=' + $source );
		}
	};
	var drop_options = {
//		accept: '.ui-draggable',
		greedy: true,
		drop: function( event, ui ) {
			var draggable = ui.draggable;
			var droppable = $(this);
			console.log( 'SOURCE FROM DROP=' + $source );

			if ( droppable.hasClass( 'mp62_mrm_container' ) ) {
				if ( $source == 'mp62_mrm_items_free_inside' ) {
					$item = ui.draggable.clone();
					var free_label = prompt( mp62_mrm_strings.free_label );
					if ( free_label == '' ) {
						free_label = 'empty';
					}
					$item.find('span').text( free_label );
					$item.append('<div class="mp62_mrm_menu_icon_remove dashicons dashicons-dismiss"></div>');
					$item.append('<div class="mp62_mrm_menu_icon_edit dashicons dashicons-edit"></div>');
					$item.uniqueId();
					$( $item ).draggable( drag_options ).droppable( drop_options ).appendTo( this );
				}
				if ( $source.indexOf('mp62_mrm_menu_items_inside') > -1 ) {
					$item = ui.draggable.clone();
					$item.prepend('<div class="mp62_mrm_menu_icon_remove dashicons dashicons-dismiss"></div>');
					$( $item ).draggable( drag_options ).sortable().appendTo( this );
				}
				if ( $source == 'mp62_mrm_menu_col1_inside' ) {
					$item = ui.draggable;
					$( $item ).draggable( drag_options ).appendTo( this );
				}
				if ( $source == 'mp62_mrm_menu_col2_inside' ) {
					$item = ui.draggable;
					$( $item ).draggable( drag_options ).appendTo( this );
				}
				if ( $source.substring(0, 6) == 'ui-id-' ) {
					$item = ui.draggable;
					$( $item ).draggable( drag_options ).appendTo( this );
				}
				mp62_mrm_update_hidden_field();
			}
		},
	};
	$('#mp62_mrm_items_free_inside div.mp62_mrm_items_free').draggable( drag_options );
	$('#mp62_mrm_menu_items_inside div.mp62_mrm_items_item').draggable( drag_options );
	$('#mp62_mrm_menu_col1_inside div.mp62_mrm_items_item').draggable( drag_options );
	$('#mp62_mrm_menu_col2_inside div.mp62_mrm_items_item').draggable( drag_options );

	$('#mp62_mrm_menu_col1_inside').sortable();
	$('#mp62_mrm_menu_col2_inside').sortable();

	$('.mp62_mrm_container').droppable( drop_options );

	$( document ).on( 'click', '.mp62_mrm_menu_icon_edit', function() {
		$parent = $( this ).parent().children('span');
		var free_label = prompt( mp62_mrm_strings.free_label, $parent.text() );
		if ( free_label != null ) {
			$parent.text( free_label );
			mp62_mrm_update_hidden_field();
		}
	});

	$( document ).on( 'click', '.mp62_mrm_menu_icon_remove', function() {
		$parent = $( this ).parent();
		$parent.remove();
		mp62_mrm_update_hidden_field();
	});

	$('#post').submit(function() {
		if ( $('#mp62_mrm_menu_col1_values').length ) {
			mp62_mrm_update_hidden_field();
		}
	});
	function mp62_mrm_update_hidden_field() {
		temp1 = new Array();
		$('#mp62_mrm_menu_col1_inside .mp62_mrm_items_item').each(function() {
			current_id = $(this).attr('id');
			current_value = $(this).children('span').text();
			parent_id = this.parentElement.id;
			//temp1[ temp1.length ] = new Array( parent_id, current_id, current_value );
			temp1.push( new Array( parent_id, current_id, current_value ) );
		});
		$('#mp62_mrm_menu_col1_values').val( JSON.stringify( temp1 ) );
		console.log(temp1);

		temp2 = new Array();
		$('#mp62_mrm_menu_col2_inside .mp62_mrm_items_item').each(function() {
			current_id = $(this).attr('id');
			current_value = $(this).children('span').text();
			parent_id = this.parentElement.id;
			//temp2[ temp2.length ] = new Array( parent_id, current_id, current_value );
			temp2.push( new Array( parent_id, current_id, current_value ) );
		});
		$('#mp62_mrm_menu_col2_values').val( JSON.stringify( temp2 ) );
	}
	$('.mp62_mrm_color_picker').wpColorPicker();
	$('.mp62_mrm_input_int').numberMask({
		type: 'int',
		beforePoint: 3,
	});
});
function print_r(o) {
//	return JSON.stringify(o,null,'\t').replace(/\n/g,'<br>').replace(/\t/g,'&nbsp;&nbsp;&nbsp;');
	return JSON.stringify(o,null,'\t');
}
