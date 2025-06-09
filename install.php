<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Logic for database table creation and other setup on module activation
if (!$CI->db->table_exists(db_prefix() . 'lead_analytics_chart_configs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'lead_analytics_chart_configs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `staff_id` int(11) NOT NULL,
        `chart_id` varchar(191) NOT NULL,
        `config` TEXT NOT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}
