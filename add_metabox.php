<?php

function sitemap_checklist_metabox_exclude( $object ) {
	wp_nonce_field( basename( __FILE__ ), "meta-box-nonce" );
	?>
	<div>

		<label for="exclude_from_sitemap_checklist">Exclude </label>
		<?php
		$checkbox_value = get_post_meta( $object->ID, "exclude_from_sitemap_checklist", true );

		if ( $checkbox_value == "" ) {
			?>
			<input name="exclude_from_sitemap_checklist" type="checkbox" value="true">
			<?php
		} else if ( $checkbox_value == "true" ) {
			?>
			<input name="exclude_from_sitemap_checklist" type="checkbox" value="true" checked>
			<?php
		}
		?>
	</div>
	<?php
}

function add_sitemap_checklist_metabox_exclude() {
	add_meta_box( "demo-meta-box", "Exclude form SitemapChecklist", "sitemap_checklist_metabox_exclude", "page", "side", "high", null );
}
add_action( "add_meta_boxes", "add_sitemap_checklist_metabox_exclude" );


function save_sitemap_checklist_metabox_exclude( $post_id, $post, $update ) {
	if ( ! isset( $_POST["meta-box-nonce"] ) || ! wp_verify_nonce( $_POST["meta-box-nonce"], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	if ( ! current_user_can( "edit_post", $post_id ) ) {
		return $post_id;
	}

	if ( defined( "DOING_AUTOSAVE" ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	$slug = "page";
	if ( $slug != $post->post_type ) {
		return $post_id;
	}


	$meta_box_checkbox_value = "";
	if ( isset( $_POST["exclude_from_sitemap_checklist"] ) ) {
		$meta_box_checkbox_value = $_POST["exclude_from_sitemap_checklist"];
	}
	update_post_meta( $post_id, "exclude_from_sitemap_checklist", $meta_box_checkbox_value );
}

add_action( "save_post", "save_sitemap_checklist_metabox_exclude", 10, 3 );