=== CatWalker ===
Contributors: kwiliarty
Donate link: none
Tags: categories, intersections, widgets, custom taxonomies
Requires at least: 3.1
Tested up to: 4.2
Stable tag: 1.3.1

List categories or cross-categorizations in page or post contents. Let users search for the intersection of two categories.

== Description ==

The catWalker plugin lets you do more with WordPress categories. The plugin has these main uses.

1. Generate a customizable list of categories within the contents of a page or post
1. Create a configurable widget that will make it easy for visitors to find posts or pages at the intersection of two categories
1. Generate a list of cross-categorizations on a page or post
1. List the posts or pages from a given category on a page or post
1. Automatically list at the bottom of each post the categories which have been assigned to that post
1. Automatically list at the bottom of each post other posts in the same category
1. Customize the ordering preferences and number of posts on category archive pages

In addition, users can opt to use a hierarchical custom taxonomy (called "Attributes") that applies to Pages as well as Posts. If you use the Attributes taxonomy, most of the above options can be used on pages as well as posts.  

= List categories =

To generate a list of your site's categories add the following shortcode (in square brackets) to any post or page:

[categories]

The listing is highly configurable. To show just one branch of your category structure, for instance, you can create a shortcode like this:

[categories child_of="#"]

where the # stands for the id of the parent category.

This is only one of many attributes that you can use to customize your list. In general, you should be able to use any of the options documented at:

http://codex.wordpress.org/Template_Tags/wp_list_categories

In addition, if you can set a 'taxonomy' for your listing. In general, your choices will be to use the built-in "Categories" taxonomy or the custom "Attributes" taxonomy that comes with this plugin. 

Go to Settings > Writing to choose whether or not to use the custom "Attributes" taxonomy, and whether or not to make it the default for all CatWalker functions (shortcodes and the CrossCategorizer widget).

= Cross Categorizer widget =

On display pages, the Cross Categorizer widget includes two configurable dropdown lists of categories. Choose two categories, then click "Search" to view the posts or pages that belong to both.

On the admin side you can configure the widget to show different category branches in each dropdown.

As of version 1.3.1 you can also opt to list empty categories. Previously empty categories were not listed, and that is still the default behavior.

= Cross Categorizer shortcode =

You can add a configurable list of cross-categorizations to any post or page. Use this shortcode

[crosscat]

with any of the options documented at:

http://codex.wordpress.org/Function_Reference/get_categories

To show cross categorizations add an "intersector" attribute. The list of categories will then link only to posts that belong also to the intersector category. Imagine, for instance, that "10" is the id for a category called "Years" with child categories "2011," "2010," "2009," etc. Let "20" be the id for a category that names a particular course. The following shortcode would list all the children of Years, and the linked names would point only to posts or pages that had been categorized as belonging to the course in a give year.

[crosscat child_of="10" intersector="20"]

The listing will show the number of results for each cross categorization, and links that do not find any results are semi-transparent. 

= List category posts =

User the shortcode:

[category-posts]

to list on a page or post all the posts or pages belonging to a particular category (or other taxonomy term). 

= Custom "Attributes" Taxonomy =

The hierarchical custom taxonomy "Attributes" applies to Pages as well as Posts. Activated it in the "Catwalker Options" section on the "Settings > Writing" page, where you can also opt to make "Attributes" the default taxonomy for CatWalker functions.

= Post Attributes Listing =

If you are using the custom Attributes taxonomy, you may want to include a list of Attributes assigned to a given post or page at the end of that post or page. Many themes offer similar lists of tags and categories, but because these lists are typically theme-specific, it is not possible to provide a theme-generic solution. On the other hand, you can improve your chances by setting a CSS class for the attributes listing so that it will be styled similarly to the category listing in your theme. You can set both of these options on the Settings > Writing page in the CatWalker Options settings. 

= Related Posts Listing =

You can automatically add a list of related posts or pages to the end of every post or page by checking the appropriate box on the Settings > Writing page. You can designate specific categories or attributes to be included, you can specify categories or attributes whose child-terms will be included, or you can leave those inputs blank to list related posts for all terms. In a similar way, you can list terms for which to include no related-posts lists. The automated related-posts listing will operate only on your CatWalker default taxonomy. 

