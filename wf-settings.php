<?php
/*
Plugin Name: Wirefront Settings Framework
Description: A lightweight developer-first WordPress settings framework that lets you define plugin options using a structured array. Includes all essential input types—textbox, checkbox, radio, select, file upload, sliders, and more. Designed for fast prototyping and reuse across projects. Easily retrieve saved values. Perfect for teams building modular, consistent, and scalable plugins.
Version: 0.1.0
Author: Jonathan Cabato
Author URI: https://wirefront.net
License: MIT
License URI: https://opensource.org/licenses/MIT
Text Domain: wirefront-settings-framework
Plugin URI: https://github.com/WireFront/wf-settings
*/


if (!defined('ABSPATH')) exit;

// Enqueue admin CSS/JS for settings page
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'settings_page_wf-settings') {
        wp_enqueue_style('wf-settings-admin', plugin_dir_url(__FILE__) . 'assets/css/wf-settings-admin.css', [], '1.0.0');
        wp_enqueue_script('wf-settings-admin', plugin_dir_url(__FILE__) . 'assets/js/wf-settings-admin.js', [], '1.0.0', true);
    }
});

// Main class
class WF_Settings_Framework {
    private static $instance = null;
    private $fields = [];
    private $option_name = 'wf_settings';

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set_fields($fields) {
        $this->fields = $fields;
    }

    public function get_fields() {
        return $this->fields;
    }

    public function get_value($id) {
        $options = get_option($this->option_name, []);
        return isset($options[$id]) ? $options[$id] : null;
    }

    public function render_form() {
        $fields = $this->fields;
        $options = get_option($this->option_name, []);
        echo '<form method="post" enctype="multipart/form-data" class="wf-settings-form">';
        wp_nonce_field('wf_settings_save', 'wf_settings_nonce');
        foreach ($fields as $field) {
            $this->render_field($field, $options);
        }
        echo '<p><input type="submit" class="button button-primary" value="Save Settings"></p>';
        echo '</form>';
    }

