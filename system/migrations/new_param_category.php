<?php
require_once ("{$_SERVER['DOCUMENT_ROOT']}/config.php");
$collumn = 'shopify_category_id';
$mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$test = $mysqli->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'oc_category' AND COLUMN_NAME = 'shopify_category_id'");
if (array_values($test->fetch_assoc())[0] == 0) {
    $test = $mysqli->query("ALTER TABLE `".DB_PREFIX."category` ADD `shopify_category_id` BIGINT NOT NULL AFTER `sort_order`;");
    var_dump($test);
}else{
    echo 'COLLUMN EXIST shopify_category_id';
}