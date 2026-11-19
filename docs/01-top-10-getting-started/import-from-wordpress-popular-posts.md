---
slug: import-from-wordpress-popular-posts
title: "Import view counts from WordPress Popular Posts"
products: [top-10]
sections: ["01-top-10-getting-started"]
tags: [import, top-10, wordpress-popular-posts]
status: publish
order: 0
---

[Top 10](https://webberzone.com/plugins/top-10/) can copy the view counts that the [WordPress Popular Posts](https://wordpress.org/plugins/wordpress-popular-posts/) plugin (WPP) has already collected, so a switch does not reset your popular posts lists.

Back up your database before you run the import. The import cannot be undone.

## Before you start

The importer reads the WPP data tables (`popularpostsdata` and `popularpostssummary`) from your database. It appears on the **Import/Export** screen under Top 10 once WPP is active and both tables exist. If the plugin is not active and the tables are missing, or the tables are missing on their own, the screen shows an error instead of the import form.

Keep WPP active until the import finishes. You can deactivate it afterward.

## Run the import

1. Go to **Top 10 → Import/Export**.
2. Scroll to **Migrate WordPress Popular Posts Data to Top 10**.
3. Choose the options below.
4. Leave **Enable Dry Run** ticked and select **Run Import** to preview the result.
5. Untick **Enable Dry Run** and select **Run Import** again to write the data. Confirm the prompt.

## Options

### Import Mode

**Replace data** (the default) removes the existing Top 10 counts for each post being imported and then inserts the WPP counts. Posts that are not in the WPP data keep their Top 10 counts.

**Merge data** adds the WPP counts to the Top 10 counts that already exist.

### Data to Import

- **Total counts only** (the default) imports the all-time view count for each post.
- **Daily counts only** imports the data behind the daily and custom period lists.
- **Both total and daily counts** imports both.

WPP records every individual view, while Top 10 stores cumulative views and daily views grouped by hour. For daily counts, the importer groups the WPP views by hour to match the Top 10 format.

### Minimum View Count

Only posts with at least this many views are imported. The default is `1`. For daily counts, the threshold applies to each hour. Raise it to skip posts with very few views, or to shorten the import on a large site.

### Enable Dry Run

Enabled by default. A dry run counts the records and views that would be imported and reports them without changing the database. Run it first to check your settings.

## Multisite

On a multisite network, the import form on a single site imports into that site. In the Network Admin, the form adds a **Select Sites** list and you choose which sites to import into. Importing into any site other than the current one requires the `manage_network_options` capability, so only network administrators can import data across sites.

## After the import

Top 10 writes the imported counts straight to its count tables. If your lists are served from a cache, [clear the Top 10 cache](https://webberzone.com/support/knowledgebase/top-10-tools-admin-screen/) so the new counts appear.

## Large sites

A large WPP table can cause the request to time out. Run the import again with a higher **Minimum View Count** to reduce the number of posts, or import one site at a time on a network.
