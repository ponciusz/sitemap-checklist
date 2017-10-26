<?php
/*
Plugin Name: Sitemap Checklist
Plugin URI: http://albrycht.info
Description: Generate sitemap form pages, add your custom tests to make it more usefull
Version: 1.3
Author: Kamil Albrycht
Author URI: http://albrycht.info
License: UNLICENSED
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} // end if


class SitemapChecklist {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new SitemapChecklist();
		}

		return self::$instance;

	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	public function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			// 4.6 and older
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			// Add a filter to the wp 4.7 version attributes metabox
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data',
			array( $this, 'register_project_templates' )
		);


		// Add a filter to the template include to determine if the page has our
		// template assigned and return it's path
		add_filter(
			'template_include',
			array( $this, 'view_project_template' )
		);


		// Add your templates to this array.
		$this->templates = array(
			'templates/sitemap.php' => 'sitemapChecklist',
		);


		add_action( 'wp_enqueue_scripts', array( $this, 'sitemapChecklist_init' ), 99 );


	}


	public function sitemapChecklist_display( $posttype = 'page' ) {

		$frontpage_id = get_option( 'page_on_front' );
		$exclude[]    = $frontpage_id;

		// EXCLUDE
		$argsExclude    = [
			'post_type'  => $posttype,
			'parent'     => 0,
			'meta_key'   => 'hide_in_sitemap_checklist',
			'meta_value' => 'true',
		];
		$sitemapExclude = get_pages( $argsExclude );
		foreach ( $sitemapExclude as $page ) {
			$exclude[] = $page->ID;
		}
		// ---------

		$args = [
			'post_type' => $posttype,
			'parent'    => 0,
			'exclude'   => $exclude,
		];

		$sitemap = get_pages( $args );

		if ( ! empty( $sitemap ) ): ?>

            <div class="sitemapChecklist">

                <div class="sitemapChecklist__home">
                    <div class="sitemapChecklist__container">
                        <div class="sitemapChecklist__title">
                            <a href="<?php the_permalink( $frontpage_id ) ?>"><?php echo get_the_title( $frontpage_id ); ?></a>
                        </div>
                    </div>
                </div>

				<?php foreach ( $sitemap as $page ): ?>

					<?php $this->sitemapChecklist_generateChildren( 'page', $page->ID ); ?>

				<?php endforeach; ?>

            </div>

		<?php endif;

	}


	public function sitemapChecklist_generateChildren( $posttype = 'page', $id ) { ?>

		<?php $args = [
			'post_type' => $posttype,
			'parent'    => $id,
			array(
				'meta_key'   => 'hide_in_sitemap_checklist',
				'meta_value' => true,
				'compare'    => '!=',
			),
		];
		$children   = get_pages( $args ); ?>


        <div class="sitemapChecklist__pagewrap <?php if ( ! empty( $children ) ) {
			echo 'has-children';
		} ?>" title="<?php echo get_the_title( $id ); ?>">
            <div class="sitemapChecklist__container">
                <div class="sitemapChecklist__title">
                    <a target="_blank" href="<?php the_permalink( $id ) ?>"><?php echo get_the_title( $id ); ?></a>
                </div>

                <ul class="sitemapChecklist__details">
					<?php $this->sitemapChecklist_checkExcerpt( $id, 'Excerpt' ); ?><?php $this->sitemapChecklist_checkImage( $id, 'Image' ); ?><?php $this->sitemapChecklist_checkPageBuilder( $id, 'PB sections' ); ?>
                </ul>

                <a class="sitemapChecklist__edit" title="Edit <?php echo get_the_title( $id ); ?>" target="_blank" href="<?php echo get_edit_post_link( $id ); ?> "></a>
            </div>


			<?php if ( ! empty( $children ) ): ?>
                <div class="sitemapChecklist__children">
                    <button class="sitemapChecklist__children__toggle"></button>
					<?php foreach ( $children as $child ): ?><?php $this->sitemapChecklist_generateChildren( 'page', $child->ID ); ?><?php endforeach; ?>
                </div>
			<?php endif; ?>

        </div>

	<?php }

	public function sitemapChecklist_checkExcerpt( $id = null, $name ) {
		$test = get_the_excerpt( $id );
		$this->displayStatus( $test, $name );
	}

	public function sitemapChecklist_checkImage( $id = null, $name ) {
		$test = has_post_thumbnail( $id );
		$this->displayStatus( $test, $name );
	}

	public function sitemapChecklist_checkPageBuilder( $id = null, $name ) {
		$test = get_field( 'page_builder', $id );
		$this->displayStatus( $test, $name );
	}

	public function displayStatus( $bool, $name ) {
		if ( $bool ) {
			echo "<li>$name: <i class=\"status-ok\"></i></li>";

		} else {
			echo "<li>$name: <i class=\"status-fail\"></i></li>";
		}
	}

	/**
	 * Init JS & CSS
	 *
	 */
	public function sitemapChecklist_init() {

		if ( is_page( 'sitemap' ) ) {
			$csspath = plugin_dir_path( __FILE__ ) . 'dist/styles/style.min.css';
			wp_register_style( 'sitemapChecklist_css', plugins_url( 'dist/styles/style.min.css', __FILE__ ), [], filemtime( $csspath ), 'screen' );
			wp_enqueue_style( 'sitemapChecklist_css' );

			$csspathprint = plugin_dir_path( __FILE__ ) . 'dist/styles/print.min.css';
			wp_register_style( 'sitemapChecklist_css_print', plugins_url( 'dist/styles/print.min.css', __FILE__ ), [], filemtime( $csspathprint ), 'print' );
			wp_enqueue_style( 'sitemapChecklist_css_print' );

			$jspath = plugin_dir_path( __FILE__ ) . 'dist/js/scripts.min.js';
			wp_register_script( 'sitemapChecklist_js', plugins_url( 'dist/js/scripts.min.js', __FILE__ ), [ 'jquery' ], filemtime( $jspath ), true );
			wp_enqueue_script( 'sitemapChecklist_js' );
		}


	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );

		return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key, 'themes' );

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {

		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[ get_post_meta(
				$post->ID, '_wp_page_template', true
			) ] )
		) {
			return $template;
		}

		$file = plugin_dir_path( __FILE__ ) . get_post_meta(
				$post->ID, '_wp_page_template', true
			);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

}

