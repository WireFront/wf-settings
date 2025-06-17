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
    private $page_config = [];
    private $option_name = 'wf_settings';
    private $notifications = [];

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set_fields($fields) {
        // Extract page configuration if present
        if (isset($fields['page_title']) || isset($fields['page_description'])) {
            $this->page_config = [
                'title' => isset($fields['page_title']) ? $fields['page_title'] : 'Settings',
                'description' => isset($fields['page_description']) ? $fields['page_description'] : ''
            ];
            // Remove page config from fields array
            unset($fields['page_title']);
            unset($fields['page_description']);
        }
        $this->fields = $fields;
    }

    public function get_fields() {
        return $this->fields;
    }

    public function get_value($id) {
        $options = get_option($this->option_name, []);
        $value = isset($options[$id]) ? $options[$id] : null;
        
        // Check if this is a range field and parse the value into an array
        foreach ($this->fields as $field) {
            if ($field['id'] === $id && $field['type'] === 'range') {
                if ($value && strpos($value, '-') !== false) {
                    $parts = explode('-', $value);
                    return [
                        'min' => (int) $parts[0],
                        'max' => (int) $parts[1]
                    ];
                } else {
                    // Return default values if no value is saved yet
                    return [
                        'min' => isset($field['min']) ? (int) $field['min'] : 0,
                        'max' => isset($field['max']) ? (int) $field['max'] : 100
                    ];
                }
            }
        }
        
        return $value;
    }

    private function add_notification($message, $type = 'success') {
        $this->notifications[] = [
            'message' => $message,
            'type' => $type
        ];
    }

    private function render_notifications() {
        if (!empty($this->notifications)) {
            echo '<div class="wf-notifications">';
            foreach ($this->notifications as $notification) {
                $type_class = $notification['type'] === 'error' ? 'wf-notification-error' : 'wf-notification-success';
                $icon = $notification['type'] === 'error' ? '⚠️' : '✅';
                echo '<div class="wf-notification ' . $type_class . '">';
                echo '<span class="wf-notification-icon">' . $icon . '</span>';
                echo '<span class="wf-notification-message">' . esc_html($notification['message']) . '</span>';
                echo '<button class="wf-notification-close" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                echo '</div>';
            }
            echo '</div>';
        }
    }

    public function render_page_header() {
        if (!empty($this->page_config)) {
            echo '<div class="wf-page-header">';
            if (!empty($this->page_config['title'])) {
                echo '<h1 class="wf-page-title">' . esc_html($this->page_config['title']) . '</h1>';
            }
            if (!empty($this->page_config['description'])) {
                echo '<p class="wf-page-description">' . esc_html($this->page_config['description']) . '</p>';
            }
            echo '</div>';
        }
    }

    public function render_form() {
        $this->render_page_header();
        $this->render_notifications();
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
            
            // Render field description right after the label
            if (isset($field['description']) && !empty($field['description'])) {
                echo '<p class="wf-field-description">' . esc_html($field['description']) . '</p>';
            }
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
                echo '<div class="selectbox-wrapper">';
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
                echo '<div class="wf-range-container">';
                echo '<input type="range" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="wf-input-range" ' . $min . ' ' . $max . ' ' . $step . ' ' . $required . ' />';
                echo '<span class="wf-range-value" data-target="' . $id . '">' . esc_attr($value) . '</span>';
                echo '</div>';
                break;
            case 'range':
                // Parse existing value or set defaults
                $range_value = $value ? $value : '';
                $min_val = $max_val = '';
                if ($range_value && strpos($range_value, '-') !== false) {
                    list($min_val, $max_val) = explode('-', $range_value);
                } else {
                    $min_val = isset($field['min']) ? $field['min'] : '0';
                    $max_val = isset($field['max']) ? $field['max'] : '100';
                }
                
                echo '<div class="wf-dual-range-container">';
                echo '<input type="hidden" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($range_value) . '" />';
                echo '<span class="wf-range-value wf-range-min">' . esc_attr($min_val) . '</span>';
                echo '<div class="wf-dual-range-slider" data-min="' . (isset($field['min']) ? esc_attr($field['min']) : '0') . '" data-max="' . (isset($field['max']) ? esc_attr($field['max']) : '100') . '" data-step="' . (isset($field['step']) ? esc_attr($field['step']) : '1') . '" data-target="' . $id . '">';
                echo '<div class="wf-dual-range-track"></div>';
                echo '<div class="wf-dual-range-fill"></div>';
                echo '<div class="wf-dual-range-thumb wf-dual-range-thumb-min" data-value="' . esc_attr($min_val) . '"></div>';
                echo '<div class="wf-dual-range-thumb wf-dual-range-thumb-max" data-value="' . esc_attr($max_val) . '"></div>';
                echo '</div>';
                echo '<span class="wf-range-value wf-range-max">' . esc_attr($max_val) . '</span>';
                echo '</div>';
                break;
            default:
                echo '<input type="text" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="wf-input-text" ' . $placeholder . ' ' . $required . ' />';
        }
        
        echo '</div>';
    }

    public function handle_save() {
        if (isset($_POST['wf_settings_nonce']) && wp_verify_nonce($_POST['wf_settings_nonce'], 'wf_settings_save')) {
            try {
                $fields = $this->fields;
                $new_values = [];
                $validation_errors = [];
                
                foreach ($fields as $field) {
                    $id = $field['id'];
                    if ($field['type'] === 'file') {
                        // File upload handling can be added here
                        continue;
                    }
                    
                    $val = isset($_POST['wf_settings'][$id]) ? $_POST['wf_settings'][$id] : null;
                    
                    // Check required fields
                    if (!empty($field['required']) && (empty($val) && $val !== '0')) {
                        $label = isset($field['label']) ? $field['label'] : $id;
                        $validation_errors[] = "Field '{$label}' is required.";
                        continue;
                    }
                    
                    // Basic validation (expand as needed)
                    if (isset($field['validation'])) {
                        $validated_val = $this->validate($val, $field['validation']);
                        if ($validated_val === false) {
                            $label = isset($field['label']) ? $field['label'] : $id;
                            $validation_errors[] = "Field '{$label}' contains invalid data.";
                            continue;
                        }
                        $val = $validated_val;
                    }
                    
                    $new_values[$id] = $val;
                }
                
                if (!empty($validation_errors)) {
                    foreach ($validation_errors as $error) {
                        $this->add_notification($error, 'error');
                    }
                    return;
                }
                
                $result = update_option($this->option_name, $new_values);
                if ($result !== false) {
                    $this->add_notification('Settings saved successfully!', 'success');
                } else {
                    $this->add_notification('Settings may not have changed or failed to save.', 'error');
                }
                
            } catch (Exception $e) {
                $this->add_notification('An error occurred while saving settings: ' . $e->getMessage(), 'error');
            }
        } else if (isset($_POST['wf_settings_nonce'])) {
            $this->add_notification('Security verification failed. Please try again.', 'error');
        }
    }

    private function validate($val, $rules) {
        // Simple validation logic (expand as needed)
        if ($rules['type'] === 'string') {
            $val = sanitize_text_field($val);
            $len = strlen($val);
            if (isset($rules['minLength']) && $len < $rules['minLength']) {
                return false; // Validation failed
            }
            if (isset($rules['maxLength']) && $len > $rules['maxLength']) {
                $val = substr($val, 0, $rules['maxLength']);
            }
        }
        return $val;
    }
}

// Helper function
function wf_settings_val($id) {
    $instance = WF_Settings_Framework::instance();
    
    // Load fields if they haven't been loaded yet
    if (empty($instance->get_fields())) {
        $fields = include __DIR__ . '/wf-settings-fields.php';
        $instance->set_fields($fields);
    }
    
    return $instance->get_value($id);
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
