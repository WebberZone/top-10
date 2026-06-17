---
slug: disable-top-10-dashboard-widgets
title: "Disable Top 10 Dashboard Widgets"
products: [top-10]
sections: [02-top-10-advanced]
tags: [actions-and-filters,top-10]
status: publish
order: 0
---

Top 10 adds two Dashboard Widgets that allows logged in users to see the popular posts. These widgets reflect the same counts as **Top 10 \> Popular Posts**.

If you don't want to display this, then you can add the following code into your theme's functions.php or a file in `mu-plugins` folder.

```php
add_filter( 'tptn_dashboard_setup', '__return_false' );
```