add_action( 'plugins_loaded', array( 'SitemapChecklist', 'get_instance' ) );


function custom_meta_box_markup( $object ) {
	wp_nonce_field( basename( __FILE__ ), "meta-box-nonce" );
	?>
    <div>

        <label for="hide_in_sitemap_checklist">Hide</label>
		<?php
		$checkbox_value = get_post_meta( $object->ID, "hide_in_sitemap_checklist", true );

		if ( $checkbox_value == "" ) {
			?>
            <input name="hide_in_sitemap_checklist" type="checkbox" value="true">
			<?php
		} else if ( $checkbox_value == "true" ) {
			?>
            <input name="hide_in_sitemap_checklist" type="checkbox" value="true" checked>
			<?php
		}
		?>
    </div>
	<?php
}

function add_custom_meta_box() {
	add_meta_box( "demo-meta-box", "Hide Page in UPP Sitemap", "custom_meta_box_markup", "page", "side", "high", null );
}

add_action( "add_meta_boxes", "add_custom_meta_box" );


function save_custom_meta_box( $post_id, $post, $update ) {
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
	if ( isset( $_POST["hide_in_sitemap_checklist"] ) ) {
		$meta_box_checkbox_value = $_POST["hide_in_sitemap_checklist"];
	}
	update_post_meta( $post_id, "hide_in_sitemap_checklist", $meta_box_checkbox_value );
}

add_action( "save_post", "save_custom_meta_box", 10, 3 );