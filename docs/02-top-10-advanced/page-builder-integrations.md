---
slug: page-builder-integrations
title: "Page Builder Integrations for Top 10 Pro"
products: [top-10]
sections: ["02-top-10-advanced"]
tags: [top-10, pro, bricks, elementor, wpbakery, page-builder]
status: publish
order: 0
toc: true
---

[toc]

[Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) v4.5.0 adds native "Popular Posts (Top 10)" elements for three popular page builders: **WPBakery Page Builder**, **Elementor**, and **Bricks Builder**. Each integration renders through the same [[tptn_list]] shortcode used everywhere else in the plugin, so the output and behavior match your existing popular posts lists.

> [!NOTE]
> ⓘ These integrations only appear when the corresponding builder plugin/theme is active, Top 10 Pro (not the free version) is active, and the **Page builder integrations** feature is enabled on the **Features** tab of Top 10 settings (on by default). No further configuration is needed to register the element.

## WPBakery Page Builder

If WPBakery Page Builder (`js_composer`) is active, Top 10 Pro registers a **Popular Posts (Top 10)** element under its own **WebberZone** category in the Add Element panel.

### Adding the element

1. Edit a post or page with WPBakery Page Builder.
2. Click the **Add Element** (+) button to open the Add Element panel.
3. Select the **WebberZone** category.
4. Click **Popular Posts (Top 10)** to insert the element.

### Configuring the element

The element's settings panel is organized into groups: **General**, **Query**, **Display**, **Advanced**, and **Design Options**. The first four map to the [shared controls](#shared-controls) below. **Design Options** is WPBakery's own group and holds the **Extra class name** field and a **CSS box** editor.

## Elementor

If Elementor is active, Top 10 Pro registers a **Popular Posts (Top 10)** widget under its own **WebberZone** category in the widget panel.

### Adding the widget

1. Edit a page with Elementor.
2. Open the widget panel and search for "Popular Posts", or browse to the **WebberZone** category.
3. Drag the **Popular Posts (Top 10)** widget onto the page.

### Configuring the widget

The widget's **Content** tab contains the **General**, **Query**, **Display**, and **Output** sections described in [shared controls](#shared-controls) below.

## Bricks Builder

If the Bricks theme is active, Top 10 Pro registers a **Popular Posts (Top 10)** element under its own **WebberZone** category, with support for Bricks dynamic data tags on the text controls (**Title**, **Custom period title**, **Post types**, **Include category term IDs**, **Include post IDs**, **Exclude post IDs**, **Exclude category slugs**, **Other attributes**).

### Adding the element

1. Edit a page with the Bricks builder.
2. Open the Elements panel and search for "Popular Posts", or browse to the **WebberZone** category.
3. Drag the **Popular Posts (Top 10)** element onto the canvas.

### Configuring the element

The element's **Content** tab exposes the same **General**, **Query**, **Display**, and **Output** groups as Elementor — see [shared controls](#shared-controls) below. Click the lightning-bolt icon next to a supported text control to insert a dynamic tag, e.g. `Most viewed in {term_name}`.

## Shared controls

All three elements expose the same set of controls, grouped the same way:

**General**

- **Title** — heading text shown above the list. Leave blank to use the plugin setting.
- **Custom period title** — heading text shown when **Custom period** is enabled below. Leave blank to use the plugin setting.
- **Show heading** — toggle the heading on or off.
- **Custom period** — switch from all-time counts to a custom date range.
- **Days in period** / **Hours in period** — only shown when **Custom period** is on. Leave blank to use the plugin's [default custom period range](https://webberzone.com/support/knowledgebase/top-10-settings-counter-tracker-options/).
- **Number of posts** — maximum popular posts to display.
- **Offset** — number of posts to skip from the top.
- **Published within (days)** — only show posts published within this many days. Leave blank to use the plugin setting; `0` means no restriction.

**Query**

- **Post types** — comma-separated post type slugs to include (Bricks exposes this as a multi-select of registered post types instead).
- **Include category term IDs** — comma-separated `term_taxonomy_id`s. Only posts in these categories are included.
- **Include post IDs** — comma-separated post IDs to force-include.
- **Exclude current post** — leave the currently viewed post out of the results.
- **Exclude post IDs** — comma-separated post IDs to exclude.
- **Exclude category slugs** — comma-separated category slugs to exclude.

**Display**

- **Style** — one of the plugin's built-in styles (same list as the [Styles settings](https://webberzone.com/support/knowledgebase/top-10-settings-styles-options/)).
- **Thumbnail placement** — inline before the title, inline after the title, thumbnails only, or no thumbnails.
- **Thumbnail size** — a registered WordPress image size name. Hidden when **Thumbnail placement** is set to no thumbnails.
- **Show excerpt** and **Excerpt length (words)**.
- **Show author**.
- **Show date**.
- **Show view count**.
- **Show rating** — do not display, star rating, or average score. Requires the free [WP-PostRatings](https://wordpress.org/plugins/wp-postratings/) plugin to be active.

**Output** (WPBakery calls this group **Advanced**)

- **Other attributes** — a free-text field accepting extra [[tptn_list]] shortcode attributes for options not exposed as a dedicated control, e.g. `lazy_load=0&link_nofollow=1` or `link_nofollow="1"`.

## How controls fall back to your saved settings

A control left blank (or, for a checkbox/switcher, left unchecked) falls back to your saved Top 10 settings — the same way an omitted [[tptn_list]] shortcode attribute does. Only an explicit value typed into a control overrides the saved setting for that element instance.

## Troubleshooting

- **The element doesn't appear in the panel.** Confirm the relevant builder plugin/theme is active, that Top 10 Pro (not the free version) is active, and that **Page builder integrations** is enabled on the Features tab of Top 10 settings.
- **A setting doesn't seem to apply.** Check whether the control was left blank/unchecked — it will use your saved plugin settings instead. Set an explicit value on the element to override it.
- **No popular posts are shown.** The same query and settings apply regardless of how the element is inserted — check your [Counter and tracker options](https://webberzone.com/support/knowledgebase/top-10-settings-counter-tracker-options/) and [Posts list options](https://webberzone.com/support/knowledgebase/top-10-settings-list-options/) first.

## See also

- [Top 10 shortcodes](../02-top-10-advanced/top-10-shortcodes/)
- [Blocks in Top 10](../02-top-10-advanced/popular-posts-blocks/)
- [Top 10 Settings – Styles options](../01-top-10-getting-started/top-10-settings-styles-options/)
