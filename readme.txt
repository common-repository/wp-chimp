=== WP Chimp ===
Contributors: tfirdaus, oknoorap
Donate link: https://paypal.me/tfirdaus
Tags: mailchimp, form, subscription, gutenberg, widget, shortocode.
Requires at least: 5.0
Tested up to: 5.3
Stable tag: 0.7.4
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lean MailChimp subscription form plugin for WordPress

== Description ==

**WP Chimp** provides the ability to add a MailChimp subscription form to your post and page content and in the Widget area. The plugin also registers a custom [Gutenberg Block](https://wordpress.org/gutenberg/handbook/language/) allowing you to add and preview the subscription form immediately within the WordPress content editor.

This plugin focuses on simplicity, user experience, and speed. We ensure to provide seamless integration to MailChimp services, ease of use with an interface that complies with WordPress design guidelines, and that it would work at scale.

### Non-affiliation Disclaimer

MailChimp is a registered trademark of The Rocket Science Group. The name "MailChimp" used in this plugin is for identification and reference purposes only and does not imply any association with the trademark holder of their product brand, or any of its subsidiaries or its affiliates.

== Installation ==

=== From within WordPress (Recommended) ===

1. Visit **Plugins > Add New**
2. Search for "WP Chimp" (without the quote, of course)
3. Click the **Install Now** button of WP Chimp on the search result
4. Activate it from the Plugins page.

=== Manual Upload: ===

1. Download the plugin `.zip` packcage from WordPress.org.
2. Visit **Plugins > Add New**
3. Click **Upload Plugin**
4. Click **Choose File**, and select the plugin `.zip` package you have just downloaded.
5. Click **Install Now**.
6. Activate it from the Plugins page.

=== FTP Upload: ===

If none of the above works, though this is going to be less convenient, you can install the plugin through FTP (File Transfer Protocol). To do so, you will need an FTP software installed on your computer, such as:

* [FileZilla](https://filezilla-project.org/) (Windows, macOS, Linux)
* [CyberDuck](https://cyberduck.io/) (Windows, macOS)

Then, connect to the FTP/SFTP server using the credentials given by your hosting provider.

1. Download the plugin `.zip` packcage from WordPress.org.
2. Unzip the archive and upload the `wp-chimp` folder into the plugin directory at `/wp-content/plugins/`.
3. Activate it from the _Plugins_ page.

== Frequently Asked Questions ==

None, at the moment. Please ask :)

== Screenshots ==

1. The plugin Setting page
2. The MailChimp list with the details
3. The subscription form setting
4. The subscription form Gutenberg block
5. The subscription form Gutenberg block rendered on the front-end
6. The subscription form editor on the Widget
7. The subscription form rendered on the Widget area

== Changelog ==

= 0.7.4 =
* Fixed: Fatal error due to missing files.

= 0.7.3 =
* Added: Improvement to the "Subscription Form" reliability.
* Fixed: Conditional logic to render the GDPR checkbox field.

= 0.7.2 =
* Fixed: Incorrect version numbering in some files.

= 0.7.1 =
* Fixed: The input pagination of the table in the Settings page.
* Fixed: The POT file generator to include all the files and parse the translateable strings properly.

= 0.7.0 =
* Changed: The Settings screen UI with built-in React Component in Gutenberg.
* Added: The button to copy the shortcode in the Settings screen (#53).
* Added: Basic GDPR field in the subscription form (#52).
* Added: Template tag function to render the subscription form (#48).

== Upgrade Notice ==

= 0.7.4 =
Fixed fatal error due to missing files.
