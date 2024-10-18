=== Share Post ===
Contributors: bastho, agencenous, n4thaniel, ecolosites
Donate link:
Tags: Post, share, embed, reblog, posts, links, multisites, SEO, network, sharing
Requires at least: 5.0
Tested up to: 5.8
Stable tag: 1.0.4
License: GPL v2
License URI: http://creativecommons.org/licenses/by-nc/3.0/

Reblog posts accross the network,
Share a post link from a blog to another blog on the same WP multisite network and include the post content !

== Description ==

The plugin allows webmasters to suggest interesting posts on the network. Suggested posts will appear in a dashboard widget.
Any page or posts can be shared by clicking on the share button in the toolbar. Un new window (press-this) will be opened to include the post to share.

Additionally, inserting a short-link of a post in another one will display an extract of the original post.

no more duplicate content, just sharing !


== Installation ==

1. Upload `eelv-share-post` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress network admin

== Frequently asked questions ==

= Can I share on every blogs ? =

no, the plugin just transfrom short-links into post preview. You have to be administrator, author, editor... to post on a blog.

== Screenshots ==

1. Suggested post on the dashboard
2. Share button in the toolbar
3. Embeded link

== Changelog ==

= 1.0.4 =

* Fix Undefined variable: tumb

= 1.0.3 =

* Fix infinite loop in some cases

= 1.0.2 =

* Allow to remove all shared-on blogs
* Fix bug in URLs on batch sharing

= 1.0.1 =

* Remove PHP warnings in some cases

= 1.0.0 =

Release date: October 21, 2015

* Use of native PressThis url sharing
* Add "Suggest" feature
* Add .pot file

* JS, CSS, SQL files in separated directories
* Code cleanup and refactoring, Add some comments
* Required version updated to 3.8

= 0.4.3 =
* Fix : extends the_excerpt filter to get_the_excerpt to match more themes

= 0.4.2 =
* Fix : Add a network wide restriction

= 0.4.1 =
* Fix : Replace deprecated capability

= 0.4.0 =
* Add : improve sharing action : no more page refresh (Requires jQuery)
* Fix : PHP Warning:  array_key_exists()

= 0.3.0 =
* Add : manage sharing on edit page, select categories on each blog

= 0.2.3 =
* Fix : CSS fix and enhancement

= 0.2.2 =
* Add : new network-admin GUI for domain mapping
* Fix : multi-domain-mapping working properly

= 0.2.1 =
* Fix : domain names with "-" or "." causing js bug

= 0.2.0 =
* Add : support for multi-domain-mapping
* Add : options for preview length
* Add : options for displaying youtube, dailymotion or twitter links
* fix : performances optimisation

= 0.1.5 =
* fix : do not forget anymore the last site in the sharing list

= 0.1.4 =
* add : icon for extra-link in excerpt
* fix : only one thumbnail, if !has_post_thumbnail()

= 0.1.3 =
* fix : performances optimisation

= 0.1.2 =
* fix : performances optimisation

= 0.1 =
* plugin creation

== Upgrade notice ==

No particular informations
