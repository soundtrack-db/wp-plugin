<?php
/**
 * Plugin Name: SoundtrackDB Integration
 * Plugin URI:  https://soundtrackdb.vercel.app/
 * Description: Adds a movie/TV Spotify soundtrack widget to any post using an existing TMDB or IMDb ID custom field. Theme-agnostic — works with any site that stores a TMDB/IMDb ID in post meta.
 * Version:     1.0.0
 * Author:      cnf1g & shreyash
 * Author URI:  https://soundtrackdb.vercel.app/
 * License:     GPL-2.0+
 * Text Domain: soundtrackdb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'SOUNDTRACKDB_VERSION', '1.0.0' );
define( 'SOUNDTRACKDB_API_BASE', 'https://soundtrackdb.vercel.app/v1/titles' );

/**
 * ---------------------------------------------------------------------------
 * Settings page (Settings -> SoundtrackDB)
 * ---------------------------------------------------------------------------
 */
class SoundtrackDB_Settings {

	const OPTION_KEY = 'soundtrackdb_settings';

	public static function defaults() {
		return array(
			'tmdb_meta_key'   => 'tmdb_id',
			'imdb_meta_key'   => 'imdb_id',
			'auto_insert'     => '0',
			'insert_position' => 'after', // 'before' or 'after' the_content
			'widget_title'    => 'Listen to the Soundtrack',
			'cache_hours'     => '24',
		);
	}

