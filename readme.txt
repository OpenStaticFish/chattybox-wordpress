=== ChattyBox AI Chatbot ===
Tags: ai chatbot, chatbot, customer support, documentation, knowledge base
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add the ChattyBox source-cited AI chatbot to public WordPress pages without editing theme files.

== Description ==

ChattyBox AI Chatbot connects a WordPress site to a ChattyBox project and loads the maintained chatbot widget on public pages and posts.

ChattyBox indexes the public content you choose in your ChattyBox account. The hosted service searches that material and generates answers with source links when it finds support. You need a ChattyBox account and a project with indexed content. Free and paid service plans have different usage limits.

= Features =

* Configure the plugin from Settings > ChattyBox.
* Add the widget without editing theme files or installing a build toolchain.
* Keep the widget out of wp-admin, feeds, REST requests, and AJAX requests.
* Set an optional language override or let ChattyBox detect the page language.
* Use the public widget key intended for browser integrations.

== External services ==

Activating the plugin does not contact ChattyBox. After a site administrator saves a public widget key and enables loading, the visitor’s browser downloads the service’s JavaScript from `https://chattybox.ai/widget.js` (or the script URL the administrator chose). This is the chat interface; the plugin does not download or install WordPress updates from ChattyBox.

Before a visitor asks a question, the widget contacts `https://adorable-woodpecker-629.convex.site` for project configuration using the public key. It may also request interface translations for the selected language and load the project's configured icon. Request hosts receive normal connection information such as IP address and browser headers.

When the visitor sends a message, the widget sends the question, public key, conversation identifier (for follow-ups), language and current page context to the `/chat` endpoint on that same API host. ChattyBox processes the message with relevant indexed content and model providers and stores conversations with the project. The plugin does not store visitor messages in WordPress. Changing the script URL does not change the plugin's API endpoint.

The hosted chat window includes a linked “Powered by ChattyBox” footer on Free. Starter, Pro and Business can turn it off under Customize > ChattyBox branding. The plugin does not add a separate credit to your WordPress theme.

Review the [ChattyBox privacy policy](https://chattybox.ai/privacy/) and [terms of service](https://chattybox.ai/terms/). The enable setting is a site administrator's choice, not a visitor consent prompt. If your site requires prior visitor consent, prevent the widget from loading until consent is granted.

== Installation ==

1. Follow the [WordPress setup guide](https://chattybox.ai/docs/wordpress-chatbot-plugin/) for the current ZIP. In WordPress, choose Plugins > Add New > Upload Plugin.
2. Activate the plugin.
3. Go to Settings > ChattyBox.
4. Create a ChattyBox project and crawl your public WordPress URL.
5. Copy the public widget key from Install > Website installation and check the allowed origins in Keys & domains.
6. Read the service disclosure, paste the key, enable the widget, and save. Visit a public post signed out, ask a supported question and follow its citation.

== Frequently Asked Questions ==

= Does this plugin crawl my WordPress content? =

No. The plugin loads the ChattyBox widget. ChattyBox crawls the public URL you connect in the dashboard, so your WordPress publishing workflow stays unchanged.

= Do I need to edit my theme? =

No. The plugin adds the widget script to the frontend for you.

= Is the API key safe in frontend HTML? =

Use only the public widget key from Install. It is designed to identify your project in browser integrations. Never use a management credential.

= Can I disable the chatbot on a specific page? =

Yes. Use the `chattybox_load_widget` filter in your theme or another plugin to return `false` for checkout, account, membership or other sensitive routes. The plugin does not detect them automatically. Purge page caches after changing settings or exclusions.

= What data does the plugin store? =

The plugin stores its public widget API key, script URL, enabled state, and optional locale in the WordPress options table. Visitor questions are sent by the widget to ChattyBox and are not stored by this plugin.

== Screenshots ==

1. Settings > ChattyBox before connecting a project, with external requests and the hosted footer explained beside the enable switch.
2. A visitor asks about sticky posts in a WordPress demo and receives an answer linked to the public WordPress documentation article.

== Changelog ==

= 0.2.1 =
* Explain the hosted service, external requests and plan branding before enablement.
* Update setup instructions and link to the WordPress-specific guide.

= 0.2.0 =
* Configure the production widget API endpoint automatically.
* Remove the API URL from customer settings.

= 0.1.0 =
* Initial release.
