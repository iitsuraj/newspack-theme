<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Newspack
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> data-amp-auto-lightbox-disable>
<?php

do_action( 'wp_body_open' );
do_action( 'before_header' );

// Header Settings
$header_simplified     = get_theme_mod( 'header_simplified', false );
$header_center_logo    = get_theme_mod( 'header_center_logo', false );
$show_slideout_sidebar = get_theme_mod( 'header_show_slideout', false );
$slideout_sidebar_side = get_theme_mod( 'slideout_sidebar_side', 'left' );
$header_sub_simplified = get_theme_mod( 'header_sub_simplified', false );
$header_sticky         = get_theme_mod( 'header_sticky', false );

// Even if 'Show Slideout Sidebar' is checked, don't show it if no widgets are assigned.
if ( ! is_active_sidebar( 'header-1' ) ) {
	$show_slideout_sidebar = false;
}

get_template_part( 'template-parts/header/mobile', 'sidebar' );
get_template_part( 'template-parts/header/desktop', 'sidebar' );

if ( true === $header_sub_simplified && ! is_front_page() ) :
	get_template_part( 'template-parts/header/subpage', 'sidebar' );
endif;
?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#main"><?php _e( 'Skip to content', 'newspack' ); ?></a>

	<?php if ( is_active_sidebar( 'header-2' ) ) : ?>
		<div class="header-widget above-header-widgets">
			<div class="wrapper">
				<?php dynamic_sidebar( 'header-2' ); ?>
			</div><!-- .wrapper -->
		</div><!-- .above-header-widgets -->
	<?php endif; ?>

	<header id="masthead" class="site-header hide-header-search" [class]="searchVisible ? 'show-header-search site-header ' : 'hide-header-search site-header'">

		<?php if ( true === $header_sub_simplified && ! is_front_page() ) : ?>
			<div class="middle-header-contain">
				<div class="wrapper">
					<?php if ( newspack_has_menus() || true === $show_slideout_sidebar ) : ?>
						<div class="subpage-toggle-contain">
							<button class="subpage-toggle" on="tap:subpage-sidebar.toggle">
								<?php echo wp_kses( newspack_get_icon_svg( 'menu', 20 ), newspack_sanitize_svgs() ); ?>
								<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'newspack' ); ?></span>
							</button>
						</div>
					<?php endif; ?>

					<?php get_template_part( 'template-parts/header/site', 'branding' ); ?>

					<?php newspack_mobile_cta(); ?>

					<?php if ( newspack_has_menus() ) : ?>
						<button class="mobile-menu-toggle" on="tap:mobile-sidebar.toggle">
							<?php echo wp_kses( newspack_get_icon_svg( 'menu', 20 ), newspack_sanitize_svgs() ); ?>
							<span><?php esc_html_e( 'Menu', 'newspack' ); ?></span>
						</button>
					<?php endif; ?>

					<?php get_template_part( 'template-parts/header/header', 'search' ); ?>
				</div>
			</div><!-- .wrapper -->
		<?php else : ?>
			<?php if ( has_nav_menu( 'secondary-menu' ) ) : ?>
				<div class="top-header-contain desktop-only">
					<div class="wrapper">
						<?php if ( true === $show_slideout_sidebar && 'left' === $slideout_sidebar_side ) : ?>
							<button class="desktop-menu-toggle" on="tap:desktop-sidebar.toggle">
								<?php echo wp_kses( newspack_get_icon_svg( 'menu', 20 ), newspack_sanitize_svgs() ); ?>
								<span><?php echo esc_html( get_theme_mod( 'slideout_label', esc_html__( 'Menu', 'newspack' ) ) ); ?></span>
							</button>
						<?php endif; ?>

						<div id="secondary-nav-contain">
							<?php
							if ( ! newspack_is_amp() ) {
								newspack_secondary_menu();
							}
							?>
						</div>

						<?php
						// If logo is NOT centered:
						if (
							( false === $header_center_logo && false === $header_simplified ) ||
							( true === $header_simplified )
							) :
						?>
							<div id="social-nav-contain">
								<?php
								if ( ! newspack_is_amp() ) {
									newspack_social_menu_header();
								}
								?>
							</div>
						<?php endif; ?>

						<?php if ( true === $show_slideout_sidebar && 'right' === $slideout_sidebar_side ) : ?>
							<button class="desktop-menu-toggle dir-right" on="tap:desktop-sidebar.toggle">
								<?php echo wp_kses( newspack_get_icon_svg( 'menu', 20 ), newspack_sanitize_svgs() ); ?>
								<span><?php echo esc_html( get_theme_mod( 'slideout_label', esc_html__( 'Menu', 'newspack' ) ) ); ?></span>
							</button>
						<?php endif; ?>
					</div><!-- .wrapper -->
				</div><!-- .top-header-contain -->
			<?php endif; ?>

			<div class="middle-header-contain">
				<div class="wrapper">
					<?php if ( true === $show_slideout_sidebar && ! has_nav_menu( 'secondary-menu' ) && 'left' === $slideout_sidebar_side ) : ?>
						<button class="desktop-menu-toggle" on="tap:desktop-sidebar.toggle">
							<?php echo wp_kses( newspack_get_icon_svg( 'menu', 20 ), newspack_sanitize_svgs() ); ?>
							<span><?php echo esc_html( get_theme_mod( 'slideout_label', esc_html__( 'Menu', 'newspack' ) ) ); ?></span>
						</button>
					<?php endif; ?>

					<?php
					// Centered logo AND NOT short header.
					if ( true === $header_center_logo && false === $header_simplified ) :
					?>
						<div id="social-nav-contain" class="desktop-only">
							<?php
							if ( ! newspack_is_amp() ) {
								newspack_social_menu_header();
							}
							?>
						</div>
					<?php endif; ?>

					<?php
					// Centered logo AND short header.
					if ( true === $header_center_logo && true === $header_simplified ) :
					?>

						<div class="nav-wrapper desktop-only">
							<div id="site-navigation">
								<?php
								if ( ! newspack_is_amp() ) {
									newspack_primary_menu();
								}
								?>
							</div><!-- #site-navigation -->
						</div><!-- .nav-wrapper -->

					<?php endif; ?>

					<?php get_template_part( 'template-parts/header/site', 'branding' ); ?>

					<?php
					// Short header:
					if ( true === $header_simplified && false === $header_center_logo ) :
					?>

						<div class="nav-wrapper desktop-only">
							<div id="site-navigation">
								<?php
								if ( ! newspack_is_amp() ) {
									newspack_primary_menu();
								}
								?>
							</div><!-- #site-navigation -->

							<?php
							// Centered logo:
							if ( true === $header_center_logo ) {
								get_template_part( 'template-parts/header/header', 'search' );
							}
							?>
						</div><!-- .nav-wrapper -->

					<?php endif; ?>


					<div class="nav-wrapper desktop-only">
						<div id="tertiary-nav-contain">
							<?php
							if ( ! newspack_is_amp() ) {
								newspack_tertiary_menu();
							}
							?>
						</div><!-- #tertiary-nav-contain -->

						<?php
							// Header is simplified OR logo is centered:
							if ( true === $header_simplified || true === $header_center_logo ) :
								get_template_part( 'template-parts/header/header', 'search' );
							endif;
						?>
					</div><!-- .nav-wrapper -->

					<?php if ( true === $show_slideout_sidebar && ! has_nav_menu( 'secondary-menu' ) && 'right' === $slideout_sidebar_side ) : ?>
						<button class="desktop-menu-toggle dir-right" on="tap:desktop-sidebar.toggle">
							<?php echo wp_kses( newspack_get_icon_svg( 'menu', 20 ), newspack_sanitize_svgs() ); ?>
							<span><?php echo esc_html( get_theme_mod( 'slideout_label', esc_html__( 'Menu', 'newspack' ) ) ); ?></span>
						</button>
					<?php endif; ?>

					<?php newspack_mobile_cta(); ?>

					<?php do_action( 'newspack_header_before_mobile_toggle' ); ?>

					<?php if ( newspack_has_menus() ) : ?>
						<button class="mobile-menu-toggle" on="tap:mobile-sidebar.toggle">
							<?php echo wp_kses( newspack_get_icon_svg( 'menu', 20 ), newspack_sanitize_svgs() ); ?>
							<span><?php esc_html_e( 'Menu', 'newspack' ); ?></span>
						</button>
					<?php endif; ?>

					<?php do_action( 'newspack_header_after_mobile_toggle' ); ?>

				</div><!-- .wrapper -->
			</div><!-- .middle-header-contain -->


			<?php
			// Header is NOT short:
			if ( false === $header_simplified ) :
			?>
				<div class="bottom-header-contain desktop-only">
					<div class="wrapper">
						<div id="site-navigation">
							<?php
							if ( ! newspack_is_amp() ) {
								newspack_primary_menu();
							}
							?>
						</div>

						<?php
						// If logo is not centered.
						if ( false === $header_center_logo && has_nav_menu( 'primary-menu' ) ) {
							get_template_part( 'template-parts/header/header', 'search' );
						}
						?>
					</div><!-- .wrapper -->
				</div><!-- .bottom-header-contain -->
			<?php
			endif;

			/**
			 * Displays 'highlight' menu; created a function to reduce duplication.
			 */
			if ( has_nav_menu( 'highlight-menu' ) ) :
			?>
				<div class="highlight-menu-contain desktop-only">
					<div class="wrapper">
						<nav class="highlight-menu" aria-label="<?php esc_attr_e( 'Highlight Menu', 'newspack' ); ?>">
							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'highlight-menu',
									'container'      => false,
									'items_wrap'     => '<ul id="%1$s" class="%2$s"><li><span class="menu-label">' . esc_html( wp_get_nav_menu_name( 'highlight-menu' ) ) . '</span></li>%3$s</ul>',
									'depth'          => 1,
								)
							);
							?>
						</nav>
					</div><!-- .wrapper -->
				</div><!-- .highlight-menu-contain -->
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $header_sticky ) : ?>
			<div class="sticky-bg"></div>
		<?php endif; ?>
	</header><!-- #masthead -->

	<?php
	if ( function_exists('yoast_breadcrumb') ) {
		yoast_breadcrumb( '<div class="site-breadcrumb desktop-only"><div class="wrapper">','</div></div>' );
	}
	?>
	<?php
	if ((is_category() || is_page())) {
		$current_category = get_queried_object();
		// $current_category = is_page() ? get_category_by_slug(); : ();
		if (is_page() && !empty($current_category->post_name)) {
			$current_category = get_category_by_slug($current_category->post_name);
		}
		// Check if $current_category is valid and is a category
		if ($current_category && isset($current_category->term_id)) {
			// Check if the current category has child categories
			$child_categories = get_categories(array(
				'parent' => $current_category->term_id, // Fetch categories whose parent is the current category
				'hide_empty' => false,                   // Only show categories that have posts
				'hierarchical' => false,                 // Include hierarchical structure
			));

			if (!empty($child_categories)) {
				// If the current category has children, make it the parent and display its children
				$parent_category_id = $current_category->term_id;
				$parent_category = $current_category;
			} else {
				// If the current category has no children, use the parent category (if any)
				$parent_category_id = $current_category->parent ? $current_category->parent : $current_category->term_id;
				$parent_category = get_category($parent_category_id);
			}

			// Ensure the parent category is valid
			if ($parent_category && isset($parent_category->term_id)) {
				// Get child categories of the parent category
				if ($parent_category_id != $current_category->term_id) {
					$child_categories = get_categories(array(
						'parent' => $parent_category_id,
						'hide_empty' => true,
						'hierarchical' => false,
					));
				}
				// Initialize an array to store post counts
				$category_post_counts = [];

				// Get the current date and the date 7 days ago
				$date_3_days_ago = date('Y-m-d', strtotime('-3 days'));

				// Loop through each child category to get the post count from the last 7 days
				foreach ($child_categories as $child) {
					// Query the posts from the last 7 days
					$recent_posts_query = new WP_Query(array(
						'posts_per_page' => -1, // Get all posts
						'post_type' => 'post',
						'post_status' => 'publish',
						'cat' => $child->term_id, // Filter by category
						'date_query' => array(
							'after' => $date_3_days_ago, // Posts after the last 7 days
						),
					));

					// Store the post count for each child category
					$category_post_counts[$child->term_id] = $recent_posts_query->found_posts;

					// Reset the query
					wp_reset_postdata();
				}

				// Sort the child categories by post count in descending order
				usort($child_categories, function ($a, $b) use ($category_post_counts) {
					return $category_post_counts[$b->term_id] - $category_post_counts[$a->term_id];
				});

				global $child_categories_global;
				$child_categories_global = $child_categories;

				if (!empty($child_categories)) : ?>
					<div class="category-nav">
						<div class="wrapper wrapper-nav">
								<span class="first">
									<a href="<?php echo esc_url(get_category_link($parent_category->term_id)); ?>"
									   title="<?php echo esc_attr($parent_category->name); ?>">
										<?php echo esc_html($parent_category->name); ?>
									</a>
								</span>
							<nav class="nav">
								<?php foreach ($child_categories as $child) : ?>
									<a title="<?php echo esc_attr($child->name); ?>"
									   class="nav-item <?php echo ($current_category->term_id == $child->term_id) ? 'active' : ''; ?>"
									   href="<?php echo esc_url(get_category_link($child->term_id)); ?>">
										<span><?php echo esc_html($child->name); ?></span>
									</a>
								<?php endforeach; ?>
							</nav>
						</div>
					</div>
				<?php endif;
			}
		}
	}
	?>

	<?php do_action( 'after_header' ); ?>

	<?php if ( is_active_sidebar( 'header-3' ) ) : ?>
		<div class="header-widget below-header-widgets">
			<div class="wrapper">
				<?php dynamic_sidebar( 'header-3' ); ?>
			</div><!-- .wrapper -->
		</div><!-- .above-header-widgets -->
	<?php endif; ?>

	<div id="content" class="site-content">
