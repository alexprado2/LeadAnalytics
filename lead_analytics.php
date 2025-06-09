<?php
/**
 * Module Name: Lead Analytics
 * Description: Análise avançada de leads com busca configurável e gráficos interativos.
 * Version: 1.0.0
 * Author: Alex Prado
 */

defined('BASEPATH') or exit('No direct script access allowed');

define('LEAD_ANALYTICS_MODULE_NAME', 'lead_analytics');

/**
 * Register language files
 */
register_language_files(LEAD_ANALYTICS_MODULE_NAME, [LEAD_ANALYTICS_MODULE_NAME]);

/**
 * Init module menu items
 */
function lead_analytics_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('leads', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('lead_analytics', [
            'name'     => _l('lead_analytics'),
            'href'     => admin_url('lead_analytics'),
            'icon'     => 'fa fa-bar-chart-o',
            'position' => 35,
        ]);
    }
}
hooks()->add_action('admin_init', 'lead_analytics_init_menu_items');

/**
 * Register activation and deactivation hooks
 */
register_activation_hook(LEAD_ANALYTICS_MODULE_NAME, 'lead_analytics_module_activation_hook');
function lead_analytics_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

register_deactivation_hook(LEAD_ANALYTICS_MODULE_NAME, 'lead_analytics_module_deactivation_hook');
function lead_analytics_module_deactivation_hook()
{
    // Logic for deactivation if needed
}

/**
 * Register module permissions
 */
function lead_analytics_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
        'export' => _l('permission_export'),
    ];
    register_staff_capabilities('leads', $capabilities, _l('lead_analytics'));
}
hooks()->add_action('staff_permissions', 'lead_analytics_permissions');


/**
 * Add CSS and JS assets
 */
function lead_analytics_add_head_components()
{
    $CI = &get_instance();
    $module_name = LEAD_ANALYTICS_MODULE_NAME;
    $page = $CI->uri->segment(2);

    if ($page === 'lead_analytics') {
        echo '<link rel="stylesheet" type="text/css" href="' . module_dir_url($module_name, 'assets/css/lead_analytics.css') . '?v=' . time() . '">';
        echo '<script src="' . module_dir_url($module_name, 'assets/libs/chart.min.js') . '"></script>';
    }
}
hooks()->add_action('app_admin_head', 'lead_analytics_add_head_components');


function lead_analytics_add_footer_components(){
    $CI = & get_instance();
    $module_name = LEAD_ANALYTICS_MODULE_NAME;
    $page = $CI->uri->segment(2);

    if ($page === 'lead_analytics') {
         echo '<script src="' . module_dir_url($module_name, 'assets/js/lead_analytics.js') . '?v=' . time() . '"></script>';
    }
}
hooks()->add_action('app_admin_footer', 'lead_analytics_add_footer_components');

/**
 * Load helper
 */
$CI = &get_instance();
$CI->load->helper(LEAD_ANALYTICS_MODULE_NAME . '/lead_analytics');