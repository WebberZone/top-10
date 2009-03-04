<?php
if ( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}
	global $wpdb;
   	$table_name = $wpdb->prefix . "top_ten";
   	$table_name_daily = $wpdb->prefix . "table_name_daily";

	$sql = "DROP TABLE $table_name";
	$wpdb->query($sql);
	$sql = "DROP TABLE $table_name_daily";
	$wpdb->query($sql);
	delete_option('ald_tptn_settings');
	delete_option('tptn_db_version');
?>