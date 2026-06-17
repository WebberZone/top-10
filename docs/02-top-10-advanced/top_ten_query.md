---
slug: top_ten_query
title: "Display popular posts with Top_Ten_Query"
products: [top-10]
sections: [02-top-10-advanced]
tags: [top-10]
status: publish
order: 0
---

Top 10 v3.0.0 introduced **Top_Ten_Query**, which works as a wrapper for <a href="https://developer.wordpress.org/reference/classes/wp_query/" target="_blank" rel="noreferrer noopener">WP_Query</a>. This brings all the power and flexibility of WP_Query to Top 10. If you're not familiar with WP_Query, I recommend <a href="https://developer.wordpress.org/reference/classes/wp_query/" target="_blank" rel="noreferrer noopener">reading the documentation</a>.

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

## Parameters

In addition to the <a href="https://developer.wordpress.org/reference/classes/wp_query/#parameters" rel="noreferrer noopener" target="_blank">WP_Query parameters</a>, Top_Ten_Query also takes these additional parameters.

<figure class="wp-block-table">
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Type</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>blog_id</code></td>
<td><code>array</code> or <code>string</code></td>
<td>Array or comma-separated string of blog IDs.</td>
</tr>
<tr>
<td><code>daily</code></td>
<td><code>bool</code></td>
<td>Set to <code>true</code> to fetch daily or custom period posts. Set to <code>false</code> for the overall popular posts.</td>
</tr>
<tr>
<td><code>daily_range</code></td>
<td><code>number</code></td>
<td>Enter the number of days, e.g. set it to 7 to fetch the popular posts in the past week.</td>
</tr>
<tr>
<td><code>include_cat_ids</code></td>
<td><code>array</code> or <code>string</code></td>
<td>Array or comma-separated string of categories or <code>term_taxonomy_id</code>s.</td>
</tr>
<tr>
<td><code>include_post_ids</code></td>
<td><code>array</code> or <code>string</code></td>
<td>Array or comma-separated string of post IDs to include.</td>
</tr>
<tr>
<td><code>offset</code></td>
<td><code>int</code></td>
<td>Offset the related posts returned by this number.</td>
</tr>
<tr>
<td><code>strict_limit</code></td>
<td><code>bool</code></td>
<td>If <code>false</code>, fetches up to 3× the limit to allow filtering.</td>
</tr>
</tbody>
</table>
</figure>
