---
slug: popular-posts-blocks
title: "Blocks in Top 10"
products: [top-10]
sections: ["02-top-10-advanced"]
tags: [advanced-block, block, counter, featured-image, gutenberg, query-loop, top-10, views]
status: publish
order: 0
toc: true
---

[toc]

[Top 10](https://webberzone.com/plugins/top-10/) and [Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) include multiple blocks that allow you to display the popular posts in the block editor or the site editor. The sections below explain each block and how to use it.

[Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) brings an advanced *Query Loop block*, which allows you to display popular posts based on specified parameters. You can use the pre-built block patterns or create your own ones within posts or the site editor.

## Adding the Blocks

To add the Top 10 blocks, click the block editor's plus (+) icon. Search for "Top 10" to see the available blocks. Click on the desired block to insert it into your content area.

If you're using the pro version, you'll also have access to the "Top 10 Popular Posts — Advanced", "Top 10 Query Loop" and "Top 10 Featured Image" blocks, as well as the "Top 10 Popular Posts" and "Top 10 Post Count" blocks.

![Insert Block menu in the Gutenberg editor in WordPress. Search for a Top 10 block. The block editor will display 2 blocks for the free version and 2 for the pro version.](https://webberzone.com/wp-content/uploads/2024/09/Top-10-Adding-the-Blocks.webp)

## Configuring the Top 10 Popular Posts block

The Top 10 Popular Posts block is a basic Gutenberg block that can replace the widget or [shortcode](https://webberzone.com/support/knowledgebase/top-10-shortcodes/) for displaying the popular posts. This block can be used in your posts, pages, or any other custom post type. You can also use it within the Site Editor if you are using a block theme.

The block lets you preview the popular posts directly in the block editor. You can customize various aspects of the block using the sidebar as follows:

| Setting | Type | Description |
| --- | --- | --- |
| Custom Period | Toggle (ON/OFF) | Toggle between displaying all-time popular posts or popular posts for a custom period, which can be set using the next two settings. For example, setting it to 1 day and 12 hours will display the popular posts from the last 36 hours. |
| Day(s) | Number | Enter the number of days to define the range for displaying popular posts. |
| Hour range | Number | Enter the number of hours within a day to define the range for displaying popular posts. |
| Number of Posts | Number | The maximum number of popular posts that will be displayed by the plugin. |
| Offset | Number | Number of posts to skip from the top. |
| Show Heading | Toggle | This displays a heading before the popular posts list. |
| Show excerpt | Toggle | Displays the excerpt of each post. By default, Top 10 uses the manually created post excerpt. If no post excerpt is found, the plugin generates the excerpt from the post content based on the excerpt length set in the global [Post List settings panel](https://webberzone.com/support/knowledgebase/top-10-settings-list-options/#length-of-excerpt-in-words). |
| Show author | Toggle | Display the author of each post |
| Show date | Toggle | Displays the published date of each post. |
| Show count | Toggle | Display the number of views alongside the post title. |
| Styles | Dropdown | You can choose from various styles to display popular posts. The free version of Top 10 offers three styles: No Styles, Text Only, and Left Thumbnails. The pro version adds an extra style to display posts in a grid. Selecting "Text only" changes the Thumbnail location option below to "No thumbnail". |
| Thumbnail location | Dropdown | This provides four self-explanatory options. "Before title", "After title", "Only thumbnail", "No thumbnail". Selecting "No thumbnail" will change the above option for Styles to "Text only". |
| Other attributes | Textarea field | Enter other attributes in a URL-style string-query. It supports any of the plugin's global settings e.g. `post_types=post,page&link_nofollow=1&exclude_post_ids=5,6`. |

### Pro Settings

Top 10 Pro users will see an additional section in the block settings sidebar that allows them to save the existing block settings as default or clear the defaults.

## Top 10 Popular Posts — Advanced block *(Pro only)*

The Advanced block lets you design the popular posts list visually. You edit one card, and every popular post in the list uses that design. It needs the **Top 10 Popular Posts — Advanced block** feature, which is enabled by default on the [Features tab](https://webberzone.com/support/knowledgebase/top-10-settings-features-options/).

### Choose a layout

When you insert the block, pick one of six layout patterns. Use **Change pattern** in the block toolbar to switch later.

- **Text list**: Post titles only.
- **Small image beside title**: A small image to the left of each title.
- **Image above title**: Cards with an image, title, and view count.
- **Image, title and excerpt**: Cards with an image, title, date, view count, and excerpt.
- **Horizontal image and text**: An image beside the title, date, view count, and excerpt.
- **Rounded thumbnails**: Square images with rounded corners beside each title.

### Card blocks

The card is built from these blocks, which you can add, remove, move, and style like any other block:

| Block | Description |
| --- | --- |
| Popular Post Title | The post title, with a heading level and an optional link. |
| Popular Post Image | The post's image, chosen from the featured image and the sources you enable: a custom image, a meta key, the first image in the content, the default image, or the site icon. |
| Popular Post Date | The post date, with an optional custom format. |
| Popular Post Excerpt | An excerpt of the post. Default length: 24 words. |
| Popular Post Views | The view count for the selected popularity period, followed by a suffix. Default suffix: `views`. |

The **Popular Posts Template** block holds the card. It sets the maximum number of columns, the minimum card width, and the gap between cards. The **Popular Posts Empty State** block holds the content shown when no popular posts are found.

### Block settings

| Panel | Settings |
| --- | --- |
| Popularity period | **Count views from**: **All time**, **Recent days and hours**, or **Date range**. A date range uses the site timezone; leave the end date empty to include all later views. |
| Results | Number of posts (1–50), order by, order, and posts to skip. **Use global count and post types** resets the number of posts and post types to the plugin settings. |
| Filters | Post types, authors, and terms to include or exclude for each public taxonomy. |
| Manual selections | Specific posts to include or exclude. |
| Empty results | **Hide the entire section** (default) or **Show the empty state**. |
| Advanced custom-field conditions | Custom-field conditions that posts must match. |

Lists built with the Top 10 Query Loop block can be converted to the Advanced block using the block toolbar's **Transform** menu.

## Using the Top 10 Query Loop Block (Pro version)

If you are not familiar with the Core Query Loop Block, the section below will help you start using it.

<figure class="wp-block-video">
</figure>

The above video gives you a basic preview of inserting the Top 10 Query Loop block, the various settings, and the output on your site's front end.

The block allows you to modify the output and layout flexibly. You have a few ready-made patterns currently included, with more coming in future versions. Here is a short guide on how to use it:

### 1. Configuring the Query Loop Block

The Query Loop block allows you to customize the query that will be used to retrieve the popular posts. You can configure the following settings:

- **Number of Posts**: Enter the number of posts to display per page.
- **Post Types**: Select one or multiple post types to include in the popular posts.
- **Offset**: Set the number of posts to skip from the beginning of the results.
- **Order By**: Choose how the results should be sorted (e.g., visits, date, title, author, etc.).
- **Order**: Toggle between Ascending and Descending.
- **Filters – Taxonomy**: Filter the results by specific taxonomies (e.g., category, tag).
- **Filters – Authors**: Filter the results by specific authors.

### 2. Customize the Layout

When you insert the Query Loop block, the plugin selects a default list layout with just the post title.

The Query Loop block provides several layout options (patterns) to choose from, including:

- **Numbered List**: Display the posts or pages in a vertical numbered list.
- **Grid**: Text, Excerpt, Date and Post Views in a simple grid.
- **Left Thumbnail**: As the name suggests, the thumbnail is displayed in the left column, and the right column contains the title, date, post views and excerpt.
- **Rounded Thumbs**: Displays the posts with rounded thumbnails containing the featured image and the post title.
- **Image, Title, Excerpt**: Displays popular posts with featured images, post titles, and excerpts.

To select a pattern, you can select the block using the navigation bar at the bottom left of the editor or the Parent block in the top/hover toolbar.

![Block Editor Toolbar with the Replace button](https://webberzone.com/wp-content/uploads/2024/05/Block-Editor-Toolbar-with-the-Replace-button-1024x114.webp)

Once you do so, you'll see the "Replace" button, allowing you to select from the different patterns.

![Top 10 Query Loop block - Choose a pattern for the popular posts in the block editor.](https://webberzone.com/wp-content/uploads/2024/09/Top-10-Query-Loop-block-Choose-a-pattern-1024x607.webp)

### 4. Add Additional Blocks

Within the Query Loop block, you can add additional blocks to display specific content for each post or page, such as:

- **Post Title**: Show the title of the post or page.
- **Post Content**: Display the full content of the post or page.
- **Post Date**: Show the date the post or page was published.
- **Post Featured Image**: Display the featured image of the post or page. Top 10 Pro includes an [advanced Featured Image Block](#top-10-featured-image-block-pro-version).
- **Post Views**: Use the [Top 10 Post Count block](#top-10-post-count) to display the number of views the post has received.

You can arrange and style these blocks to create a visually appealing and informative layout for your content.

## Top 10 Featured Image Block (Pro version)

Top 10 Pro offers enhanced flexibility and reliability for displaying featured images in your posts. This can be used for the popular posts list and across your WordPress site that uses the Block or the Site editor.

If a featured image is not explicitly set for a post, the plugin will automatically fall back to the following configurable options:

1. **Custom Image**: Select an image from the Media Library as the default featured image.
2. **First Image in the Post Content:** If the post contains images, the first image encountered will be used as the featured image.
3. **Meta Key:** If a specific meta key is defined, the value associated with that key will be used as the featured image URL. The meta key needs to contain the full URL of the image to be used.
4. **Default Image:** The default image can be specified if no image is found using the above methods.
5. **Site Icon**: Use the site icon configured in Settings > General.

This feature ensures that your popular posts always have visually appealing featured images, even if a featured image hasn't been set.

## Top 10 Post Count

This block is available in both the free and paid versions of Top 10. It displays the number of views received by the current post. When used standalone, it shows the views of the current post. Alternatively, when used in any Query Loop block, it displays the views of each post.
You can customize various aspects of the block using the sidebar as follows:

| Setting | Type | Description |
| --- | --- | --- |
| Counter type | Dropdown | Choose between displaying the Total or the Daily views. If you choose Daily, then two additional settings can be configured. |
| From | Date selector | Select the date from which the views are going to be counted. |
| To | Date selector | Select the date until which the views are going to be counted. |
| Number formatting | Toggle (ON/OFF) | Toggle this ON to display the number-formatted view count, e.g. 1,000. It uses the number formatting of the current locale. |
| Advanced mode | Toggle | Toggle this ON to display the "Advanced text" box which allows you to use placeholders to display the total, daily or overall counts. |
| Text before count | Textbox | This text will be displayed before the post views. |
| Text after count | Textbox | This text will be displayed after the post views. |
| Advanced text | Textarea field | This allows you to set the text to be displayed. This will ignore the "Counter type" setting above. Use `%totalcount%` or `%dailycount%` as placeholders for the count value. |
| Icon SVG HTML | Textbox | Enter the full `svg` code of the icon to be displayed. You can select from 20 different SVGs or find over 500k open-licensed SVGs at <a href="https://www.svgrepo.com" target="_blank" rel="noreferrer noopener">https://www.svgrepo.com</a>. |
| Icon location | Dropdown | Choose to display the icon before or after the text. |
| Icon size | Number and Dropdown | Enter the icon's size and unit (px, em, % or rem). |
| Padding | Number and Dropdown | These settings let you configure the icon's padding (not the views). You can enter a different value for the padding for top, right, bottom and left or "link" them to display the same padding. |
