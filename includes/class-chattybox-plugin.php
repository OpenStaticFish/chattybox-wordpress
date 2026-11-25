<?php
/**
 * ChattyBox plugin bootstrap and WordPress integration.
 *
 * @package ChattyBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the ChattyBox widget to a WordPress site.
 */
final class ChattyBox_Plugin {
	const OPTION_NAME = 'chattybox_settings';
	const SETTINGS_GROUP = 'chattybox_settings_group';
	const PAGE_SLUG = 'chattybox';
	const SCRIPT_HANDLE = 'chattybox-widget';
	const DEFAULT_SCRIPT_URL = 'https://chattybox.ai/widget.js';
	const WIDGET_API_URL = 'https://adorable-woodpecker-629.convex.site/chat';

	/**
	 * The singleton instance.
	 *
	 * @var ChattyBox_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Prevent booting hooks more than once.
	 *
	 * @var bool
	 */
	private $booted = false;

	/**
	 * Return the plugin instance.
	 *
	 * @return ChattyBox_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set defaults when the plugin is activated.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( false === get_option( self::OPTION_NAME, false ) ) {
			add_option( self::OPTION_NAME, self::defaults(), '', 'no' );
		}
	}

	/**
	 * WordPress does not need a deactivation migration for this plugin.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Settings are intentionally kept so a temporary deactivation is safe.
	}

	/**
	 * Register the plugin hooks.
	 *
	 * @return void
	 */
	public function boot() {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_configuration_notice' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_widget' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_widget_attributes' ), 10, 3 );
		add_filter( 'plugin_action_links_' . plugin_basename( CHATTYBOX_FILE ), array( $this, 'add_action_links' ) );
	}

	/**
	 * Return the option defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'enabled'    => false,
			'api_key'    => '',
			'script_url' => self::DEFAULT_SCRIPT_URL,
			'locale'     => '',
		);
	}

	/**
	 * Return normalized settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_settings() {
		$stored = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return wp_parse_args( $stored, self::defaults() );
	}

	/**
	 * Register the Settings API fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section(
			'chattybox_connection',
			__( 'Connect your ChattyBox project', 'chattybox' ),
			array( $this, 'render_connection_section' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'chattybox_enabled',
			__( 'Enable chatbot', 'chattybox' ),
			array( $this, 'render_enabled_field' ),
			self::PAGE_SLUG,
			'chattybox_connection'
		);

		add_settings_field(
			'chattybox_api_key',
			__( 'Public widget API key', 'chattybox' ),
			array( $this, 'render_api_key_field' ),
			self::PAGE_SLUG,
			'chattybox_connection'
		);

		add_settings_field(
			'chattybox_script_url',
			__( 'Widget script URL', 'chattybox' ),
			array( $this, 'render_script_url_field' ),
			self::PAGE_SLUG,
			'chattybox_connection'
		);

		add_settings_field(
			'chattybox_locale',
			__( 'Widget language override', 'chattybox' ),
			array( $this, 'render_locale_field' ),
			self::PAGE_SLUG,
			'chattybox_connection'
		);
	}

	/**
	 * Add the settings page below Settings.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'ChattyBox AI Chatbot', 'chattybox' ),
			__( 'ChattyBox', 'chattybox' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Sanitize and validate submitted settings.
	 *
	 * @param mixed $input Submitted settings.
	 * @return array<string, mixed>
	 */
	public function sanitize_settings( $input ) {
		$input   = is_array( $input ) ? $input : array();
		$current = self::get_settings();
		$clean   = self::defaults();

		$clean['enabled'] = ! empty( $input['enabled'] );
		$clean['api_key'] = $this->sanitize_api_key( $input['api_key'] ?? '' );

		$script_url = trim( wp_unslash( (string) ( $input['script_url'] ?? self::DEFAULT_SCRIPT_URL ) ) );
		if ( '' === $script_url ) {
			$script_url = self::DEFAULT_SCRIPT_URL;
		}

		$clean['script_url'] = $this->sanitize_url_setting(
			$script_url,
			$current['script_url'] ?: self::DEFAULT_SCRIPT_URL,
			__( 'The widget script URL must use HTTPS. HTTP is only allowed for localhost development URLs.', 'chattybox' ),
			'script_url'
		);

		$clean['locale'] = $this->sanitize_locale( $input['locale'] ?? '', $current['locale'] );

		if ( $clean['enabled'] && '' === $clean['api_key'] ) {
			add_settings_error(
				self::OPTION_NAME,
				'missing_connection',
				__( 'Add the public widget API key before enabling ChattyBox.', 'chattybox' ),
				'error'
			);
			$clean['enabled'] = false;
		}

		return $clean;
	}

	/**
	 * Enqueue the hosted widget only on public frontend requests.
	 *
	 * @return void
	 */
	public function enqueue_widget() {
		$settings = self::get_settings();

		if ( ! $settings['enabled'] || '' === $settings['api_key'] ) {
			return;
		}

		if ( is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_ajax() ) {
			return;
		}

		/**
		 * Allow a theme or another plugin to disable the widget for a request.
		 *
		 * @param bool  $should_load Whether the widget should load.
		 * @param array $settings    Current ChattyBox settings.
		 */
		if ( ! apply_filters( 'chattybox_load_widget', true, $settings ) ) {
			return;
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			$settings['script_url'],
			array(),
			CHATTYBOX_VERSION,
			true
		);
	}