    private function render_field($field, $options) {
        $id = esc_attr($field['id']);
        $label = isset($field['label']) ? esc_html($field['label']) : '';
        $type = isset($field['type']) ? $field['type'] : 'textbox';
        $value = isset($options[$id]) ? $options[$id] : (isset($field['value']) ? $field['value'] : '');
        $required = !empty($field['required']) ? 'required' : '';
        $placeholder = isset($field['placeholder']) ? 'placeholder="' . esc_attr($field['placeholder']) . '"' : '';
        $min = isset($field['min']) ? 'min="' . esc_attr($field['min']) . '"' : '';
        $max = isset($field['max']) ? 'max="' . esc_attr($field['max']) . '"' : '';
        $step = isset($field['step']) ? 'step="' . esc_attr($field['step']) . '"' : '';
        $accept = isset($field['accept']) ? 'accept="' . esc_attr(implode(',', (array)$field['accept'])) . '"' : '';
        echo '<div class="wf-field wf-field-' . esc_attr($type) . '">';
        if ($type !== 'hidden') {
            echo '<label for="' . $id . '">' . $label . '</label> ';
        }
        switch ($type) {
            case 'textbox':
                echo '<input type="text" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="wf-input-text" ' . $placeholder . ' ' . $required . ' />';
                break;
            case 'checkbox':
                $checked = $value ? 'checked' : '';
                echo '<label class="wf-switch"><input type="checkbox" id="' . $id . '" name="wf_settings[' . $id . ']" value="1" ' . $checked . ' ' . $required . ' /><span class="wf-slider"></span></label>';
                break;
            case 'radio':
                if (!empty($field['options'])) {
                    echo '<div class="wf-radio">';
                    foreach ($field['options'] as $opt) {
                        $opt_val = esc_attr($opt['value']);
                        $opt_label = esc_html($opt['label']);
                        $checked = ($value == $opt['value']) ? 'checked' : '';
                        echo '<label><input type="radio" name="wf_settings[' . $id . ']" value="' . $opt_val . '" ' . $checked . ' ' . $required . '> ' . $opt_label . '</label>';
                    }
                    echo '</div>';
                }
                break;
            case 'select':
                echo '<div style="position:relative;">';
                echo '<select id="' . $id . '" name="wf_settings[' . $id . ']" class="wf-select" ' . $required . '>';
                if (!empty($field['options'])) {
                    foreach ($field['options'] as $opt) {
                        $opt_val = esc_attr($opt['value']);
                        $opt_label = esc_html($opt['label']);
                        $selected = ($value == $opt['value']) ? 'selected' : '';
                        echo '<option value="' . $opt_val . '" ' . $selected . '>' . $opt_label . '</option>';
                    }
                }
                echo '</select>';
                echo '<span class="wf-select-arrow"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 8L10 12L14 8" stroke="#6b7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
                echo '</div>';
                break;
            case 'textarea':
                echo '<textarea id="' . $id . '" name="wf_settings[' . $id . ']" class="wf-textarea" ' . $placeholder . ' ' . $required . '>' . esc_textarea($value) . '</textarea>';
                break;
            case 'date':
                echo '<input type="date" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="wf-input-date" ' . $required . ' />';
                break;
            case 'number':
                echo '<input type="number" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="wf-input-number" ' . $min . ' ' . $max . ' ' . $step . ' ' . $required . ' />';
                break;
            case 'file':
                echo '<input type="file" id="' . $id . '" name="wf_settings_' . $id . '" class="wf-input-file" ' . $accept . ' ' . $required . ' />';
                break;
            case 'color':
                echo '<input type="color" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="wf-input-color" ' . $required . ' />';
                break;
            case 'hidden':
                echo '<input type="hidden" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" />';
                break;
            case 'slider':
            case 'range':
                $input_type = $type === 'slider' ? 'range' : 'range';
                echo '<input type="' . $input_type . '" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="wf-input-range" ' . $min . ' ' . $max . ' ' . $step . ' ' . $required . ' />';
                break;
            default:
                echo '<input type="text" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="wf-input-text" ' . $placeholder . ' ' . $required . ' />';
        }
        echo '</div>';
    }

    public function handle_save() {
        if (isset($_POST['wf_settings_nonce']) && wp_verify_nonce($_POST['wf_settings_nonce'], 'wf_settings_save')) {
            $fields = $this->fields;
            $new_values = [];
            foreach ($fields as $field) {
                $id = $field['id'];
                if ($field['type'] === 'file') {
                    // File upload handling can be added here
                    continue;
                }
                $val = isset($_POST['wf_settings'][$id]) ? $_POST['wf_settings'][$id] : null;
                // Basic validation (expand as needed)
                if (isset($field['validation'])) {
                    $val = $this->validate($val, $field['validation']);
                }
                $new_values[$id] = $val;
            }
            update_option($this->option_name, $new_values);
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
            });
        }
    }

    private function validate($val, $rules) {
        // Simple validation logic (expand as needed)
        if ($rules['type'] === 'string') {
            $val = sanitize_text_field($val);
            $len = strlen($val);
            if (isset($rules['minLength']) && $len < $rules['minLength']) return '';
            if (isset($rules['maxLength']) && $len > $rules['maxLength']) $val = substr($val, 0, $rules['maxLength']);
        }
        return $val;
    }
}

// Helper function
function wf_settings_val($id) {
    return WF_Settings_Framework::instance()->get_value($id);
}

// Example usage (move to your plugin's admin page)
add_action('admin_menu', function() {
    add_options_page('WF Settings', 'WF Settings', 'manage_options', 'wf-settings', function() {
        $fields = include __DIR__ . '/wf-settings-fields.php';
        $wf = WF_Settings_Framework::instance();
        $wf->set_fields($fields);
        $wf->handle_save();
        $wf->render_form();
    });
});
