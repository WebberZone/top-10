---
slug: top_ten_query
title: "Display popular posts with Top_Ten_Query"
products: [top-10]
sections: ["02-top-10-advanced"]
tags: [top-10]
status: publish
order: 0
featured_image: "https://webberzone.com/wp-content/uploads/2024/11/product-16384-banner-740x416-1.png"
---

[Top 10](https://webberzone.com/plugins/top-10/) v3.0.0 introduced **Top_Ten_Query**, which works as a wrapper for <a href="https://developer.wordpress.org/reference/classes/wp_query/" target="_blank" rel="noreferrer noopener">WP_Query</a>. This brings all the power and flexibility of WP_Query to Top 10. If you're not familiar with WP_Query, I recommend <a href="https://developer.wordpress.org/reference/classes/wp_query/" target="_blank" rel="noreferrer noopener">reading the documentation</a>.

## Standard Loop

```php
<?php

// The Query.
$the_query = new Top_Ten_Query( $args );

// The Loop.
if ( $the_query->have_posts() ) {
    echo '<ul>';
    while ( $the_query->have_posts() ) {
        $the_query->the_post();
        echo '<li>' . get_the_title() . '</li>';
    }
    echo '</ul>';
} else {
    // no posts found.
}
/* Restore original Post Data */
wp_reset_postdata();
```

## get_tptn_posts()

get_tptn_posts() is a wrapper to Top_Ten_Query. You can use it to retrieve an array of the popular posts. It also accepts the same `$args` as Top_Ten_Query.

## Data source

Queries follow the [Data source setting](https://webberzone.com/support/knowledgebase/koko-analytics-data-source/). In Hybrid and Koko Analytics only modes, lists use Koko Analytics page views. Date boundaries resolve to whole days, and overall counts cover only retained Koko Analytics data.

Top 10 Pro can query other network sites; sites without usable Koko Analytics data fall back to Top 10 counts. The free plugin restricts queries to the current site.

## Parameters

In addition to the <a href="https://developer.wordpress.org/reference/classes/wp_query/#parameters" target="_blank" rel="noreferrer noopener">WP_Query parameters</a>, Top_Ten_Query also takes these additional parameters.

| Parameter | Type | Description |
| --- | --- | --- |
| `blog_id` | `array` or `string` | Array or comma-separated string of blog IDs. Lists across several sites need Top 10 Pro; the free plugin uses the current site. |
| `daily` | `bool` | Set to `true` to fetch daily or custom period posts. Set to `false` for the overall popular posts. |
| `daily_range` | `number` | Enter the number of days, e.g. set it to 7 to fetch the popular posts in the past week. |
| `include_cat_ids` | `array` or `string` | Array or comma-separated string of categories or `term_taxonomy_id`s. |
| `include_post_ids` | `array` or `string` | Array or comma-separated string of post IDs to include. |
| `offset` | `int` | Offset the related posts returned by this number. |
| `strict_limit` | `bool` | If `false`, fetches up to 3× the limit to allow filtering. |

## Post types and statuses

Lists built from shortcode and block attributes pass through the same query, so Top_Ten_Query limits what it returns to content visitors are allowed to see. From Top 10 4.6.1:

- `post_type` and `post_types` accept only post types that WordPress lets visitors view, plus the post types selected in the plugin settings. Revisions are never returned. Post types that are not allowed are ignored, and if none are left, the post types in the settings are used.
- `post_status` accepts only public statuses, such as `publish`, and `inherit`, which attachments use. Other statuses, such as `draft`, `pending`, `future` and `private`, are ignored, and the default of `publish` and `inherit` is used instead.
- An attachment is returned only if it is not attached to a post, or its parent post is published and of a viewable post type.

To add a post type to lists built from `post_types` or the plugin settings, use the `top_ten_posts_post_types` filter, which runs after this check:

```php
add_filter(
	'top_ten_posts_post_types',
	function ( $post_types ) {
		$post_types[] = 'my_post_type';
		return $post_types;
	}
);
```
