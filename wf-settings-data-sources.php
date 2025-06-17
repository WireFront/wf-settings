<?php
// wf-settings-data-sources.php
// Dynamic data sources for populating form fields in the Wirefront Settings Framework
// This file provides functions to fetch WordPress data like pages, posts, users, etc.

if (!defined('ABSPATH')) exit;

/**
 * Get all WordPress pages
 * @return array Array of pages with page_id and label
 */
function wf_get_pages_data_source() {
    $pages = get_pages([
        'sort_column' => 'menu_order',
        'sort_order' => 'ASC',
        'post_status' => 'publish'
    ]);
    
    $data = [];
    foreach ($pages as $page) {
        $data[] = [
            'value' => $page->ID,
            'label' => $page->post_title
        ];
    }
    
    return $data;
}

/**
 * Get all WordPress posts
 * @param string $post_type Post type to fetch (default: 'post')
 * @param int $limit Number of posts to fetch (default: -1 for all)
 * @return array Array of posts with post_id and label
 */
function wf_get_posts_data_source($post_type = 'post', $limit = -1) {
    $posts = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'numberposts' => $limit,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    $data = [];
    foreach ($posts as $post) {
        $data[] = [
            'value' => $post->ID,
            'label' => $post->post_title
        ];
    }
    
    return $data;
}

/**
 * Get all WordPress users
 * @param array $roles Array of user roles to include (default: all roles)
 * @return array Array of users with user_id and label
 */
function wf_get_users_data_source($roles = []) {
    $args = [
        'orderby' => 'display_name',
        'order' => 'ASC'
    ];
    
    if (!empty($roles)) {
        $args['role__in'] = $roles;
    }
    
    $users = get_users($args);
    
    $data = [];
    foreach ($users as $user) {
        $data[] = [
            'value' => $user->ID,
            'label' => $user->display_name . ' (' . $user->user_email . ')'
        ];
    }
    
    return $data;
}

/**
 * Get all WordPress categories
 * @param string $taxonomy Taxonomy name (default: 'category')
 * @return array Array of categories with term_id and label
 */
function wf_get_categories_data_source($taxonomy = 'category') {
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
    
    $data = [];
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $data[] = [
                'value' => $term->term_id,
                'label' => $term->name
            ];
        }
    }
    
    return $data;
}

/**
 * Get all WordPress tags
 * @return array Array of tags with term_id and label
 */
function wf_get_tags_data_source() {
    return wf_get_categories_data_source('post_tag');
}

/**
 * Get all WordPress menus
 * @return array Array of menus with menu_id and label
 */
function wf_get_menus_data_source() {
    $menus = wp_get_nav_menus();
    
    $data = [];
    foreach ($menus as $menu) {
        $data[] = [
            'value' => $menu->term_id,
            'label' => $menu->name
        ];
    }
    
    return $data;
}

/**
 * Get all registered post types
 * @param bool $public_only Whether to include only public post types (default: true)
 * @return array Array of post types with name and label
 */
function wf_get_post_types_data_source($public_only = true) {
    $args = [];
    if ($public_only) {
        $args['public'] = true;
    }
    
    $post_types = get_post_types($args, 'objects');
    
    $data = [];
    foreach ($post_types as $post_type) {
        $data[] = [
            'value' => $post_type->name,
            'label' => $post_type->labels->name
        ];
    }
    
    return $data;
}

/**
 * Get WordPress user roles
 * @return array Array of user roles with role name and label
 */
function wf_get_user_roles_data_source() {
    global $wp_roles;
    
    $data = [];
    foreach ($wp_roles->roles as $role_key => $role_data) {
        $data[] = [
            'value' => $role_key,
            'label' => $role_data['name']
        ];
    }
    
    return $data;
}

/**
 * Get WordPress sidebars/widget areas
 * @return array Array of sidebars with sidebar_id and label
 */
function wf_get_sidebars_data_source() {
    global $wp_registered_sidebars;
    
    $data = [];
    foreach ($wp_registered_sidebars as $sidebar) {
        $data[] = [
            'value' => $sidebar['id'],
            'label' => $sidebar['name']
        ];
    }
    
    return $data;
}

/**
 * Get installed WordPress themes
 * @return array Array of themes with theme directory name and label
 */
function wf_get_themes_data_source() {
    $themes = wp_get_themes();
    
    $data = [];
    foreach ($themes as $theme_key => $theme) {
        $data[] = [
            'value' => $theme_key,
            'label' => $theme->get('Name')
        ];
    }
    
    return $data;
}

/**
 * Get active WordPress plugins
 * @return array Array of plugins with plugin file and label
 */
function wf_get_plugins_data_source() {
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins', []);
    
    $data = [];
    foreach ($active_plugins as $plugin_file) {
        if (isset($all_plugins[$plugin_file])) {
            $data[] = [
                'value' => $plugin_file,
                'label' => $all_plugins[$plugin_file]['Name']
            ];
        }
    }
    
    return $data;
}

/**
 * Get custom data source with optional caching
 * @param callable $callback Function that returns the data array
 * @param string $cache_key Unique cache key (optional)
 * @param int $cache_duration Cache duration in seconds (default: 1 hour)
 * @return array Array of data from the callback function
 */
function wf_get_custom_data_source($callback, $cache_key = '', $cache_duration = 3600) {
    if (!empty($cache_key)) {
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            return $cached_data;
        }
    }
    
    $data = call_user_func($callback);
    
    if (!empty($cache_key) && is_array($data)) {
        set_transient($cache_key, $data, $cache_duration);
    }
    
    return $data;
}

/**
 * Helper function to get data source by name
 * @param string $source_name Name of the data source function
 * @param array $args Arguments to pass to the data source function
 * @return array Array of data from the specified source
 */
function wf_get_data_source($source_name, $args = []) {
    $function_name = 'wf_get_' . $source_name . '_data_source';
    
    if (function_exists($function_name)) {
        return call_user_func_array($function_name, $args);
    }
    
    return [];
}

/**
 * Example custom data source functions
 */

/**
 * Get country list (example static data source)
 * @return array Array of countries with country code and label
 */
function wf_get_countries_data_source() {
    return [
        ['value' => 'US', 'label' => 'United States'],
        ['value' => 'CA', 'label' => 'Canada'],
        ['value' => 'GB', 'label' => 'United Kingdom'],
        ['value' => 'AU', 'label' => 'Australia'],
        ['value' => 'DE', 'label' => 'Germany'],
        ['value' => 'FR', 'label' => 'France'],
        ['value' => 'JP', 'label' => 'Japan'],
        ['value' => 'BR', 'label' => 'Brazil'],
        ['value' => 'IN', 'label' => 'India'],
        ['value' => 'CN', 'label' => 'China']
    ];
}

/**
 * Get timezone list
 * @return array Array of timezones with timezone identifier and label
 */
function wf_get_timezones_data_source() {
    $timezones = timezone_identifiers_list();
    
    $data = [];
    foreach ($timezones as $timezone) {
        $data[] = [
            'value' => $timezone,
            'label' => str_replace('_', ' ', $timezone)
        ];
    }
    
    return $data;
}
