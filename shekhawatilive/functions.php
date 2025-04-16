<?php

/**
 * Shekhawati functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Shekhawati
 */


if (! function_exists('newspack_shekhawatilive_setup')) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function newspack_shekhawatilive_setup()
	{
		// Remove the default editor styles
		remove_editor_styles();
		// Add child theme editor styles, compiled from `style-child-theme-editor.scss`.
		add_editor_style('style-editor.css');
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
		add_filter('perfmatters_delay_js_timeout', function ($timeout) {
			return '5';
		});
		add_filter('jetpack_top_posts_days', 'jetpackme_top_posts_timeframe');
		add_filter('jetpack_relatedposts_filter_date_range', 'mtz_related_posts_limit');
		add_filter('onesignal_send_notification', 'prevent_onesignal_replacing_notifications');

		if (function_exists('newspack_post_thumbnail_sizes_attr') && function_exists('newspack_custom_post_thumbnail_sizes_attr')) {
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
add_action('after_setup_theme', 'newspack_shekhawatilive_setup', 12);
function jetpackme_top_posts_timeframe()
{
	return '3';
}
function mtz_related_posts_limit($date_range)
{
	return array(
		'from' => strtotime('-3 days'),
		'to'   => time(),
	);
}
function prevent_onesignal_replacing_notifications($fields)
{
	// Add a unique tag to prevent collapsing/overwriting
	$unique_id = uniqid('notif_', true);
	$fields['web_push_topic'] = $unique_id;
	$fields['chrome_web_notification_tag'] = $unique_id;
	// Optional: disable renotify so users don’t hear the sound again
	$fields['chrome_web_notification_renotify'] = true;
	return $fields;
}
/**
 * Display custom color CSS in customizer and on frontend.
 */
function newspack_shekhawatilive_custom_colors_css_wrap()
{
	// Only bother if we haven't customized the color.
	if ((! is_customize_preview() && 'default' === get_theme_mod('theme_colors', 'default')) || is_admin()) {
		return;
	}
	require_once get_stylesheet_directory() . '/inc/child-color-patterns.php';
?>

	<style type="text/css" id="custom-theme-colors-shekhawatilive">
		<?php echo newspack_shekhawatilive_custom_colors_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</style>
<?php
}
add_action('wp_head', 'newspack_shekhawatilive_custom_colors_css_wrap');

/**
 * Display custom font CSS in customizer and on frontend.
 */
function newspack_shekhawatilive_typography_css_wrap()
{
	if (is_admin() || (! get_theme_mod('font_body', '') && ! get_theme_mod('font_header', '') && ! get_theme_mod('accent_allcaps', true))) {
		return;
	}
?>

	<style type="text/css" id="custom-theme-fonts-shekhawati">
		<?php echo newspack_shekhawatilive_custom_typography_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</style>

	<?php
}
add_action('wp_head', 'newspack_shekhawatilive_typography_css_wrap');

/**
 * Enqueue supplemental block editor styles.
 */
function newspack_shekhawatilive_editor_customizer_styles()
{
	// Check for color or font customizations.
	$theme_customizations = '';
	require_once get_stylesheet_directory() . '/inc/child-color-patterns.php';

	if ('custom' === get_theme_mod('theme_colors')) {
		// Include color patterns.
		$theme_customizations .= newspack_shekhawatilive_custom_colors_css();
	}

	if (get_theme_mod('font_body', '') || get_theme_mod('font_header', '') || get_theme_mod('accent_allcaps', true)) {
		$theme_customizations .= newspack_shekhawatilive_custom_colors_css();
	}

	// If there are any, add those styles inline.
	if ($theme_customizations) {
		// Enqueue a non-existant file to hook our inline styles to:
		wp_register_style('newspack-shekhawatilive-editor-inline-styles', false);
		wp_enqueue_style('newspack-shekhawatilive-editor-inline-styles');
		// Add inline styles:
		wp_add_inline_style('newspack-shekhawatilive-editor-inline-styles', $theme_customizations);
	}
}
add_action('enqueue_block_editor_assets', 'newspack_shekhawatilive_editor_customizer_styles');

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

function newspack_post_thumbnail($size = 'newspack-featured-image')
{
	if (! newspack_can_show_post_thumbnail()) {
		return;
	}

	$default_image_attributes = array(
		'loading'             => isset($GLOBALS['newspack_after_first_featured_image']) ? 'lazy' : false, // Disable lazy loading for first featured image on the page.
		'data-hero-candidate' => isset($GLOBALS['newspack_after_first_featured_image']) ? false : true, // Make this image a hero candidate for AMP prerendering.
		'fetchpriority'       => 'high',
	);
	$custom_image_attributes = array(
		'loading'             => isset($GLOBALS['newspack_after_first_featured_image']) ? 'lazy' : false, // Disable lazy loading for first featured image on the page.
		'data-hero-candidate' => isset($GLOBALS['newspack_after_first_featured_image']) ? false : true, // Make this image a hero candidate for AMP prerendering.
	);
	if (is_singular()) :
	?>

		<figure class="post-thumbnail">

			<?php

			// If using the behind or beside image styles, add the object-fit argument for AMP.
			if (in_array(newspack_featured_image_position(), array('behind', 'beside'))) :

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

				if ('above' === newspack_featured_image_position()) :
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
					the_post_thumbnail($size, $default_image_attributes);
				endif;

				newspack_post_thumbnail_caption();
			endif;
			?>

		</figure><!-- .post-thumbnail -->

	<?php else : ?>

		<figure class="post-thumbnail">
			<a class="post-thumbnail-inner" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php the_post_thumbnail($size, $custom_image_attributes); ?>
			</a>
			<?php if (get_theme_mod('archive_show_captions') || get_theme_mod('archive_show_credits')) : ?>
				<?php
				$featured_image_id = get_post_thumbnail_id();
				$caption           = wp_get_attachment_caption($featured_image_id);
				$credit            = method_exists('Newspack\Newspack_Image_Credits', 'get_media_credit_string') && \Newspack\Newspack_Image_Credits::get_media_credit_string($featured_image_id);
				if ($caption || $credit) :
				?>
					<figcaption>
						<?php if (get_theme_mod('archive_show_captions') && $caption) : ?>
							<?php echo esc_html($caption); ?>
						<?php endif; ?>
						<?php if (get_theme_mod('archive_show_credits') && $credit) : ?>
							<?php echo wp_kses_post(\Newspack\Newspack_Image_Credits::get_media_credit_string(get_post_thumbnail_id())); ?>
						<?php endif; ?>
					</figcaption>
				<?php endif; ?>
			<?php endif; ?>
		</figure>

<?php
	endif; // End is_singular().

	// Set a global variable to identify that the first featured image has been displayed.
	if (! isset($GLOBALS['newspack_after_first_featured_image'])) {
		$GLOBALS['newspack_after_first_featured_image'] = true;
	}
}

function newspack_custom_post_thumbnail_sizes_attr($attr)
{
	if ($attr["class"] === "custom-logo") {
		return $attr;
	}
	if (is_admin()) {
		return $attr;
	}
	if (is_home() || is_front_page()) {
		return set_custom_image_attributes($attr, array(200, 400), 400);
	}
	// if (is_page() || is_archive()) {
	// 	return set_custom_image_attributes($attr, array(200, 400), 400);
	// }
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
		//return get_the_modified_time('D, d M Y H:i:s', $post_id) . ' GMT';
		return gmdate('D, d M Y H:i:s', get_the_modified_time('U', $post_id)) . ' GMT';
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
add_action('pre_get_posts', 'custom_category_tag_combo_pages');

function custom_category_tag_combo_pages($query)
{
	if (is_admin() || !$query->is_main_query()) return;

	$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$parts = explode('/', $uri);
	$last_part = end($parts);

	// 🔁 Redirect rule: /abc/xyz/test.html → /test.html
	if (count($parts) > 1 && str_ends_with($last_part, '.html')) {
		$redirect_to = '/' . $last_part;
		wp_redirect(site_url($redirect_to), 301);
		exit;
	}

	// 🛑 Then skip if .href or it's a valid post
	if (strpos($uri, '.href') !== false || is_single()) {
		return;
	}


	if (count($parts) < 2) return;

	$category_ids = [];
	$tag_slugs = [];

	foreach ($parts as $slug) {
		$cat = get_category_by_slug($slug);
		if ($cat) {
			$category_ids[] = $cat->term_id;
			continue;
		}

		$tag = get_term_by('slug', $slug, 'post_tag');
		if ($tag) {
			$tag_slugs[] = $slug;
			continue;
		}

		return;
	}

	if (!empty($category_ids)) {
		$query->set('category__and', $category_ids);
	}

	if (!empty($tag_slugs)) {
		$query->set('tag_slug__and', $tag_slugs);
	}

	$query->set('post_type', 'post');
	$query->set('posts_per_page', get_option('posts_per_page'));

	$query->is_custom_combo_query = true;
}

add_filter('posts_results', 'check_posts_results', 10, 2);
function check_posts_results($posts, $query)
{
	if (isset($query->is_custom_combo_query) && $query->is_custom_combo_query) {
		$log_file = WP_CONTENT_DIR . '/my-query-log.txt';

		if (!empty($posts)) {
			$query->is_home = false;
			$query->is_archive = true;
			$query->is_404 = false;
		}
	}
	return $posts;
}
add_action('template_redirect', 'fix_custom_combo_404', 20);
function fix_custom_combo_404()
{
	global $wp_query;

	if (isset($wp_query->is_custom_combo_query) && $wp_query->is_custom_combo_query) {
		if ($wp_query->have_posts()) {
			status_header(200);
			$wp_query->is_404 = false;
		} else {
			// No posts found - let it 404
			return;
		}
	}
}

add_filter('wpseo_breadcrumb_links', 'custom_yoast_breadcrumb_order');
function custom_yoast_breadcrumb_order($links)
{
	global $wp_query;
	if (!isset($wp_query->is_custom_combo_query) || !$wp_query->is_custom_combo_query) {
		return $links;
	}
	$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
	$parts = explode('/', $uri);
	// Start with home breadcrumb
	$new_links = [array_shift($links)]; // Keep the home link

	foreach ($parts as $slug) {
		$term = null;
		$breadcrumb_text = null;

		// Check if it's a category
		if ($cat = get_category_by_slug($slug)) {
			$term = $cat;
			$taxonomy = 'category';
		}
		// Check if it's a tag
		elseif ($tag = get_term_by('slug', $slug, 'post_tag')) {
			$term = $tag;
			$taxonomy = 'post_tag';
		}

		if ($term) {
			// Check for Yoast's breadcrumb title first
			$breadcrumb_text = get_term_meta($term->term_id, 'wpseo_breadcrumb_title', true);
			// Fall back to term name if no custom title exists
			if (empty($breadcrumb_text)) {
				$breadcrumb_text = $term->name;
			}

			$new_links[] = array(
				'url' => get_term_link($term, $taxonomy),
				'text' => $breadcrumb_text
			);
		}
	}

	// Add the current page (remove URL to make it non-clickable)
	if (!empty($links)) {
		$current = end($links);
		if (isset($current['url'])) {
			unset($current['url']);
		}
		$new_links[] = $current;
	}

	return $new_links;
}
/**
 * Get Yoast SEO term title correctly
 */
function get_term_title_with_fallback($term_id)
{
	// 1. First try Yoast SEO description
	$yoast_title = get_term_meta($term_id, '_yoast_wpseo_title', true);
	if (!empty($yoast_title)) {
		return $yoast_title;
	}

	// 2. Check legacy Yoast taxonomy meta
	$tax_meta = get_option('wpseo_taxonomy_meta');
	if (!empty($tax_meta['category'][$term_id]['wpseo_title'])) {
		return $tax_meta['category'][$term_id]['wpseo_title'];
	}

	// 3. Fallback to standard term description
	$term = get_term($term_id);
	if ($term && !is_wp_error($term) && !empty($term->description)) {
		return strip_tags($term->title);
	}

	// 4. Ultimate fallback
	$front_page_id = get_option('page_on_front');
	if ($front_page_id) {
		return get_post_meta($front_page_id, '_yoast_wpseo_title', true);
	}
	return false;
}

/**
 * Get Term Description with fallbacks
 */
function get_term_description_with_fallback($term_id)
{
	// 1. First try Yoast SEO description
	$yoast_desc = get_term_meta($term_id, '_yoast_wpseo_metadesc', true);
	if (!empty($yoast_desc)) {
		return $yoast_desc;
	}

	// 2. Check legacy Yoast taxonomy meta
	$tax_meta = get_option('wpseo_taxonomy_meta');
	if (!empty($tax_meta['category'][$term_id]['wpseo_desc'])) {
		return $tax_meta['category'][$term_id]['wpseo_desc'];
	}

	// 3. Fallback to standard term description
	$term = get_term($term_id);
	if ($term && !is_wp_error($term) && !empty($term->description)) {
		return strip_tags($term->description);
	}

	// 4. Ultimate fallback
	$front_page_id = get_option('page_on_front');
	if ($front_page_id) {
		return get_post_meta($front_page_id, '_yoast_wpseo_metadesc', true);
	}
	return false;
}

/**
 * Custom SEO Title with Shekhawati News format
 */
add_filter('wpseo_title', 'custom_dynamic_seo_title');
function custom_dynamic_seo_title($title)
{
	global $wp_query;
	if (!isset($wp_query->is_custom_combo_query) || !$wp_query->is_custom_combo_query) {
		return $title;
	}
	$default_format = 'Shekhawati News: झुंझुनू, सीकर, चूरू की ताजा खबरें - Shekhawati Live';

	// Try to get last category's title
	if (!empty($wp_query->query_vars['category__and'])) {
		$last_category_id = end($wp_query->query_vars['category__and']);
		$meta_title = get_term_title_with_fallback($last_category_id);
		if (!empty($meta_title)) {
			$processed_title = str_replace(
				['%%page%%', '%%sep%%', '%%sitename%%'],
				['', '-', get_bloginfo('name')],
				$meta_title
			);
			return $processed_title;
		}
	}
	return $default_format;
}

/**
 * Set meta description - last category or fallback to homepage
 */
add_filter('wpseo_metadesc', 'custom_dynamic_seo_description');
function custom_dynamic_seo_description($description)
{
	global $wp_query;
	if (!isset($wp_query->is_custom_combo_query) || !$wp_query->is_custom_combo_query) {
		return $description;
	}
	if (!empty($wp_query->query_vars['category__and'])) {
		$last_category_id = end($wp_query->query_vars['category__and']);
		$meta_desc = get_term_description_with_fallback($last_category_id);
		if (!empty($meta_desc)) {
			return $meta_desc;
		}
	}
	$default_desc = get_bloginfo('description');
	return $default_desc;
}
