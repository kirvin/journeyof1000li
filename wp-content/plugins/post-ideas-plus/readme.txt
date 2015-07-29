=== Post Ideas+ ===
Contributors: Matt Hobbs & Aaron Robbins
Tags: ideas,admin,posts,track,articles,idea tracking,thoughts,planning
Requires at least: 2.3
Tested up to: 2.9.2
Stable tag: 2.1.0.5

Keeps track of all your blog articles and post ideas using the following fields:
* Working title
* Tags
* Source links
* Description
* Priority

== Description ==

**Manage** and keep track of your blog articles and post ideas. Option to allow
all users to view the Administrators post ideas and write them (great for multiple
author blogs).

Features:

* [NEW] Option to allow all users to see Administrator post ideas
* [NEW] Option to only allow users to see Administrator post ideas, not add their own
* [NEW] Users can only delete / edit their own post ideas
* [NEW] Each user can now has there own set of post ideas
* [NEW] Updated for latest version of Wordpress (2.9+)
* [NEW] 2 Dashboard widgets for easy editing / access to your post ideas
* [NEW] Preference to order the post ideas on the dashboard
* Manages post ideas
* Sort ideas by name, priority or date
* Track keywords and research urls
* Edit, delete or write options

* [Support](http://nooshu.com/wordpress-plug-in-post-ideas-plus/)

**TODO:**
Some sort of post idea status for admins, see if users are writing the post ideas

== Installation ==
1. Download the zip file and extract. Upload all files to the '/wp-content/plugins/'. Make sure you upload in the correct
folder structure e.g. /wp-content/plugins/post-ideas-plus/.
2. Activate the Post Ideas+ plugin through the 'Plugins' menu in WordPress.
3. Under 'Tools' you should see a new option called 'Post Ideas+'. You can add / edit / delete ideas from this page.
4. You should also see 2 new dashboard widgets 'Add post idea' & 'Latest post idea'. These can be disabled via the screen options.
4. If the script fails to install the required mysql table please use the included wp_piplus.sql file and import it to your
 wordpress database using phpMyAdmin (if your table prefix is not wp_ you will need to change it in the sql file)

== Screenshots ==
1. Admin area screenshot
2. Dashboard area screenshot

== Frequently Asked Questions ==
= I have post ideas in the database but the dashboard doesn't list them? =
Try updating the number of rows that are displayed in the Post Ideas+ admin menu.

== Changelog ==
= 2.1.0.5 =
* Fixed an annoying layout bug on the admin page in certain browsers 
* Added the ability for users to choose the dashboard sort order

= 2.1.0.4 =
* Added option to allow users to view admin post ideas (users can write but not edit / delete ideas) 
* Added setting to only allow users to view admin post ideas

= 2.1.0.3 =
* Fixed an error with 'include' on certain server setups (thanks Chris)

= 2.1.0.2 =
* Each user now has their own set of post ideas.
* User roles below admin can view the PI+ settings page and add ideas

= 2.1.0.1 =
* Fixed broken styling for 'Your Post Ideas' header on the admin page.

= 2.1 =
* Update of Aaron Robbins' 'Post Ideas'. 
* Added dashboard widgets.
* Updated the admin section styling.

== Upgrade Notice ==
= 2.1.0.5 =
* Fixed an annoying layout bug on the admin page in certain browsers 
* Added the ability for users to choose the dashboard sort order

= 2.1.0.4 =
First stab at support for multi-author blogs where the admin wants to suggest post ideas to authors.
Feedback very welcome on how people think this should work.

= 2.1.0.3 =
Small PHP include fix.

= 2.1.0.2 =
Added support for multiple users.

= 2.1.0.1 =
Very small fix to the admin page.

= 2.1 =
Updated Aaron Robbins' code and updated the plugin styling.