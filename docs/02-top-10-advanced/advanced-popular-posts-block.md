---
slug: advanced-popular-posts-block
title: "Advanced Popular Posts block"
products: [top-10]
sections: ["02-top-10-advanced"]
tags: [block, pro, top-10]
status: publish
order: 0
toc: true
---

[toc]

[Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) 4.6.0 adds **Top 10 Popular Posts — Advanced**. Use it to design a repeated post card with editable blocks and choose which posts appear by popularity, period, and filters.

## Add the block

1. Enable **Top 10 Popular Posts — Advanced block** under [Top 10 Settings – Features](https://webberzone.com/support/knowledgebase/top-10-settings-features-options/).
2. Open a post, page, or Site Editor template.
3. Search the block inserter for **Top 10 Popular Posts — Advanced**.
4. Insert the block and choose a layout pattern.

The block queries published, publicly viewable posts without passwords on the current site. Its editor preview requires **REST API endpoints** to be enabled; front-end rendering still works when those endpoints are disabled.

## Choose a pattern and edit the cards

Six patterns provide starting layouts:

| Pattern | Layout |
| --- | --- |
| Text list | Linked titles in one column. |
| Small image beside title | A thumbnail beside each title. |
| Image above title | Image, title, and view count. |
| Image, title and excerpt | Image, title, date, count, and excerpt. |
| Horizontal image and text | Image beside title, date, count, and excerpt. |
| Rounded thumbnails | Rounded images beside titles. |

Use **Change pattern** to choose another starting layout. Within **Popular Posts Template**, edit one card to change every result. You can arrange the Popular Post Image, Title, Date, Excerpt, and Count blocks and their surrounding Groups.

The **Layout** controls set columns (1–6), minimum card width (120–600 pixels), and card gap (0–100 pixels). A template defaults to 3 columns, 220 pixels minimum width, and a 24-pixel gap; patterns can set their own column count. The layout adapts to the available width.

## Choose the popularity period

**Count views from** defaults to **All time**. Choose **Recent days and hours** for a rolling window, or **Date range** to enter a start and end time in the site's timezone. Leave the end date empty to include all later views.

The rolling controls allow 0–365 days and 0–23 additional hours. They initially use the [configured custom period](https://webberzone.com/support/knowledgebase/top-10-settings-counter-tracker-options/). A reversed or invalid date range returns no results.

When [Koko Analytics supplies the data](https://webberzone.com/support/knowledgebase/koko-analytics-data-source/), date boundaries resolve to whole days. Overall counts include only the data Koko Analytics retains.

## Select results and filters

**Number of posts** allows 1–50 results and initially uses the plugin's global limit, capped at 50. **Skip posts** allows an offset of 0–500. Results default to descending view counts.

You can change **Order by** to date, last modified, title, author, random, menu order, post ID, or a custom-field value. Custom-field ordering needs a key, either entered directly or supplied by a custom-field condition.

Filter results by public post types, authors, and included or excluded taxonomy terms. Manual selections let you include or exclude specific posts. Global post exclusions also apply. **Advanced custom-field conditions** supports up to ten conditions combined with AND or OR.

Use **Use global count and post types** to remove the block's overrides for those two settings. The block remains limited to the current site even when Top 10 Pro can build other lists across a network.

For example, choose **Recent days and hours**, set **Days** to 7 and **Hours** to 0, select posts, and order by descending views to show the week's most popular posts. Choose **Image, title and excerpt** and edit the card to adjust the presentation.

## Empty results

The default **When no popular posts are found** behavior is **Hide the entire section**. Choose **Show the empty state** to display the content inside **Popular Posts Empty State** instead. Edit that content to provide your own message.

## Convert an existing Top 10 Query Loop

Supported Top 10 Query Loop blocks can be transformed into the Advanced block through the block toolbar. The conversion preserves supported query settings and card content.

The transform is unavailable for unsupported setups, including inherited queries, search queries, pagination, nested queries, and Post Content blocks. A supported conversion also needs one Post Template, at most 50 results, and an offset no greater than 500. Existing Query Loop blocks continue to work without conversion.

## See also

- [Blocks in Top 10](https://webberzone.com/support/knowledgebase/popular-posts-blocks/)
- [Top 10 Settings – Features](https://webberzone.com/support/knowledgebase/top-10-settings-features-options/)
