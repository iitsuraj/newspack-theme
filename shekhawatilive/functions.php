<?php
/**
 * Shekhawati functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Shekhawati
 */


if ( ! function_exists( 'newspack_shekhawatilive_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function newspack_shekhawatilive_setup() {
		// Remove the default editor styles
		remove_editor_styles();
		// Add child theme editor styles, compiled from `style-child-theme-editor.scss`.
		add_editor_style( 'style-editor.css' );
		// Adding custom paths
		// Pagination rule must come first
		add_rewrite_rule(
			'^(.+?)/news/page/?([0-9]{1,})/?$',
			'index.php?category_name=$matches[1]&paged=$matches[2]',
			'top'
		);

		// News category rule
		add_rewrite_rule(
			'^([^/]+)/news/?$',
			'index.php?category_name=$matches[1]',
			'top'
		);

		// Page rule
		add_rewrite_rule(
			'^([^/]+)/?$',
			'index.php?pagename=$matches[1]',
			'top'
		);
		if(function_exists('newspack_post_thumbnail_sizes_attr') && function_exists('newspack_custom_post_thumbnail_sizes_attr') ){
			remove_filter('wp_get_attachment_image_attributes', 'newspack_post_thumbnail_sizes_attr');
			add_filter('wp_get_attachment_image_attributes', 'newspack_custom_post_thumbnail_sizes_attr', 11);
		}
		add_filter('newspack_reader_activation_should_render_auth', '__return_false', 5);
		add_action('wp', 'add_last_modified_header');
		add_filter('wp_editor_set_quality', function ($quality) {
			return 20;
		});
	}
endif;
add_action( 'after_setup_theme', 'newspack_shekhawatilive_setup', 12 );

/**
 * Display custom color CSS in customizer and on frontend.
 */
function newspack_shekhawatilive_custom_colors_css_wrap() {
	// Only bother if we haven't customized the color.
	if ( ( ! is_customize_preview() && 'default' === get_theme_mod( 'theme_colors', 'default' ) ) || is_admin() ) {
		return;
	}
	require_once get_stylesheet_directory() . '/inc/child-color-patterns.php';
	?>

	<style type="text/css" id="custom-theme-colors-shekhawatilive">
		<?php echo newspack_shekhawatilive_custom_colors_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</style>
	<?php
}
add_action( 'wp_head', 'newspack_shekhawatilive_custom_colors_css_wrap' );

/**
 * Display custom font CSS in customizer and on frontend.
 */
function newspack_shekhawatilive_typography_css_wrap() {
	if ( is_admin() || ( ! get_theme_mod( 'font_body', '' ) && ! get_theme_mod( 'font_header', '' ) && ! get_theme_mod( 'accent_allcaps', true ) ) ) {
		return;
	}
	?>

	<style type="text/css" id="custom-theme-fonts-shekhawati">
		<?php echo newspack_shekhawatilive_custom_typography_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</style>

<?php
}
add_action( 'wp_head', 'newspack_shekhawatilive_typography_css_wrap' );

/**
 * Enqueue supplemental block editor styles.
 */
function newspack_shekhawatilive_editor_customizer_styles() {
	// Check for color or font customizations.
	$theme_customizations = '';
	require_once get_stylesheet_directory() . '/inc/child-color-patterns.php';

	if ( 'custom' === get_theme_mod( 'theme_colors' ) ) {
		// Include color patterns.
		$theme_customizations .= newspack_shekhawatilive_custom_colors_css();
	}

	if ( get_theme_mod( 'font_body', '' ) || get_theme_mod( 'font_header', '' ) || get_theme_mod( 'accent_allcaps', true ) ) {
		$theme_customizations .= newspack_shekhawatilive_custom_colors_css();
	}

	// If there are any, add those styles inline.
	if ( $theme_customizations ) {
		// Enqueue a non-existant file to hook our inline styles to:
		wp_register_style( 'newspack-shekhawatilive-editor-inline-styles', false );
		wp_enqueue_style( 'newspack-shekhawatilive-editor-inline-styles' );
		// Add inline styles:
		wp_add_inline_style( 'newspack-shekhawatilive-editor-inline-styles', $theme_customizations );
	}
}
add_action( 'enqueue_block_editor_assets', 'newspack_shekhawatilive_editor_customizer_styles' );

/**
 * Custom typography styles for child theme.
 */

require get_stylesheet_directory() . '/inc/child-typography.php';

/**
 * Add sidebar on all-pages.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function newspack_add_sidebar($classes)
{
	if (($key = array_search("no-sidebar", $classes)) !== false) {
		unset($classes[$key]);
	}

	$classes[] = 'has-sidebar';
	error_log(json_encode($classes));
	return $classes;
}
add_filter('body_class', 'newspack_add_sidebar', 999);

/**
 * Add custom sizes attribute to responsive image functionality for post thumbnails.
 *
 * @origin Newspack Theme 1.0
 *
 * @param array $attr  Attributes for the image markup.
 * @return array Value for use in post thumbnail 'sizes' attribute.
 */
function set_custom_image_attributes($attr, $required_widths = array(400, 800), $default_width = 400)
{
	// Ensure $attr is an array
	if (!is_array($attr)) {
		return $attr;
	}

	if (!isset($attr['src'])) {
		return $attr;
	}

	// Prepare an array to hold the filtered srcset
	$filtered_srcset = array();
	$original_url = $attr['src'];
	$base_url = preg_replace('/-\d+x\d+\.(jpg|png|webp)$/', '', $original_url);
	$base_url = preg_replace('/\.(jpg|png|webp)$/', '', $base_url);
	$file_extension = pathinfo($original_url, PATHINFO_EXTENSION);


	// Build the srcset based on required widths
	foreach ($required_widths as $width) {
		// Generate the new URL for the required width
		$new_url = sprintf('%s-%dx%d%s', $base_url, $width, (int)($width * 0.75), "." . $file_extension);  // Assuming 4:3 aspect ratio
		$filtered_srcset[] = $new_url . ' ' . $width . 'w';

		// Set src if this matches the default width
		if ($width == $default_width || $width === $default_width) {
			$attr['src'] = $new_url;
		}
	}

	// Rebuild the srcset with the newly generated items
	$attr['srcset'] = implode(',', $filtered_srcset);

	return $attr;
}

function newspack_post_thumbnail( $size = 'newspack-featured-image' ) {
	if ( ! newspack_can_show_post_thumbnail() ) {
		return;
	}

	$default_image_attributes = array(
		'loading'             => isset( $GLOBALS['newspack_after_first_featured_image'] ) ? 'lazy' : false, // Disable lazy loading for first featured image on the page.
		'data-hero-candidate' => isset( $GLOBALS['newspack_after_first_featured_image'] ) ? false : true, // Make this image a hero candidate for AMP prerendering.
		'fetchpriority'       => 'high',
	);
	$custom_image_attributes = array(
		'loading'             => isset( $GLOBALS['newspack_after_first_featured_image'] ) ? 'lazy' : false, // Disable lazy loading for first featured image on the page.
		'data-hero-candidate' => isset( $GLOBALS['newspack_after_first_featured_image'] ) ? false : true, // Make this image a hero candidate for AMP prerendering.
	);
	if ( is_singular() ) :
		?>

		<figure class="post-thumbnail">

			<?php

			// If using the behind or beside image styles, add the object-fit argument for AMP.
			if ( in_array( newspack_featured_image_position(), array( 'behind', 'beside' ) ) ) :

				the_post_thumbnail(
					$size,
					wp_parse_args(
						array(
							'object-fit' => 'cover',
						),
						$default_image_attributes
					)
				);
			else :

				if ( 'above' === newspack_featured_image_position() ) :
					the_post_thumbnail(
						$size,
						wp_parse_args(
							array(
								'layout' => 'responsive',
							),
							$default_image_attributes
						)
					);
				else :
					the_post_thumbnail( $size, $default_image_attributes );
				endif;

				newspack_post_thumbnail_caption();
			endif;
			?>

		</figure><!-- .post-thumbnail -->

	<?php else : ?>

		<figure class="post-thumbnail">
			<a class="post-thumbnail-inner" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php the_post_thumbnail( $size, $custom_image_attributes ); ?>
			</a>
			<?php if ( get_theme_mod( 'archive_show_captions' ) || get_theme_mod( 'archive_show_credits' ) ) : ?>
				<?php
				$featured_image_id = get_post_thumbnail_id();
				$caption           = wp_get_attachment_caption( $featured_image_id );
				$credit            = method_exists( 'Newspack\Newspack_Image_Credits', 'get_media_credit_string' ) && \Newspack\Newspack_Image_Credits::get_media_credit_string( $featured_image_id );
				if ( $caption || $credit ) :
					?>
					<figcaption>
						<?php if ( get_theme_mod( 'archive_show_captions' ) && $caption ) : ?>
							<?php echo esc_html( $caption ); ?>
						<?php endif; ?>
						<?php if ( get_theme_mod( 'archive_show_credits' ) && $credit ) : ?>
							<?php echo wp_kses_post( \Newspack\Newspack_Image_Credits::get_media_credit_string( get_post_thumbnail_id() ) ); ?>
						<?php endif; ?>
					</figcaption>
				<?php endif; ?>
			<?php endif; ?>
		</figure>

	<?php
	endif; // End is_singular().

	// Set a global variable to identify that the first featured image has been displayed.
	if ( ! isset( $GLOBALS['newspack_after_first_featured_image'] ) ) {
		$GLOBALS['newspack_after_first_featured_image'] = true;
	}
}

function newspack_custom_post_thumbnail_sizes_attr($attr)
{
	if ($attr["class"] === "custom-logo") {
		return $attr;
	}
//	if (is_admin()) {
//		return $attr;
//	}
	if (is_home() || is_front_page()) {
		return set_custom_image_attributes($attr, array(200, 400), 400);
	}
	if(is_page()) {
		$current_category = get_queried_object();
		if (!empty($current_category->post_name)) {
			$current_category = get_category_by_slug($current_category->post_name);
		}
		if ($current_category && isset($current_category->term_id)) {
			return set_custom_image_attributes($attr, array(200, 400), 400);
		}
		return $attr;
	}
	if (! is_singular()) {
		$attr['sizes'] = '(max-width: 34.9rem) calc(100vw - 2rem), (max-width: 48.8rem) calc(50vw), (min-width: 48.9rem) 190px';
		return set_custom_image_attributes($attr, array(200, 400), 400);
	}
	return $attr;
}


function add_last_modified_header()
{
	// Ensure headers are not already sent
    if (headers_sent()) {
        return;
    }
	// Helper function to get the last modified time for a post
    function get_last_modified_time_for_post($post_id = null)
    {
        return get_the_modified_time('D, d M Y H:i:s', $post_id) . ' GMT';
    }
	// Helper function to get the latest modified post
	function get_latest_modified_post($args = [])
	{
		$default_args = [
			'posts_per_page' => 1,
			'orderby' => 'modified',
			'order' => 'DESC',
			'post_type' => 'post',
			'post_status' => 'publish',
		];
		$args = wp_parse_args($args, $default_args);
		$posts = get_posts($args);
		return !empty($posts) ? $posts[0] : null;
	}
	if (is_single()) {
		$last_modified = get_last_modified_time_for_post(get_the_ID());
        header('Last-Modified: ' . $last_modified);
        return;
	} elseif (is_page()) {
		$args = [];
		$current_category = get_queried_object();
		if (!empty($current_category->post_name)) {
			$current_category = get_category_by_slug($current_category->post_name);
		}
		if ($current_category && isset($current_category->term_id)) {
			$args['cat'] = $current_category->term_id;
			$latest_post = get_latest_modified_post($args);
			if ($latest_post) {
				$last_modified = get_last_modified_time_for_post($latest_post->ID);
				header('Last-Modified: ' . $last_modified);
				return;
			}
		} else {
			$last_modified = get_the_modified_time('D, d M Y H:i:s') . ' GMT';
			header('Last-Modified: ' . $last_modified);
			return;
		}
	} elseif (is_archive()) {
		$args = [];

        if (is_category()) {
            $args['cat'] = get_queried_object_id();
        } elseif (is_author()) {
            $args['author'] = get_queried_object_id();
        } elseif (is_tag()) {
            $args['tag_id'] = get_queried_object_id();
        }

        $latest_post = get_latest_modified_post($args);
        if ($latest_post) {
            $last_modified = get_last_modified_time_for_post($latest_post->ID);
            header('Last-Modified: ' . $last_modified);
        }
        return;
	} elseif (is_home() || is_front_page()) {
		$latest_post = get_latest_modified_post();
        if ($latest_post) {
            $last_modified = get_last_modified_time_for_post($latest_post->ID);
            header('Last-Modified: ' . $last_modified);
        }
        return;
	}
}
// add_action('wp', 'add_last_modified_header');
