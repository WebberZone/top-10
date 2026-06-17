---
slug: using-and-customising-top-10
title: "Using and Customising Top 10"
products: [top-10]
sections: [01-top-10-getting-started]
tags: [top-10]
status: publish
order: 0
---

Top 10 can be used in four ways to display the popular posts:

1.  **Widget**: simply drag and drop "Popular Posts \[Top 10\]" widget into your theme's sidebar and configure it
2.  **Shortcode**: `[tptn_list]`, so you can embed it inside a post or a page. [View details on the shortcodes](https://webberzone.com/support/knowledgebase/top-10-shortcodes/)
3.  **Template tags**: Use `tptn_show_pop_posts()` to display the popular posts anywhere on your theme. See the template tags section below
4.  **Top_Ten_Query**: You can use this for a more advanced implementation. [Read more details on Top_Ten_Query](https://webberzone.com/support/knowledgebase/top_ten_query/)

## Template Tags

The below functions need to be added by editing your theme files where you wish to display them.

Display the post count with `<?php if ( function_exists( 'echo_tptn_post_count' ) ) { echo_tptn_post_count(); } ?>`

Display the overall most popular posts with `<?php if ( function_exists( 'tptn_show_pop_posts' ) ) { tptn_show_pop_posts(); } ?>`

Display the daily/custom period popular posts with `<?php if ( function_exists( 'tptn_show_daily_pop_posts' ) ) { tptn_show_daily_pop_posts(); } ?>`

## Customizing the output

Several customization options are available via the Settings page in WordPress Admin. You can access this via ****Top 10 » Settings****

The main CSS classes include:

- **tptn_posts** and **tptn_posts_daily**: Class of the main wrapper `div`. If you are displaying the related posts on non-singular pages, then you should style this
- **tptn_title**: Class of the `span` tag for title of the post
- **tptn_excerpt**: Class of the `span` tag for excerpt (if included)
- **tptn_thumb**: Class of the post thumbnail `img` tag
- **tptn_list_count**: Class of the `span` tag for post count in top posts list
- **tptn_counter**: Class of the `div` tag that wraps the post count that is driven by the field "Format to display the count in: " under 'Output Options'
