=== Alkivia Open Community ===
Contributors: txanny
Donate link: http://alkivia.org/donate
Help link: http://wordpress.org/tags/alkivia?forum_id=10
Docs link: http://wiki.alkivia.org/community
Tags: community, communities, profile, profiles, profiler, directory, members, member, users, widget, widgets, login, photo, picture, gallery
Requires at least: 2.9
Tested up to: 2.9.2
Stable tag: trunk

Plugin to create a users community on any WordPress blog. You will have user profiles pages, photo galleries, private messaging and much more.

== Description ==

A plugin to build user communities all around WordPress, mainly it’s around having a well integrated profiles system. This plugin will provide all needed functions and widgets to make it easy and flexible the ways to show all information about a user. Also, there is some user privacy settings to permit which information to show in the profile page.

With this plugin, you can manage you user profiles and have a user list. For each user, a profile page is provided. The information shown in the profile page is based on your privacy settings.
Also, you can select the user list order, ascending or descending, and ordering by different fields: ID, login name, display name or date registered.

WordPress avatars can be replaced by local avatars. And also a user picture at big size, is shown in the user profile page. This requires the Gallery component running.
For the user profile pages, the labels for IM addresses can be replaced by your own. (As example, you can change 'Yahoo IM' for 'Skype').
In the WordPress login page, the WordPress logo can be replaced, and the links will be changed to point to your blog. (By default the WordPress logo is shown and the links point to the WordPress site).

All pages use templates, so you can create you own output template to customize and style all displayed pages. The templetes directory can be moved outside the plugin to prevent loosing your custom templates on update.   

= Features: =

* Paged user list and the user profile page.
* Customize the logo on your login's page by uploading a new logo.
* Manage user galleries and upload user pictures.
* Moderate user pictures by using WordPress capabilities (Optional moderation).
* Links posts authors and comment authors to user profile.
* Use the local avatars if them exists. User can select which picture to use as Avatar.
* User photo is shown in the profile page. A photo Gallery is also provided. User can select the picture to show from loaded images.
* Settings page for profiles and gallery.
* Templating system to create your own templates and styles for all pages.
* An activity log to see what's happening on the site and who did it. Like an activity wall.

= Future Planned Features =

* Custom user fields. For registration and profile.
* Privacy settings per user.
* Private messaging.
* And some other under consideration.

= Languages included: =

* English
* Catalan
* Spanish
* Belorussian *by <a href="http://www.fatcow.com" rel="nofollow">Marcis Gasuns</a>*
* Dutch *by <a href="http://www.linkedin.com/in/johandemeijer" rel="nofollow">Johan de Meijer</a>*
* French *by <a href="http://www.monbouc.fr rel="nofollow">monBouc</a>*
* German *by <a href="http://www.gockeln.com" rel="nofollow">Arne Gockeln</a>*
* Italian *by Livio Di Puorto*
* Portuguese (Brasil) *by <a href="http://www.ideafixa.com" rel="nofollow">Alicia Ayala</a>*
* Portuguese (Portugal) *by <a href="http://www.menosketiago.com" rel="nofollow">Tiago Almeida</a>*
* POT file for easy translation to other languages included. See the <a href="http://wiki.alkivia.org/general/translators">translators page</a> for more information.

== Installation ==

= System Requirements =

* **Requires PHP 5.2**. Older versions of PHP are obsolete and expose your site to security risks.
* Verify the plugin is compatible with your WordPress Version. If not, plugin will not load.

= Installing the plugin =

1. Unzip the plugin archive.
1. Upload the plugin's folder to the WordPress plugins directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Configure all options at the 'Community' Menu, and activcate desired components.
1. Enjoy your plugin!

= First configuration step =

The first you have to do is to create a page to hold the user profiles. Do it this way:

1. Create a WordPress page. It's recommended a blank page (with no content).
1. Go to the 'Community->General' menu, and set there the community page.

NOTE: If you write something in this page, the content will be show at the top of the page when showing the user list. This content will not be shown when viewing a user profile. 

== Screenshots ==

