<?php
if ( ! is_user_logged_in() ) {
	auth_redirect();
}
/* Template Name: SitemapChecklist */
?>

<!doctype html>

<html class="no-js" <?php language_attributes(); ?>>

<head>
    <meta charset="utf-8">

    <!-- Force IE to use the latest rendering engine available -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>

    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">

	<?php wp_head(); ?>

    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">

</head>


<body>

<?php if ( class_exists( 'SitemapChecklist' ) ) {

	$sitemap = new SitemapChecklist();
	$sitemap->sitemapChecklist_display();

} ?>

<?php wp_footer(); ?>
</body>
</html> <!-- end page -->