<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Newspack
 */
get_header();
$q = get_queried_object();
$feature_latest_post = get_theme_mod( 'archive_feature_latest_post', true );
$show_excerpt        = get_theme_mod( 'archive_show_excerpt', false );
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">
			<header class="page-header">
				<h1 class="page-title">
					<span><?php echo $q->name ?></span>
				</h1>
				<?php do_action('newspack_theme_below_archive_title'); ?>
			</header>

			<?php do_action( 'before_archive_posts' ); ?>
			<?php
			if ( have_posts() ) :
				$post_count = 0;

				// Start the Loop.
				while ( have_posts() ) :
					$post_count++;
					the_post();

					// Check if you're on the first post of the first page and if it should be styled differently, or if excerpts are enabled.
					if ( ( 1 === $post_count && 0 === get_query_var( 'paged' ) && true === $feature_latest_post ) || true === $show_excerpt ) {
						get_template_part( 'template-parts/content/content', 'excerpt' );
					} else {
						get_template_part( 'template-parts/content/content', 'archive' );
					}

					do_action( 'after_archive_post', $post_count );
					// End the loop.
				endwhile;

				// Previous/next page navigation.
				newspack_the_posts_navigation();

			// If no content, include the "No posts found" template.
			else :
				get_template_part( 'template-parts/content/content', 'none' );

			endif;
			?>
		</main><!-- #main -->
		<?php
		$archive_layout = get_theme_mod( 'archive_layout', 'default' );
		if ( 'default' === $archive_layout ) {
			get_sidebar();
		}
		?>
	</section><!-- #primary -->

<?php
get_footer();
