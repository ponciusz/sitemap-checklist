<?php
// Custom tests
function add_excerpt_testing( $content, $id = null ) {
	$test       = get_the_excerpt( $id );
	$showresult = new SitemapChecklist();

	return $showresult->displayStatus( $content, $test, 'Excerpt' );
}

add_filter( 'sitemapchecklist_add_test', 'add_excerpt_testing', 10, 2 );


function add_image_testing( $content, $id = null ) {
	$test       = has_post_thumbnail( $id );
	$showresult = new SitemapChecklist();

	return $showresult->displayStatus( $content, $test, 'Image' );
}

add_filter( 'sitemapchecklist_add_test', 'add_image_testing', 11, 2 );


function add_pb_testing( $content, $id = null ) {
	$test       = get_field( 'page_builder', $id );
	$showresult = new SitemapChecklist();

	return $showresult->displayStatus( $content, $test, 'PB Sections' );
}

add_filter( 'sitemapchecklist_add_test', 'add_pb_testing', 12, 2 );