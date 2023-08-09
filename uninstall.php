<?php

if(!defined('WP_UNINSTALL_PLUGIN')){
    die();  
}

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$tabla = $wpdb->prefix . 'bluecell';

// Consulta para eliminar la tabla
$wpdb->query("DROP TABLE IF EXISTS $tabla");