<?php

/**
 * Template part for displaying post archives and search results
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Newspack
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php newspack_post_thumbnail('newspack-archive-image'); ?>

	<div class="entry-container">
		<header class="entry-header">
			<?php the_title(sprintf('<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h2>'); ?>
		</header><!-- .entry-header -->
		<div class="entry-content">
			<?php the_excerpt(); ?>
		</div>
		<div class="entry-meta">
			<?php
			newspack_posted_by();
			newspack_posted_on();
			do_action('newspack_theme_entry_meta');
			?>
		</div><!-- .meta-info -->
	</div><!-- .entry-container -->
</article><!-- #post-${ID} -->