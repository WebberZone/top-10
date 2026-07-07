---
slug: popular-posts-blocks
title: "Blocks in Top 10"
products: [top-10]
sections: [02-top-10-advanced]
tags: [block,counter,featured-image,gutenberg,query-loop,top-10,views]
status: publish
order: 0
---

[kbtoc]

[Top 10](https://webberzone.com/plugins/top-10/) and [Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) include multiple blocks that allow you to display the popular posts in the block editor or the site editor. The sections below explain each block and how to use it.

[Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) brings an advanced *Query Loop block*, which allows you to display popular posts based on specified parameters. You can use the pre-built block patterns or create your own ones within posts or the site editor.

## Adding the Blocks

To add the Top 10 blocks, click the block editor's plus (+) icon. Search for "Top 10" to see the available blocks. Click on the desired block to insert it into your content area.

If you're using the pro version, you'll also have access to the "Top 10 Query Loop" and "Top 10 Featured Image" blocks, as well as the "Top 10 Popular Posts" and "Top 10 Post Count" blocks.

<figure class="wp-block-image aligncenter size-full">
<img src="https://webberzone.com/wp-content/uploads/2024/09/Top-10-Adding-the-Blocks.webp" class="wp-image-8312" loading="lazy" decoding="async" srcset="https://webberzone.com/wp-content/uploads/2024/09/Top-10-Adding-the-Blocks.webp 353w, https://webberzone.com/wp-content/uploads/2024/09/Top-10-Adding-the-Blocks-243x300.webp 243w" sizes="auto, (max-width: 353px) 100vw, 353px" width="353" height="436" alt="Insert Block menu in the Gutenberg editor in WordPress. Search for a Top 10 block. The block editor will display 2 blocks for the free version and 2 for the pro version." />
<figcaption>Insert a Top 10 block</figcaption>
</figure>

## Configuring the Top 10 Popular Posts block

The Top 10 Popular Posts block is a basic Gutenberg block that can replace the widget or [shortcode](https://webberzone.com/support/knowledgebase/top-10-shortcodes/) for displaying the popular posts. This block can be used in your posts, pages, or any other custom post type. You can also use it within the Site Editor if you are using a block theme.

The block lets you preview the popular posts directly in the block editor. You can customize various aspects of the block using the sidebar as follows:

<figure class="wp-block-table">
<table>
<colgroup>
<col style="width: 33%" />
<col style="width: 33%" />
<col style="width: 33%" />
</colgroup>
<thead>
<tr>
<th>Setting</th>
<th>Type</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td>Custom Period</td>
<td>Toggle (ON/OFF)</td>
<td>Toggle between displaying all-time popular posts or popular posts for a custom period, which can be set using the next two settings. For example, setting it to 1 day and 12 hours will display the popular posts from the last 36 hours.</td>
</tr>
<tr>
<td>Day(s)</td>
<td>Number</td>
<td>Enter the number of days to define the range for displaying popular posts.</td>
</tr>
<tr>
<td>Hour range</td>
<td>Number</td>
<td>Enter the number of hours within a day to define the range for displaying popular posts.</td>
</tr>
<tr>
<td>Number of Posts</td>
<td>Number</td>
<td>The maximum number of popular posts that will be displayed by the plugin.</td>
</tr>
<tr>
<td>Offset</td>
<td>Number</td>
<td>Number of posts to skip from the top.</td>
</tr>
<tr>
<td>Show Heading</td>
<td>Toggle</td>
<td>This displays a heading before the popular posts list.</td>
</tr>
<tr>
<td>Show excerpt</td>
<td>Toggle</td>
<td>Displays the excerpt of each post. By default, Top 10 uses the manually created post excerpt. If no post excerpt is found, the plugin generates the excerpt from the post content based on the excerpt length set in the global <a href="https://webberzone.com/support/knowledgebase/top-10-settings-list-options/#length-of-excerpt-in-words">Post List settings panel</a>.</td>
</tr>
<tr>
<td>Show author</td>
<td>Toggle</td>
<td>Display the author of each post</td>
</tr>
<tr>
<td>Show date</td>
<td>Toggle</td>
<td>Displays the published date of each post.</td>
</tr>
<tr>
<td>Show count</td>
<td>Toggle</td>
<td>Display the number of views alongside the post title.</td>
</tr>
<tr>
<td>Styles</td>
<td>Dropdown</td>
<td>You can choose from various styles to display popular posts. The free version of Top 10 offers three styles: No Styles, Text Only, and Left Thumbnails. The pro version adds an extra style to display posts in a grid.<br />
Selecting "Text only" will change the below option for the Thumbnail location to "No thumbnail".</td>
</tr>
<tr>
<td>Thumbnail location</td>
<td>Dropdown</td>
<td>This provides four self-explanatory options. "Before title", "After title", "Only thumbnail", "No thumbnail". Selecting "No thumbnail" will change the above option for Styles to "Text only".</td>
</tr>
<tr>
<td>Other attributes</td>
<td>Textarea field</td>
<td>Enter other attributes in a URL-style string-query. It supports any of the plugin's global settings e.g. <code>post_types=post,page&amp;link_nofollow=1&amp;exclude_post_ids=5,6</code>.</td>
</tr>
</tbody>
</table>
</figure>

### Pro Settings

Top 10 Pro users will see an additional section in the block settings sidebar that allows them to save the existing block settings as default or clear the defaults.

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

<figure class="wp-block-image size-large">
<img src="https://webberzone.com/wp-content/uploads/2024/05/Block-Editor-Toolbar-with-the-Replace-button-1024x114.webp" class="wp-image-8021" loading="lazy" decoding="async" srcset="https://webberzone.com/wp-content/uploads/2024/05/Block-Editor-Toolbar-with-the-Replace-button-1024x114.webp 1024w, https://webberzone.com/wp-content/uploads/2024/05/Block-Editor-Toolbar-with-the-Replace-button-300x34.webp 300w, https://webberzone.com/wp-content/uploads/2024/05/Block-Editor-Toolbar-with-the-Replace-button-768x86.webp 768w, https://webberzone.com/wp-content/uploads/2024/05/Block-Editor-Toolbar-with-the-Replace-button.webp 1164w" sizes="auto, (max-width: 1024px) 100vw, 1024px" width="1024" height="114" alt="Block Editor Toolbar with the Replace button" />
<figcaption aria-hidden="true">Block Editor Toolbar with the Replace button</figcaption>
</figure>

Once you do so, you'll see the "Replace" button, allowing you to select from the different patterns.

<figure class="wp-block-image size-large">
<img src="https://webberzone.com/wp-content/uploads/2024/09/Top-10-Query-Loop-block-Choose-a-pattern-1024x607.webp" class="wp-image-8317" loading="lazy" decoding="async" srcset="https://webberzone.com/wp-content/uploads/2024/09/Top-10-Query-Loop-block-Choose-a-pattern-1024x607.webp 1024w, https://webberzone.com/wp-content/uploads/2024/09/Top-10-Query-Loop-block-Choose-a-pattern-300x178.webp 300w, https://webberzone.com/wp-content/uploads/2024/09/Top-10-Query-Loop-block-Choose-a-pattern-768x455.webp 768w, https://webberzone.com/wp-content/uploads/2024/09/Top-10-Query-Loop-block-Choose-a-pattern.webp 1300w" sizes="auto, (max-width: 1024px) 100vw, 1024px" width="1024" height="607" alt="Top 10 Query Loop block - Choose a pattern for the popular posts in the block editor." />
<figcaption>Choose a Popular Posts Pattern</figcaption>
</figure>

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
5. **Site Icon**: Use the site icon configured in Settings \> General.

This feature ensures that your popular posts always have visually appealing featured images, even if a featured image hasn't been set.

## Top 10 Post Count

This block is available in both the free and paid versions of Top 10. It displays the number of views received by the current post. When used standalone, it shows the views of the current post. Alternatively, when used in any Query Loop block, it displays the views of each post.\
\
You can customize various aspects of the block using the sidebar as follows:

<figure class="wp-block-table">
<table>
<thead>
<tr>
<th>Setting</th>
<th>Type</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<tr>
<td>Counter type</td>
<td>Dropdown</td>
<td>Choose between displaying the Total or the Daily views. If you choose Daily, then two additional settings can be configured.</td>
</tr>
<tr>
<td>From</td>
<td>Date selector</td>
<td>Select the date from which the views are going to be counted.</td>
</tr>
<tr>
<td>To</td>
<td>Date selector</td>
<td>Select the date until which the views are going to be counted.</td>
</tr>
<tr>
<td>Number formatting</td>
<td>Toggle (ON/OFF)</td>
<td>Toggle this ON to display the number-formatted view count, e.g. 1,000. It uses the number formatting of the current locale.</td>
</tr>
<tr>
<td>Advanced mode</td>
<td>Toggle</td>
<td>Toggle this ON to display the "Advanced text" box which allows you to use placeholders to display the total, daily or overall counts.</td>
</tr>
<tr>
<td>Text before count</td>
<td>Textbox</td>
<td>This text will be displayed before the post views.</td>
</tr>
<tr>
<td>Text after count</td>
<td>Textbox</td>
<td>This text will be displayed after the post views.</td>
</tr>
<tr>
<td>Advanced text</td>
<td>Textarea field</td>
<td>This allows you to set the text to be displayed. This will ignore the "Counter type" setting above. Use <code>%totalcount%</code> or <code>%dailycount%</code> as placeholders for the count value.</td>
</tr>
<tr>
<td>Icon SVG HTML</td>
<td>Textbox</td>
<td>Enter the full <code>svg</code> code of the icon to be displayed. You can select from 20 different SVGs or find over 500k open-licensed SVGs at <a href="https://www.svgrepo.com" target="_blank" rel="noreferrer noopener">https://www.svgrepo.com</a>.</td>
</tr>
<tr>
<td>Icon location</td>
<td>Dropdown</td>
<td>Choose to display the icon before or after the text.</td>
</tr>
<tr>
<td>Icon size</td>
<td>Number and Dropdown</td>
<td>Enter the icon's size and unit (px, em, % or rem).</td>
</tr>
<tr>
<td>Padding</td>
<td>Number and Dropdown</td>
<td>These settings let you configure the icon's padding (not the views). You can enter a different value for the <span style="text-decoration: underline;">padding</span> for top, right, bottom and left or "link" them to display the same padding.</td>
</tr>
</tbody>
</table>
</figure>
