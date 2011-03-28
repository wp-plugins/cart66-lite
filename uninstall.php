<?php
global $wpdb;

if( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
  exit();

global $wpdb;
$prefix = $wpdb->prefix . "cart66_";
$sqlFile = dirname( __FILE__ ) . "/sql/uninstall.sql";
$sql = str_replace('[prefix]', $prefix, file_get_contents($sqlFile));
$queries = explode(";\n", $sql);
foreach($queries as $sql) {
  if(strlen($sql) > 5) {
    $wpdb->query($sql);
  }
}