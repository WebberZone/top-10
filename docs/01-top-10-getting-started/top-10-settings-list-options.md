---
slug: top-10-settings-list-options
title: "Top 10 Settings – Popular Post list options"
products: [top-10]
sections: [01-top-10-getting-started]
tags: [settings,top-10,top-10-settings]
status: publish
order: 0
---

[kbtoc]

The **Popular Post list options** section contains a set of options that allow you to fine tune the settings of the list of popular articles from you blog. These are global settings and many of these can be overridden in the widget or the shortcode.

<figure class="wp-block-image is-resized">
<a href="https://webberzone.com/wp-content/uploads/2016/12/Top-10-Post-list-options-v2.6.1.png"><img src="http://webberzone.com/support/wp-content/uploads/sites/4/2016/12/Top-10-Post-list-options-v2.6.1-423x1024.png" class="wp-image-87" style="aspect-ratio:1;width:1226px;height:auto" decoding="async" alt="Top 10 - Post list options" /></a>
<figcaption>Top 10 – Post list options</figcaption>
</figure>

## Use global settings in block *(Pro only)*

If activated, the settings from this page are automatically inserted in the Popular Posts block. This also applies to existing blocks which do not have any attributes set if the post is edited.

## Number of popular posts to display

Maximum number of posts that will be displayed in the list. This is a global setting and will used if you don't specify the number of posts in the widget or shortcode.

## Published age of posts

This options allows you to only show posts that have been published within the above day range. Applies to both overall posts and daily posts lists.

e.g. 365 days will only show posts published in the last year in the popular posts lists. Enter 0 for no restriction.

This setting is different from the Custom Period Range under the [General tab](https://webberzone.com/support/knowledgebase/top-10-settings-general-options/).

## Post types to include

Top 10 will detect the various post types in your WordPress site and allow you to pick which ones you'd like to include in the popular posts' lists.

At least one option should be selected. This field can be overridden using a comma separated list of post types when using the manual display.

## Exclude Front page and Posts page

If you have designated specific pages for your Front page and Posts page via Settings > Reading, they will be tracked like any other page. Enable this option to exclude them from appearing in the popular posts lists. Note that tracking will still occur.

## Exclude current post

Enabling this will exclude the current post being browsed from being displayed in the popular posts list.

## Post/page IDs to exclude

Enter a comma separated list of post, page or custom post type IDs. This is a global setting and will be excluded from all popular post lists.

## Exclude Categories

Comma separated list of category slugs. The field above has an autocomplete so start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.

## Exclude category IDs

Read-only field automatically populated based on the above input when the settings are saved. These might differ from the IDs visible in the Categories page, which uses the term_id. Top 10 uses the term_taxonomy_id, which is unique to this taxonomy.

## Include parent categories *(Pro only)*

Exclude popular posts from parent categories or all ancestors for nested categories: None, Only parent categories, or All ancestors.

## Exclude on Categories

Comma separated list of category slugs. Popular posts lists will not be displayed on pages/posts belonging to these categories. The field above has an autocomplete so start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.

## Heading of posts

Enter the title of the popular post list. You can use basic HTML code in this field. This title is placed before the list of popular posts.

## Heading of posts for daily/custom period lists

Same as the above option but used when displaying the popular lists with the daily flag activated.

## Show when no posts are found

Display either a blank output or alternatively display a custom message.

## Custom text

Enter the custom text that will be displayed if the second option is selected above

## Show post excerpt

You can optionally display the post excerpt along with the post title when displaying the post lists.

Top 10 will use the post excerpt that you set when editing a post. If this is missing, then it will create an excerpt from the post content.

## Length of excerpt (in words)

Set how many words of the excerpt you'd like to display. By default this is 10 words.

## Show date

Display the date the post was created alongside the title. Uses the date format of your WordPress install

## Show author

Similar to the excerpt, display the post author in the list. Will be displayed in this format: "by FNAME LNAME".

## Show number of views

Display the number of views alongside the post title. It is displayed in brackets. You can remove the brackets using a <a href="https://gist.github.com/ajaydsouza/9f04c26814414a57fab4" target="_blank" rel="noopener noreferrer">filter function</a> in your theme's functions.php.

## Limit post title length (in characters)

Long post titles can distort the list output. This option sets the maximum number of characters that will be displayed. Top 10 will automatically crop words and add an ellipsis "…" at the end.

## Open links in new window

Adds the `target="_blank"` attribute to the links in order to open the links in a new page.

## Add nofollow attribute to links in the list

Adds the `rel="nofollow"` attribute to the links in order to tell search engines not to follow these posts.

## Customize the list HTML

The four set of options will allow you to customize what HTML is used to create the post lists. By default the post lists are created using an unordered list. You shouldn't need to change these as you can use CSS to style the list as required.
