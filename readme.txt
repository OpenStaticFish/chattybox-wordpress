=== ChattyBox AI Chatbot ===
Contributors: chattybox
Tags: ai chatbot, chatbot, customer support, documentation, knowledge base
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add the ChattyBox source-cited AI chatbot to public WordPress pages without editing theme files.

== Description ==

ChattyBox AI Chatbot connects a WordPress site to a ChattyBox project and loads the maintained chatbot widget on public pages and posts.

ChattyBox crawls the public content you choose, answers questions from indexed source material, and links visitors back to the pages used for each answer.

= Features =

* Configure the plugin from Settings > ChattyBox.
* Add the widget without editing theme files or installing a build toolchain.
* Keep the widget out of wp-admin, feeds, REST requests, and AJAX requests.
* Set an optional language override or let ChattyBox detect the page language.
* Use the public widget key intended for browser integrations.

== Installation ==

1. Upload the `chattybox-wordpress` folder to `/wp-content/plugins/`, or install the plugin ZIP from the WordPress admin.
2. Activate the plugin.
3. Go to Settings > ChattyBox.
4. Create a ChattyBox project and crawl your public WordPress URL.
5. Copy the public widget API key from the project Embed tab.
6. Paste the key into the plugin settings, save, and enable the chatbot.

== Frequently Asked Questions ==

= Does this plugin crawl my WordPress content? =

No. The plugin loads the ChattyBox widget. ChattyBox crawls the public URL you connect in the dashboard, so your WordPress publishing workflow stays unchanged.

= Do I need to edit my theme? =

No. The plugin adds the widget script to the frontend for you.

= Is the API key safe in frontend HTML? =

Use only the public widget API key from the ChattyBox Embed tab. It is designed to identify your project in browser integrations. Never use a management credential.

= Can I disable the chatbot on a specific page? =

Yes. Use the `chattybox_load_widget` filter in your theme or another plugin to return `false` for requests where the widget should not load.

= What data does the plugin store? =

The plugin stores its public widget API key, script URL, enabled state, and optional locale in the WordPress options table. Visitor questions are sent by the widget to ChattyBox and are not stored by this plugin.

== Changelog ==

= 0.2.0 =
* Configure the production widget API endpoint automatically.
* Remove the API URL from customer settings.

= 0.1.0 =
* Initial release.