1. Plugin menus at dashboard.
2. General settings and community components.
3. Upload a login form image.
4. User profiles settings.
5. Photo Gallery settings.
6. Users Gallery manager.
7. Default Users profile list.
8. Default User profile page.

== Frequently Asked Questions ==

= Where can I find more information about this plugin, usage and support ? =

* Take a look to the <a href="http://alkivia.org/wordpress/community">Plugin Homepage</a>.
* A <a href="http://wiki.alkivia.org/community">manual</a> is available for users and developers.
* The <a href="http://alkivia.org/cat/community">plugin posts archive</a> with new announcements and some tutorials.
* If you need help, <a href="http://wordpress.org/tags/alkivia?forum_id=10">ask in the Support forum</a>.

= I've found a bug or want to suggest a new feature. Where can I do it? =

* To fill a bug report or suggest a new feature, please fill a report in our <a href="http://tracker.alkivia.org/set_project.php?project_id=1&ref=view_all_bug_page.php">Tracker</a>.

= I'm a developer, where can I browse source code? =

* You can browse the source code at <a href="http://code.alkivia.org/wsvn/gpl/plugins/community/">Code Browser</a> and check out the last development version at http://svn.alkivia.org/gpl/plugins/community

== License ==

Copyright 2009, 2010 Jordi Canals

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.

== Changelog ==

= 0.10.4 =
  * Activating activity wall didn't create the database table.

= 0.10.3 =
  * Fixes a menor issue when WP version is not supported. (Framework Issue).
  
= 0.10.2 =
  * Fixes a crash when upgrading from old versions. (Framework Issue).
 
= 0.10.1 =
  * Fixed a local avatar not showing problem. (Framework Issue).
 
= 0.10 = 
  * Adapted to work with new Alkivia Framework (0.8).
  * Options can be set on alkivia.ini file.
  * style.css can be configured to be loaded from any url (via alkivia.ini file).
  * Can have an additional templates folder (configured at alkivia.ini or plugin).
  * Abstract component class moved to framework.
  * Some minor bug fixes.
  
= 0.9.3 =
* Tested up to WP 2.9.1.
* Changed license to GPL version 2.

= 0.9.2 =
* Minor fix browsing templates.

= 0.9.1 =
* Fixed a bug preventing gallery templates to work.

= 0.9 =
* New activity wall component.
* Thumbnails can be set on author pages.

= 0.8.1 =
* Fixed some warnings when uploaded images are rejected.

= 0.8 =
* New templating support (all pages use templates).
* Use the new WP_Widget class.

= 0.7.2 =
* Added French translation.
* Updated internal framework.

= 0.7.1 =
* Added German Translation.

= 0.7 =
* Internal fixes and improvements.
* New capabilities.
* Nickname uniqueness.
* Mail users when image approved/rejected.

= 0.6.5 =
* Solved a blocking nonce check.
 
= 0.6.4 =
* Added Italian and Portuguese translations.
* Updated Dutch translation.
* Solved problems with admin links and avatars alt attribute.

= 0.6.3 =
* Solves a bug that prevents plugin to be activated (Introduced in 0.6.2)

= 0.6.2 =
* Solves two bugs when updating: Components disabled and capabilities reset to defaults.

= 0.6.1 =
* Allows to disable user listing.
* Updated Dutch translation.

= 0.6 =
* Picture moderation.
* Force picture upload to see other users pictures.
* New widgets.
* Image captions.

= 0.5.4 =
* Added Dutch translation.

= 0.5.3 =
* Catalan and Belorussian translations.
* Now profiles can be private.
* Bugfix on watermark resize.

= 0.5.2 =
* Renewed the admin styles.
* Solved a problem with some permalinks structures.

= 0.5.1 =
* Solved problems loading pictures to usernames with spaces.
* Solved malformed RSS in some cases.
* Styles compatibility with WP 2.8.
 
= 0.5 =
* First stable public version.

== Upgrade Notice ==

= 0.10.4 =
Activating activity wall now created the activity table.

= 0.10.3 =
Fixes a crash when installing on not supported WP versions.