	/**
	 * Add the configuration attributes consumed by widget.js.
	 *
	 * @param string $tag    Script tag generated by WordPress.
	 * @param string $handle Script handle.
	 * @param string $src    Script source URL.
	 * @return string
	 */
	public function add_widget_attributes( $tag, $handle, $src ) {
		if ( self::SCRIPT_HANDLE !== $handle ) {
			return $tag;
		}

		$settings = self::get_settings();
		$attributes = array(
			'data-api-key'          => $settings['api_key'],
			'data-api-url'          => self::WIDGET_API_URL,
			'data-chattybox-widget' => 'true',
		);

		if ( '' !== $settings['locale'] ) {
			$attributes['data-locale'] = $settings['locale'];
		}

		$attribute_markup = ' async';
		foreach ( $attributes as $name => $value ) {
			$attribute_markup .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
		}

		$updated_tag = preg_replace( '/^<script\b/i', '<script' . $attribute_markup, $tag, 1 );

		return is_string( $updated_tag ) ? $updated_tag : $tag;
	}

	/**
	 * Add a direct Settings link to the plugins list.
	 *
	 * @param array<int, string> $links Existing action links.
	 * @return array<int, string>
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $this->settings_url() ),
			esc_html__( 'Settings', 'chattybox' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Render the settings screen.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings      = self::get_settings();
		$is_configured = '' !== $settings['api_key'];
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'ChattyBox AI Chatbot', 'chattybox' ); ?></h1>
			<p>
				<?php
				esc_html_e(
					'Connect this WordPress site to a ChattyBox project and load the source-cited chatbot on public pages, posts, and knowledge base content.',
					'chattybox'
				);
				?>
			</p>

			<?php settings_errors( self::OPTION_NAME ); ?>

			<?php if ( ! $is_configured ) : ?>
				<div class="notice notice-info">
					<p>
						<strong><?php echo esc_html__( 'One-time setup:', 'chattybox' ); ?></strong>
						<?php
						esc_html_e(
							"Create a ChattyBox project, index this site's public content, and copy the public widget key from Install.",
							'chattybox'
						);
						?>
						<a href="https://chattybox.ai/docs/wordpress-chatbot-plugin/" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html__( 'Read the installation guide', 'chattybox' ); ?>
						</a>
					</p>
				</div>
			<?php endif; ?>

			<form action="options.php" method="post">
				<?php
				settings_fields( self::SETTINGS_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save settings', 'chattybox' ) );
				?>
			</form>

			<hr />
			<h2><?php echo esc_html__( 'Before you publish', 'chattybox' ); ?></h2>
			<ol>
				<li><?php echo esc_html__( 'Confirm the ChattyBox project has indexed the public WordPress content you want to support.', 'chattybox' ); ?></li>
				<li><?php echo esc_html__( 'Test a real visitor question in the ChattyBox dashboard and verify its source citation.', 'chattybox' ); ?></li>
				<li><?php echo esc_html__( 'Open a public page in an incognito window and confirm the launcher appears.', 'chattybox' ); ?></li>
			</ol>
			<p>
				<?php
				esc_html_e(
					'The public widget API key is safe to use in frontend markup. Do not paste a ChattyBox management credential here.',
					'chattybox'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the settings section introduction.
	 *
	 * @return void
	 */
	public function render_connection_section() {
		printf(
			'<p>%s</p>',
			esc_html__( 'ChattyBox is a hosted service that answers questions using content you index in your ChattyBox account. This plugin does not upload your WordPress database or sync posts.', 'chattybox' )
		);
	}

