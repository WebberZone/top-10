---
slug: top-10-shortcodes
title: "Top 10 shortcodes"
products: [top-10]
sections: ["02-top-10-advanced"]
tags: [shortcode, top-10]
status: publish
featured_image: "https://webberzone.com/wp-content/uploads/2019/04/Shortcodes-in-Top-10-WordPress-plugin.webp"
---

Top 10 has two shortcodes that you can use to display the count of the current post and a list of the popular posts.

## [[tptn_views]]

This will display the number of visits of the current post, page or custom post type. It takes the following optional attributes:

- *count*: The type of count to display. Accepts `total` (default), `daily`, or `overall`
- *daily*: If set to 1, then the shortcode will return the number of *daily* views (shorthand for `count="daily"`)
- *format_number*: Set to 0 to disable number formatting (default is 1, which applies locale-based number formatting e.g. 1,000)
- *post_id*: Override the current post ID to display views for a specific post

You can wrap this shortcode in any HTML or within your text.

## [[tptn_list]]

This shortcode lets you insert the popular posts anywhere in your post content. It takes three main optional attributes:

- *limit*: Maximum number of posts to return. The actual number displayed may be lower depending on the category / post exclusion settings.
- *heading*: Set to 0 to disable the heading specified in **Title of popular posts:** under **Output options**
- *daily*: If set to 1, then the shortcode will return the daily popular posts list

In addition to these attributes, the shortcode can take all options as attributes, which can override the plugin settings. The following ones will affect the output of the popular posts list:

- *title_length*: A numerical value to limit the length of the titles in the display
- *offset*: A numerical value to indicate the number of posts to displace or pass over
- *post_types*: Comma-separated list of post types from which to select the top posts
- *daily_range* and *hour_range*: Use either or both to override the default custom period range. hour_range accepts a number between 0 and 23
- *how_old*: Number (in days) to only show posts that have been published within this range
- *thumb_width*: Thumbnail width. Accepts a number
- *thumb_height*: Thumbnail height. Accepts a number
- *show_author*: Display the author of the post. 1 or 0
- *show_date*: Display the published date of the post. 1 or 0
- *show_excerpt*: Display the excerpt. 1 or 0
- *disp_list_count*: Display the number of visits of the post
- *post_thumb_op*: Location of the post thumbnail. Values include `inline`, `after`, `text_only` and `thumbs_only`
- *exclude_post_ids*: Comma-separated list of IDs to exclude
- *link_nofollow*: Add nofollow attribute to links. 1 or 0
- *link_new_window*: Add _blank attribute to links. 1 or 0
- *include_cat_ids*: Comma-separated list of term_taxonomy_id – this can be both categories and custom taxonomies
