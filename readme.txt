=== CatWalker ===
Contributors: kwiliarty
Donate link: none
Tags: categories, intersections, widgets, custom taxonomies
Requires at least: 3.1
Tested up to: 3.1.2
Stable tag: 0.6

List categories or cross-categorizations in page or post contents. Let users search for the intersection of two categories.

== Description ==

The catWalker plugin lets you do more with WordPress categories. The plugin has these main uses.

1. Generate a customizable list of categories within the contents of a page or post
1. Create a configurable widget that will make it easy for visitors to find posts or pages at the intersection of two categories
1. Generate a list of cross-categorizations on a page or post
1. List the posts or pages from a given category on a page or post

In addition, users can opt to use a hierarchical custom taxonomy (called "Attributes") that applies to Pages as well as Posts.

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

The Cross Categorizer widget includes two configurable dropdown lists of categories. Choose two categories, then click "Search" to view the posts or pages that belong to both.

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

== Upgrade Notice ==

= 0.7 =
List on a page or post all the pages or posts belonging to a particular category (or other taxonomy term). Set your taxonomy preference as a site-wide option.

= 0.6 =
Set the preference to use or not use the custom "Attributes" taxonomy on the Writing Settings page.

== Changelog ==

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