	/**
	 * Render the enable checkbox.
	 *
	 * @return void
	 */
	public function render_enabled_field() {
		$settings = self::get_settings();
		?>
		<label for="chattybox-enabled">
			<input
				type="checkbox"
				id="chattybox-enabled"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled]"
				value="1"
				<?php checked( $settings['enabled'], true ); ?>
			/>
			<?php echo esc_html__( 'Load the hosted ChattyBox widget for visitors', 'chattybox' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'When enabled with a key, visitors’ browsers contact the script host and ChattyBox for configuration before a question is asked. Questions, conversation identifiers, language and page context are sent to ChattyBox when visitors chat. Exclude sensitive pages or gate loading if visitor consent is required.', 'chattybox' ); ?>
			<a href="https://chattybox.ai/privacy/"><?php esc_html_e( 'Privacy policy', 'chattybox' ); ?></a> ·
			<a href="https://chattybox.ai/terms/"><?php esc_html_e( 'Terms of service', 'chattybox' ); ?></a>
		</p>
		<p class="description">
			<?php esc_html_e( 'The hosted widget displays a linked “Powered by ChattyBox” footer on Free. Starter, Pro and Business can turn it off in ChattyBox under Customize → ChattyBox branding.', 'chattybox' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the public API key field.
	 *
	 * @return void
	 */
	public function render_api_key_field() {
		$settings = self::get_settings();
		$this->render_text_input(
			'api_key',
			$settings['api_key'],
			__( 'Paste the public widget key from Install in your ChattyBox project.', 'chattybox' ),
			'cb_pk_...'
		);
	}

	/**
	 * Render the widget script URL field.
	 *
	 * @return void
	 */
	public function render_script_url_field() {
		$settings = self::get_settings();
		$this->render_text_input(
			'script_url',
			$settings['script_url'],
			__( 'Leave the hosted URL unless ChattyBox has given you a preview or self-hosted loader URL.', 'chattybox' ),
			self::DEFAULT_SCRIPT_URL,
			'url'
		);
	}

	/**
	 * Render the optional locale override field.
	 *
	 * @return void
	 */
	public function render_locale_field() {
		$settings = self::get_settings();
		$this->render_text_input(
			'locale',
			$settings['locale'],
			__( 'Optional. Use a two-letter language code such as en, fr, or de. Leave blank to detect the page or browser language.', 'chattybox' ),
			'en'
		);
	}

	/**
	 * Render a text input in the Settings API table.
	 *
	 * @param string $key         Settings key.
	 * @param string $value       Current value.
	 * @param string $description Field description.
	 * @param string $placeholder Placeholder value.
	 * @param string $type        HTML input type.
	 * @return void
	 */
	private function render_text_input( $key, $value, $description, $placeholder, $type = 'text' ) {
		printf(
			'<input type="%1$s" class="regular-text" id="chattybox-%2$s" name="%3$s[%2$s]" value="%4$s" placeholder="%5$s" autocomplete="off" />',
			esc_attr( $type ),
			esc_attr( $key ),
			esc_attr( self::OPTION_NAME ),
			esc_attr( $value ),
			esc_attr( $placeholder )
		);
		printf( '<p class="description">%s</p>', esc_html( $description ) );
	}

	/**
	 * Show an admin warning when a saved installation cannot load the widget.
	 *
	 * @return void
	 */
	public function maybe_show_configuration_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = self::get_settings();
		if ( ! $settings['enabled'] || '' !== $settings['api_key'] ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'ChattyBox is enabled but still needs a public widget API key.', 'chattybox' ),
			esc_url( $this->settings_url() ),
			esc_html__( 'Open settings', 'chattybox' )
		);
	}

	/**
	 * Sanitize a public widget API key.
	 *
	 * @param mixed $value Submitted key.
	 * @return string
	 */
	private function sanitize_api_key( $value ) {
		$key = sanitize_text_field( wp_unslash( (string) $value ) );
		return substr( trim( $key ), 0, 255 );
	}

	/**
	 * Sanitize the optional widget script URL and preserve the last valid value.
	 *
	 * @param mixed  $value         Submitted URL.
	 * @param string $fallback      Last valid URL.
	 * @param string $error_message Error shown in the Settings API.
	 * @param string $field         Settings field name.
	 * @return string
	 */
	private function sanitize_url_setting( $value, $fallback, $error_message, $field ) {
		$url = esc_url_raw( trim( wp_unslash( (string) $value ) ) );

		if ( '' === $url ) {
			return '';
		}

		if ( ! $this->is_allowed_url( $url ) ) {
			add_settings_error( self::OPTION_NAME, 'invalid_' . $field, $error_message, 'error' );
			return (string) $fallback;
		}

		return substr( $url, 0, 2048 );
	}

	/**
	 * Sanitize an optional locale override.
	 *
	 * @param mixed  $value    Submitted locale.
	 * @param string $fallback Last valid locale.
	 * @return string
	 */
	private function sanitize_locale( $value, $fallback ) {
		$locale = sanitize_text_field( wp_unslash( (string) $value ) );
		$locale = strtolower( str_replace( '_', '-', trim( $locale ) ) );

		if ( '' === $locale ) {
			return '';
		}

		if ( ! preg_match( '/^[a-zA-Z]{2}(?:-[a-zA-Z]{2})?$/', $locale ) ) {
			add_settings_error(
				self::OPTION_NAME,
				'invalid_locale',
				__( 'The language override must be a two-letter code such as en, fr, or de.', 'chattybox' ),
				'error'
			);
			return (string) $fallback;
		}

		return substr( $locale, 0, 10 );
	}

	/**
	 * Only permit secure remote URLs, plus local HTTP URLs for development.
	 *
	 * @param string $url URL to inspect.
	 * @return bool
	 */
	private function is_allowed_url( $url ) {
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return false;
		}

		$scheme = strtolower( $parts['scheme'] );
		if ( 'https' === $scheme ) {
			return true;
		}

		$local_hosts = array( 'localhost', '127.0.0.1', '::1' );
		return 'http' === $scheme && in_array( strtolower( $parts['host'] ), $local_hosts, true );
	}

	/**
	 * Return the plugin settings URL.
	 *
	 * @return string
	 */
	private function settings_url() {
		return admin_url( 'options-general.php?page=' . self::PAGE_SLUG );
	}
}
