<?php
/**
 * The Template for displaying all single posts.
 *
 * @package lsx-business-directory
 */

get_header(); ?>

<?php lsx_content_wrap_before(); ?>

<div id="primary" class="content-area <?php echo esc_attr( lsx_main_class() ); ?>">

	<?php lsx_content_before(); ?>

	<main id="main" class="site-main" role="main">

		<?php lsx_content_top(); ?>

		<?php if ( have_posts() ) : ?>

			<?php
			while ( have_posts() ) :
				the_post();
				?>

				<?php include LSX_BD_PATH . '/templates/content-custom-post-type.php'; ?>

			<?php endwhile; ?>

		<?php endif; ?>

		<?php lsx_content_bottom(); ?>

	</main><!-- #main -->

	<?php lsx_content_after(); ?>

	<?php
	if ( is_singular( 'post' ) ) {
		lsx_post_nav();
	}
	?>

	<?php
	if ( comments_open() ) {
		comments_template();
	}
	?>

</div><!-- #primary -->

<?php lsx_content_wrap_after(); ?>

<?php get_sidebar(); ?>

<?php
get_footer();