= Custom order and limit on Category Pages =

(Currently available only for Categories and not for the custom Attributes taxonomy.) Go to the "Catwalker Options" section of the "Settings > Writing" page. Check the box to use a custom ordering and choose your preferences from the drop-down menus. You can sort by date or title, ascending or descending. You can also choose a custom number of posts to display on category archive pages.

== Installation ==

To install this plugin manually:

1. Download the zipped plugin 
1. Unzip it and put the folder in your wp-contents/plugins folder

== Frequently Asked Questions ==

= Can I use this plugin to find the intersection of three categories? =

No. For now the plugin functions with only two category inputs

= Are you offering support for this plugin? =

No.

== Screenshots ==

1. A sample category list
2. Widget configuration
3. Sample widget
4. Sample cross-categorization list
5. Detail from the CatWalker Options under Settings > Writing
6. Sample Post Attributes listing
7. Sample Related Posts listing

== Upgrade Notice ==

= 1.3.1 +

Minor feature improvement. The widget can now optionally display empty along with the rest in both the public and admin interfaces. 

= 1.3 =

Major bugfix for CrossCat widget

= 1.2.3 =

Minor bugfix. 

= 1.2.2 =

Bugfix for the 'categories' shortcode.

= 1.2.1 =

A small but important tweak so that custom archive limits do not apply to in-post listings of related posts.

= 1.2 =

New feature: Custom post limit for category archive pages

= 1.1 =

Important bugfix!

= 1.0 =

Ready for a version bump, not so much because of any major change, but more because of the cumulative improvements over the last several versions. This latest release includes more options for the automated related-posts lists as well as sorting options for Category archive pages.

= 0.9 =
This version lets you automatically add lists of related posts to each entry. More configuration options for this feature to follow.

= 0.8 =
You can now include a list of assigned attributes at the end of each page or post.

= 0.7 =
List on a page or post all the pages or posts belonging to a particular category (or other taxonomy term). Set your taxonomy preference as a site-wide option.

= 0.6 =
Set the preference to use or not use the custom "Attributes" taxonomy on the Writing Settings page.

== Changelog ==

= 1.3.1 =

This version introduces a minor enhancement so that users have an option to set whether the widget dropdown includes empty categories alongside non-empty categories. You can set the preference in the widget itself on the admin side.

= 1.3 = 

Introduces a major bugfix for the crosscat widget. The widget now first attempts to redirect to a pretty URL if permalinks are in use. If permalinks are not in use, the widget runs a custom query to display the requested posts. There is a lot of variability and complication in the use of URL's, rewrites, and queries. There may well be some further niceties to sort out, but I hope this is a good start to fix a piece that was pretty broken.

= 1.2.3 =

Fixes bug that could limit the number of posts from a given category displayed on administrative side.

= 1.2.2 =

Fixed bug with the 'categories' shortcode

= 1.2.1 =

A small but important tweak so that custom archive limits do not apply to in-post listings of related posts.

= 1.2 =

Adds an option to set a custom number of posts on category archive pages.

= 1.1 =

Fixes a bug that caused the custom archive order to fail on some set-ups.

= 1.0 =
1. Introduces an option to exclude certain terms from the automated related-posts lists that can be added to the end of each item.
1. Option to exclude child-terms of a given term from related-posts lists
1. Options to sort Category Archives according to date or title, ascending or descending

= 0.9 =
Introduces option to include a list of related posts or pages at the end of each entry. Settings for this option will limit the functionality to specific categories or attributes, or to child-terms of specific categories or attributes. More configuration options will follow.

= 0.8 =
Introduces option to include a list of assigned attributes at the end of each post or page.

= 0.7 =
1. List on a page or post all the pages or posts belonging to a particular category (or other taxonomy term).
1. Set a default taxonomy preference for CatWalker shortcodes and widgets on the "Settings > Writing" page.

= 0.6 =
Use of the custom "Attributes" taxonomy is now optional. Change the setting on the Writing Settings page. 

= 0.5.2 =
Now using cookies as most reliable method to assure dropdown persistence.

= 0.5.1 = 
Dropdown lists now ordered by name, ascending

= 0.5 = 
First truly stable version, includes rewriting to pretty urls
