# Top 10  - Popular posts plugin for WordPress

**Stable tag**: trunk
**Requires at least**: 3.0
**Tested up to**: 3.9
**License**: [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

Track daily and total visits on your blog posts. Display the count as well as popular and trending posts.

## Description

WordPress doesn't count page views by default. <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10</a> will count the number of page views on your single posts on a daily as well as overall basis. You can then display the page view count on individual posts and pages as well as display a list of most popular posts based on page views.

Includes a sidebar widget to display the popular posts. And, all settings can be configured from within your WordPress Admin area itself! You can choose to disable tracking of author visits on their own posts.

### Features

* **Page counter**:Counts daily and total page views on single posts, pages and *custom post types*
* **Display the count**: Customize the text that can be displayed
* **Show off popular posts**: Display a list of daily and/or overall popular posts by page count. You can choose how many posts are to be displayed plus loads of other customisation options
* **Widget ready**: Sidebar widgets available for daily popular and overall popular posts. Highly customizable widgets to control what you want to display in the list of posts
* **Customisable output**: 
	* Output wrapped in CSS classes that allows you to style the list. You can enter your custom CSS styles from within WordPress Admin area under "Custom Styles"
	* Pick your own HTML tags to use for displaying the output in case you don't prefer the default `list` format
* **Thumbnail support**
	* Support for WordPress post thumbnails
	* Auto-extract the first image in your post to be displayed as a thumbnail
	* Manually enter the URL of the thumbnail via <a href="http://codex.wordpress.org/Custom_Fields">WordPress meta fields</a>
	* Use timthumb to crop and resize images
* **Exclusions**: Exclude posts from select categories from appearing in the top posts list. Also exclude posts by ID from appearing in the list
* **Admin interface**: View list of daily and/or overall popular posts from within the dashboard. Top 10 will also add two sortable columns to your All Posts and All Pages pages in your WordPress Admin area
* **Clean uninstall**: If you choose to delete the plugin from within WP-Admin, the plugin will remove all its data. But why would you?
* **Works with caching plugins** like WP-Super-Cache, W3 Total Cache or Quick Cache


## Screenshots
![General Options](https://raw.github.com/ajaydsouza/top-10/master/screenshot-1.png)
_Top 10 settings page - General Options._



## Installation

1. Download the plugin

2. Extract the contents of top-10.zip to wp-content/plugins/ folder. You should get a folder called top-10.

3. Activate the Plugin in WP-Admin. 

4. Go to **Top 10** to configure

5. Go to **Appearance &raquo; Widgets** to add the Popular Posts sidebar widget to your theme

6. Go to **Top 10 &raquo; Overall Popular Posts** and **Top 10 &raquo; Daily Popular Posts** to view the list of popular posts

Alternatively, search for **Top 10** from Plugins &raquo; Add New within your WordPress admin.


## Frequently Asked Questions

If your question isn't listed here, please post a comment at the <a href="http://wordpress.org/support/plugin/top-10">WordPress.org support forum</a>. I monitor the forums on an ongoing basis. If you're looking for more advanced support, please see <a href="http://ajaydsouza.com/support/">details here</a>.

### How can I customise the output?

Several customization options are available via the Settings page in WordPress Admin. You can access this via <strong>Settings &raquo; Top 10</strong>

The plugin also provides you with a set of CSS classes that allow you to style your posts by adding code to the *style.css* sheet. In a future version, I will be adding in CSS support within the plugins Settings page.

The following CSS classes / IDs are available:

* **tptn_related**: ID of the main wrapper `div`. This is only displayed on singular pages, i.e. post, page and attachment

* **tptn_related**: Class of the main wrapper `div`. If you are displaying the related posts on non-singular pages, then you should style this

* **tptn_title**: Class of the `span` tag for title of the post

* **tptn_excerpt**: Class of the `span` tag for excerpt (if included)

* **tptn_thumb**: Class of the post thumbnail `img` tag

* **tptn_list_count**: Class of the `span` tag for post count in top posts list

* **tptn_counter**: Class of the `div` tag that wraps the post count that is driven by the field "Format to display the count in: " under 'Output Options'

For more information, please visit http://ajaydsouza.com/wordpress/plugins/top-10/

### How does the plugin select thumbnails?

The plugin selects thumbnails in the following order:

1. Post Thumbnail image: The image that you can set while editing your post in WordPress &raquo; New Post screen

2. Post meta field: This is the meta field value you can use when editing your post. The default is `post-image`

3. First image in the post: The plugin will try to fetch the first image in the post

3. Video Thumbnails: Meta field set by <a href="https://wordpress.org/extend/plugins/video-thumbnails/">Video Thumbnails</a>

4. Default Thumbnail: If enabled, it will use the default thumbnail that you specify in the Settings screen

The plugin uses <a href="http://www.binarymoon.co.uk/projects/timthumb/">timthumb</a> to generate thumbnails by default. Depending on the configuration of your webhost you might run into certain problems. Please check out <a href="http://www.binarymoon.co.uk/2010/11/timthumb-hints-tips/">the timthumb troubleshooting page</a> regarding permission settings for the folder and files.

### Manual install

You may choose to not display the post count automatically. If you do so, then in order to display the post count, you will need to add `<?php if(function_exists('echo_tptn_post_count')) echo_tptn_post_count(); ?>`.

In order to display the most popular posts, you will need to add `<?php if(function_exists('tptn_show_pop_posts')) tptn_show_pop_posts(); ?>`.

In order to display the most popular posts, you will need to add `<?php if(function_exists('tptn_show_daily_pop_posts')) tptn_show_daily_pop_posts(); ?>`.

You can also use the WordPress Widgets to display the popular posts in your sidebar / other widgetized areas of your theme.

### Can this plugin replace Google Analytics?

Never. This plugin is designed to only track the number of pageviews on your blog posts and display the same. It cannot replace Google Analytics or any other full fledged statistics application.

### How does the scheduled maintenance work maintenance work?

When you enabled the scheduled maintenance, Top 10 will create a cron job that will run at a predefined interval and truncate the `wp_top_ten_daily` table. 
*Note: If you enable this option, WordPress will execute this job when it is scheduled the first time*

### How to make the columns on the Custom Posts pages sortable?

Add the following code to your functions.php file of your theme.

`
add_filter( 'manage_edit-{$cpt}_sortable_columns', 'tptn_column_register_sortable' );
`

Replace `{$cpt}` by the slug of your custom post type. E.g. to make the columns on your 'projects' post type sortable, you will need to add:
`
add_filter( 'manage_edit-projects_sortable_columns', 'tptn_column_register_sortable' );
`
