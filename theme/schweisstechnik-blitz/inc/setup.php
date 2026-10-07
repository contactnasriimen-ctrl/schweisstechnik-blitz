<?php
/**
 * Theme-Grundeinstellungen, Assets, Kopfbereich.
 *
 * @package SchweisstechnikBlitz
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $content_width ) ) {
	$content_width = 1320;
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		register_nav_menus( array( 'primary' => 'Hauptnavigation (leer = Anker der Startseite)' ) );
	}
);

/** Ist das die Elementor-Vorschau (Editor-iframe)? */
function blitz_is_elementor_preview() {
	return isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ); // phpcs:ignore WordPress.Security.NonceVerification
}

/** Wurde die Seite mit Elementor gebaut? */
function blitz_built_with_elementor( $post_id ) {
	return did_action( 'elementor/loaded' ) && 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true );
}

function blitz_show_loader() {
	return is_front_page() && blitz_opt( 'loader', true ) && ! blitz_is_elementor_preview() && ! is_customize_preview();
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$v = blitz_asset_version();
		wp_enqueue_style( 'blitz-style', blitz_asset( 'css/style.css' ), array(), $v );
		wp_add_inline_style( 'blitz-style', blitz_inline_css() );

		$footer = array( 'in_footer' => true, 'strategy' => 'defer' );
		wp_register_script( 'gsap', blitz_asset( 'vendor/gsap/gsap.min.js' ), array(), '3.13.0', $footer );
		wp_register_script( 'gsap-scrolltrigger', blitz_asset( 'vendor/gsap/ScrollTrigger.min.js' ), array( 'gsap' ), '3.13.0', $footer );
		wp_register_script( 'lenis', blitz_asset( 'vendor/lenis/lenis.min.js' ), array(), '1.3', $footer );
		wp_enqueue_script( 'blitz-main', blitz_asset( 'js/main.js' ), array( 'gsap', 'gsap-scrolltrigger', 'lenis' ), $v, $footer );

		if ( blitz_opt( 'webgl', true ) ) {
			if ( function_exists( 'wp_enqueue_script_module' ) ) {
				wp_enqueue_script_module( 'blitz-hero', blitz_asset( 'js/hero.js' ), array(), $v );
			} else {
				add_action(
					'wp_footer',
					function () use ( $v ) {
						echo '<script type="module" src="' . esc_url( add_query_arg( 'ver', $v, blitz_asset( 'js/hero.js' ) ) ) . '"></script>' . "\n";
					},
					30
				);
			}
		}
	}
);

/** Farben aus dem Customizer + Abstand für die Admin-Leiste */
function blitz_inline_css() {
	$molten = sanitize_hex_color( blitz_opt( 'color_accent', '#ff6a1a' ) );
	$gold   = sanitize_hex_color( blitz_opt( 'color_gold', '#e9bd7c' ) );
	$css    = ':root{';
	if ( $molten && '#ff6a1a' !== strtolower( $molten ) ) {
		$css .= '--molten:' . $molten . ';';
	}
	if ( $gold && '#e9bd7c' !== strtolower( $gold ) ) {
		$css .= '--gold:' . $gold . ';';
	}
	$css .= '}';
	$css .= '.admin-bar .nav{top:32px}@media (max-width:782px){.admin-bar .nav{top:46px}}';
	return $css;
}

add_action(
	'wp_head',
	function () {
		echo "<script>document.documentElement.classList.add('js');</script>\n";
		echo '<meta name="theme-color" content="#09090a">' . "\n";
		echo '<link rel="preload" href="' . esc_url( blitz_asset( 'fonts/archivo.woff2' ) ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		echo '<link rel="preload" href="' . esc_url( blitz_asset( 'fonts/instrument-serif-italic.woff2' ) ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( blitz_asset( 'img/favicon.svg' ) ) . '" type="image/svg+xml">' . "\n";
			echo '<link rel="apple-touch-icon" href="' . esc_url( blitz_asset( 'img/apple-touch-icon.png' ) ) . '">' . "\n";
		}
	},
	1
);

add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'blitz';
		if ( blitz_is_elementor_preview() ) {
			$classes[] = 'blitz-edit';
		}
		return $classes;
	}
);

/* Emoji-Skripte von WordPress werden nicht gebraucht */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/* Elementor Pro: Header/Footer über den Theme Builder ersetzbar */
add_action(
	'elementor/theme/register_locations',
	function ( $manager ) {
		$manager->register_all_core_location();
	}
);
