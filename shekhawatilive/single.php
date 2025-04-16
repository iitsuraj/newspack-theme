<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Newspack
 */

get_header();
?>

	<section id="primary" class="content-area <?php echo esc_attr( newspack_get_category_tag_classes( get_the_ID() ) ); ?>">
		<main id="main" class="site-main">

			<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();
				// Check if the post belongs to the "video" category
				$hide_thumbnail = has_category('video');
				// Template part for large featured images.
				if (! $hide_thumbnail && in_array( newspack_featured_image_position(), array( 'large', 'behind', 'beside', 'above' ) ) ) :
					get_template_part( 'template-parts/post/large-featured-image' );
				else :
				?>
					<header class="entry-header">
						<?php get_template_part( 'template-parts/header/entry', 'header' ); ?>
					</header>

				<?php endif; ?>

					<?php
					if ( is_active_sidebar( 'article-1' ) ) {
						dynamic_sidebar( 'article-1' );
					}

					// Place smaller featured images inside of 'content' area.
					if (! $hide_thumbnail && 'small' === newspack_featured_image_position() ) :
						newspack_post_thumbnail();
					endif;

					get_template_part( 'template-parts/content/content-single', 'single' );

					newspack_previous_next();

					?>

			<?php endwhile; ?>

		</main><!-- #main -->
		<?php get_sidebar(); ?>
	</section><!-- #primary -->

<?php
get_footer();
