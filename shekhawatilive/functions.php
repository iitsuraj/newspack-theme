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
		// add_rewrite_rule('^([^/]+)\.html$', 'index.php?name=$matches[1]', 'top');
		add_action('template_redirect', 'redirect_old_html_urls_to_new_structure');
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
		add_filter('onesignal_send_notification', 'onesignal_send_notification_filter', 10);
		add_filter('newspack_reader_activation_should_render_auth', '__return_false', 5);
		// add_action('wp', 'add_last_modified_header');
		add_filter('wp_editor_set_quality', function ($quality) {
			return 20;
		});
	}
endif;
add_action('after_setup_theme', 'newspack_shekhawatilive_setup', 12);
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
// function newspack_add_sidebar($classes)
// {
// 	if (($key = array_search("no-sidebar", $classes)) !== false) {
// 		unset($classes[$key]);
// 	}

// 	$classes[] = 'has-sidebar';
// 	return $classes;
// }
// add_filter('body_class', 'newspack_add_sidebar', 999);

// add nav-bar like livehindustan for subcategory page
function add_sub_category_nav()
{
	// Early exit if not on a category or page
	if (!is_category() && !is_page()) {
		return;
	}

	$current_object = get_queried_object();
	$current_category = is_category() ? $current_object : get_category_by_slug($current_object->post_name ?? '');

	// Validate category
	if (empty($current_category->term_id)) {
		return;
	}

	// Get relevant categories (children or siblings)
	$parent_category_id = $current_category->term_id;
	$child_categories = get_categories([
		'parent' => $parent_category_id,
		'hide_empty' => false,
		'hierarchical' => false,
	]);

	// Fallback to siblings if no children
	if (empty($child_categories) && $current_category->parent) {
		$parent_category_id = $current_category->parent;
		$child_categories = get_categories([
			'parent' => $parent_category_id,
			'hide_empty' => true,
		]);
	}

	if (empty($child_categories)) {
		return;
	}

	// Get post counts with a single optimized query
	global $wpdb;
	$date_3_days_ago = date('Y-m-d H:i:s', strtotime('-3 days'));

	$results = $wpdb->get_results($wpdb->prepare(
		"SELECT t.term_id, COUNT(p.ID) as post_count
         FROM {$wpdb->terms} t
         INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
         INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
         INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
         WHERE tt.parent = %d
         AND p.post_type = 'post'
         AND p.post_status = 'publish'
         AND p.post_date > %s
         GROUP BY t.term_id",
		$parent_category_id,
		$date_3_days_ago
	));

	// Create post count lookup
	$post_counts = array_column($results ?: [], 'post_count', 'term_id');

	// Sort categories by post count
	usort($child_categories, function ($a, $b) use ($post_counts) {
		return ($post_counts[$b->term_id] ?? 0) <=> ($post_counts[$a->term_id] ?? 0);
	});

	// Output HTML
	$parent_category = get_category($parent_category_id);

	$output = '<div class="category-nav"><div class="wrapper wrapper-nav"><span class="first"><a href="'
	. esc_url(get_category_link($parent_category->term_id))
	. '" title="' . esc_attr($parent_category->name) . '">'
	. esc_html($parent_category->name) . '</a></span><nav class="nav">';

	foreach ($child_categories as $child) {
		$output .= '<a title="' . esc_attr($child->name)
			. '" class="nav-item' . ($current_category->term_id == $child->term_id ? ' active' : '')
			. '" href="' . esc_url(get_category_link($child->term_id)) . '"><span>'
			. esc_html($child->name) . '</span></a>';
	}

    $output .= '</nav></div></div>';
	return $output;
}
add_action('after_header', function() {
    echo add_sub_category_nav();
}, 1);
function redirect_old_html_urls_to_new_structure()
{
	// Check if the URL matches /%postname%.html/
	if (preg_match('#^/([^/]+)\.html/?$#', $_SERVER['REQUEST_URI'], $matches)) {
		$post_slug = $matches[1];

		// Try to get the post by slug
		$post = get_page_by_path($post_slug, OBJECT, ['post', 'page']); // Works for posts AND pages

		if ($post) {
			$new_url = get_permalink($post->ID); // Automatically uses the current permalink structure

			// Ensure the new URL is different (avoid infinite redirects)
			if ($new_url && $new_url !== home_url($_SERVER['REQUEST_URI'])) {
				wp_redirect($new_url, 301);
				exit;
			}
		}
	}
}

function onesignal_send_notification_filter($fields)
{
	$post_url = get_permalink($post_id) . '?share=jetpack-whatsapp&nb=1';
    $post_title = get_the_title($post_id);
	$excerpt = get_the_excerpt($post_id);
	$short_excerpt = mb_substr(strip_tags($excerpt), 0, 150) . '...';
	// Add a unique tag to prevent collapsing/overwriting
	$fields['headings'] = array(
        'en' => $post_title
    );

    $fields['contents'] = array(
        'en' => $short_excerpt
    );
	$fields['web_push_topic'] = $post_id;
	$fields['chrome_web_notification_tag'] = $post_id;
	// Optional: disable renotify so users don’t hear the sound again
	$fields['chrome_web_notification_renotify'] = true;
	   $fields['buttons'] = array(
        array(
            "id" => "share-button",
            "text" => "Share",
            "icon" => "https://cdn.shekhawatilive.com/wp-content/uploads/2025/05/whatsapp.png",  // Your custom icon URL
            "url" => $post_url
        )
    );
	return $fields;
}