	public static function get( $key ) {
		$opts = wp_parse_args( get_option( self::OPTION_KEY, array() ), self::defaults() );
		return isset( $opts[ $key ] ) ? $opts[ $key ] : null;
	}

	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function add_menu() {
		add_options_page(
			'SoundtrackDB',
			'SoundtrackDB',
			'manage_options',
			'soundtrackdb',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register_settings() {
		register_setting( 'soundtrackdb_group', self::OPTION_KEY );
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$opts = wp_parse_args( get_option( self::OPTION_KEY, array() ), self::defaults() );
		?>
		<div class="wrap">
			<h1>SoundtrackDB Settings</h1>
			<p>Free movie &amp; TV soundtrack API — no API key required. This plugin looks up an existing TMDB or IMDb ID already stored on your posts and shows the matching Spotify playlist.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'soundtrackdb_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="stdb_tmdb_key">TMDB ID meta key</label></th>
						<td>
							<input type="text" id="stdb_tmdb_key" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[tmdb_meta_key]" value="<?php echo esc_attr( $opts['tmdb_meta_key'] ); ?>" class="regular-text" />
							<p class="description">The custom field name your theme/importer already uses to store a post's TMDB ID (e.g. <code>tmdb_id</code>).</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="stdb_imdb_key">IMDb ID meta key (fallback)</label></th>
						<td>
							<input type="text" id="stdb_imdb_key" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[imdb_meta_key]" value="<?php echo esc_attr( $opts['imdb_meta_key'] ); ?>" class="regular-text" />
							<p class="description">Used if no TMDB ID is found on the post (e.g. <code>imdb_id</code>).</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Auto-insert widget</th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[auto_insert]" value="1" <?php checked( $opts['auto_insert'], '1' ); ?> />
								Automatically show the soundtrack widget on single posts/pages that have a TMDB or IMDb ID, without needing the shortcode.
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="stdb_position">Auto-insert position</label></th>
						<td>
							<select id="stdb_position" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[insert_position]">
								<option value="before" <?php selected( $opts['insert_position'], 'before' ); ?>>Before content</option>
								<option value="after" <?php selected( $opts['insert_position'], 'after' ); ?>>After content</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="stdb_title">Widget title</label></th>
						<td>
							<input type="text" id="stdb_title" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[widget_title]" value="<?php echo esc_attr( $opts['widget_title'] ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="stdb_cache">Cache lookups (hours)</label></th>
						<td>
							<input type="number" min="1" id="stdb_cache" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[cache_hours]" value="<?php echo esc_attr( $opts['cache_hours'] ); ?>" class="small-text" />
							<p class="description">How long to cache each lookup result (transient) before re-checking the API. Keeps your site fast and respects the free API's rate limit.</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<hr />
			<h2>Manual usage (shortcode)</h2>
			<p>Drop this into any post/page to show the widget regardless of auto-insert settings:</p>
			<code>[soundtrackdb]</code> — uses the current post's TMDB/IMDb ID automatically.<br/>
			<code>[soundtrackdb tmdb_id="993710"]</code> — force a specific TMDB ID.<br/>
			<code>[soundtrackdb imdb_id="tt21192188"]</code> — force a specific IMDb ID.
		</div>
		<?php
	}
}
SoundtrackDB_Settings::register();

/**
 * ---------------------------------------------------------------------------
 * Core lookup + render logic
 * ---------------------------------------------------------------------------
 */
class SoundtrackDB_Core {

	/**
	 * Resolve a TMDB or IMDb ID for the given post, checking explicit
	 * shortcode attributes first, then falling back to post meta using
	 * whatever field names the site owner configured.
	 */
	public static function resolve_ids( $post_id, $atts = array() ) {
		$tmdb_key = SoundtrackDB_Settings::get( 'tmdb_meta_key' );
		$imdb_key = SoundtrackDB_Settings::get( 'imdb_meta_key' );

		$tmdb_id = ! empty( $atts['tmdb_id'] ) ? sanitize_text_field( $atts['tmdb_id'] ) : get_post_meta( $post_id, $tmdb_key, true );
		$imdb_id = ! empty( $atts['imdb_id'] ) ? sanitize_text_field( $atts['imdb_id'] ) : get_post_meta( $post_id, $imdb_key, true );

		return array(
			'tmdb_id' => $tmdb_id ? $tmdb_id : null,
			'imdb_id' => $imdb_id ? $imdb_id : null,
		);
	}

	/**
	 * Fetch (with caching) the soundtrack result from the SoundtrackDB API.
	 */
	public static function fetch( $tmdb_id = null, $imdb_id = null ) {
		if ( ! $tmdb_id && ! $imdb_id ) {
			return null;
		}

		$cache_key = 'soundtrackdb_' . md5( $tmdb_id . '|' . $imdb_id );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		if ( $imdb_id ) {
			$url = trailingslashit( SOUNDTRACKDB_API_BASE ) . 'imdb/' . rawurlencode( $imdb_id ) . '/music';
		} else {
			$url = trailingslashit( SOUNDTRACKDB_API_BASE ) . 'tmdb/' . rawurlencode( $tmdb_id ) . '/music';
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 8,
				'headers' => array(
					'Accept'     => 'application/json',
					'User-Agent' => 'SoundtrackDB-WordPress/' . SOUNDTRACKDB_VERSION . '; ' . home_url(),
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			// Cache the miss briefly too, so a slow/broken lookup doesn't
			// hit the API on every single page load.
			set_transient( $cache_key, null, HOUR_IN_SECONDS );
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['success'] ) || empty( $body['music'][0]['url'] ) ) {
			set_transient( $cache_key, null, HOUR_IN_SECONDS );
			return null;
		}

		$hours = max( 1, (int) SoundtrackDB_Settings::get( 'cache_hours' ) );
		set_transient( $cache_key, $body, $hours * HOUR_IN_SECONDS );

		return $body;
	}

	/**
	 * Render the widget HTML for a given API result.
	 */
	public static function render( $data ) {
		if ( empty( $data['music'][0]['url'] ) ) {
			return '';
		}

		$track      = $data['music'][0];
		$title      = esc_html( SoundtrackDB_Settings::get( 'widget_title' ) );
		$url        = esc_url( $track['url'] );
		$playlist_id = isset( $track['playlist_id'] ) ? sanitize_text_field( $track['playlist_id'] ) : '';

		ob_start();
		?>
		<div class="soundtrackdb-widget" style="margin:24px 0;padding:16px;border:1px solid #e2e2e2;border-radius:12px;">
			<p style="margin:0 0 12px;font-weight:600;">🎵 <?php echo $title; ?></p>
			<?php if ( $playlist_id ) : ?>
				<iframe
					style="border-radius:12px;"
					src="https://open.spotify.com/embed/playlist/<?php echo esc_attr( $playlist_id ); ?>?utm_source=generator"
					width="100%"
					height="152"
					frameBorder="0"
					allowfullscreen=""
					allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
					loading="lazy">
				</iframe>
			<?php else : ?>
				<a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">Listen on Spotify →</a>
			<?php endif; ?>
			<p style="margin:8px 0 0;font-size:12px;opacity:0.6;">Soundtrack data via <a href="https://soundtrackdb.vercel.app/" target="_blank" rel="noopener noreferrer">SoundtrackDB</a></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Full pipeline: resolve IDs -> fetch -> render, for a given post.
	 */
	public static function widget_html_for_post( $post_id, $atts = array() ) {
		$ids  = self::resolve_ids( $post_id, $atts );
		$data = self::fetch( $ids['tmdb_id'], $ids['imdb_id'] );

		if ( ! $data ) {
			return '';
		}

		return self::render( $data );
	}
}

/**
 * ---------------------------------------------------------------------------
 * Shortcode: [soundtrackdb] or [soundtrackdb tmdb_id="..."] / [imdb_id="..."]
 * ---------------------------------------------------------------------------
 */
add_shortcode(
	'soundtrackdb',
	function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'tmdb_id' => '',
				'imdb_id' => '',
			),
			$atts,
			'soundtrackdb'
		);

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}

		return SoundtrackDB_Core::widget_html_for_post( $post_id, $atts );
	}
);

/**
 * ---------------------------------------------------------------------------
 * Auto-insert into the_content, if enabled in settings.
 * ---------------------------------------------------------------------------
 */
add_filter(
	'the_content',
	function ( $content ) {
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( '1' !== SoundtrackDB_Settings::get( 'auto_insert' ) ) {
			return $content;
		}

		$post_id = get_the_ID();
		$widget  = SoundtrackDB_Core::widget_html_for_post( $post_id );

		if ( ! $widget ) {
			return $content;
		}

		$position = SoundtrackDB_Settings::get( 'insert_position' );

		return ( 'before' === $position ) ? $widget . $content : $content . $widget;
	}
);
