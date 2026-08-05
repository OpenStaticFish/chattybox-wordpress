# ChattyBox AI Chatbot for WordPress

Standalone WordPress plugin for adding the ChattyBox source-cited AI chatbot to public WordPress pages without editing theme files.

This directory is intentionally its own Git repository and is consumed by the parent ChattyBox workspace as the `.repos/chattybox-wordpress` Git submodule.

## Features

- Native Settings API screen under **Settings > ChattyBox**.
- Loads the maintained `widget.js` only on public frontend requests.
- Adds the public widget API key and API URL as escaped script attributes.
- Supports an optional widget language override.
- Avoids admin, feed, REST, and AJAX requests.
- Keeps settings through deactivation and removes them on uninstall.
- No Composer, npm, or build step required.

## Installation

1. Copy the `chattybox-wordpress` directory into `wp-content/plugins/`, or zip the directory contents for upload in WordPress.
2. Activate **ChattyBox AI Chatbot** from the Plugins screen.
3. Open **Settings > ChattyBox**.
4. Create a ChattyBox project, crawl the public WordPress URL, and test representative questions.
5. Copy the **public widget API key** and **Widget API URL** from the project Embed tab.
6. Paste both values into the plugin settings and enable the chatbot.

The API key is designed for frontend use. Never paste a management credential into the plugin settings.

## Configuration

The production defaults are:

- Widget script: `https://chattybox.ai/widget.js`
- Widget API URL: the project-specific URL shown in the ChattyBox dashboard
- Locale: automatic detection from the page language and visitor browser

The plugin stores only its connection settings in the WordPress options table. The widget sends visitor questions and the current public page context to ChattyBox so it can answer from the project content and show citations.

Themes and plugins can disable loading for a request with the `chattybox_load_widget` filter:

```php
add_filter(
    'chattybox_load_widget',
    static function ( $should_load ) {
        return $should_load && ! is_page( 'checkout' );
    }
);
```

## Development

The plugin has no runtime dependencies. If PHP is installed locally, run:

```sh
php -l chattybox.php
php -l includes/class-chattybox-plugin.php
php -l uninstall.php
```

The repository includes `phpcs.xml.dist` for projects that run WordPress Coding Standards.

## License

GPL-2.0-or-later. See `LICENSE`.
