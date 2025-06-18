<?php
/*
Plugin Name: Wirefront Settings Framework
Description: A lightweight developer-first WordPress settings framework that lets you define plugin options using a structured array. Includes all essential input types—textbox, checkbox, radio, select, file upload with WordPress media library integration, sliders, and more. Designed for fast prototyping and reuse across projects. Easily retrieve saved values. Perfect for teams building modular, consistent, and scalable plugins.
Version: 0.2.1
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
        // Enqueue WordPress media library
        wp_enqueue_media();
        
        wp_enqueue_style('wf-settings-admin', plugin_dir_url(__FILE__) . 'assets/css/wf-settings-admin.css', [], '1.0.0');
        wp_enqueue_script('wf-settings-admin', plugin_dir_url(__FILE__) . 'assets/js/wf-settings-admin.js', ['jquery', 'media-upload', 'media-views'], '1.0.0', true);
        
        // Localize script for AJAX
        wp_localize_script('wf-settings-admin', 'wf_settings_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wf_settings_media_nonce'),
            'security_nonce' => wp_create_nonce('wf_settings_security')
        ]);
    }
});

// Add security headers for the settings page
add_action('admin_head', function() {
    global $pagenow;
    if ($pagenow === 'options-general.php' && isset($_GET['page']) && $_GET['page'] === 'wf-settings') {
        // Add Content Security Policy header
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; media-src 'self';");
        // Add other security headers
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
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
                $safe_icon = $notification['type'] === 'error' ? '⚠️' : '✅';
                echo '<div class="wf-notification ' . esc_attr($type_class) . '">';
                echo '<span class="wf-notification-icon">' . esc_html($safe_icon) . '</span>';
                echo '<span class="wf-notification-message">' . esc_html($notification['message']) . '</span>';
                echo '<button class="wf-notification-close" data-action="close" type="button">&times;</button>';
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
        
        // Prioritize submitted values over saved options for better UX during validation errors
        $submitted_value = isset($_POST['wf_settings'][$id]) ? $_POST['wf_settings'][$id] : null;
        $raw_value = $submitted_value !== null ? $submitted_value : (isset($options[$id]) ? $options[$id] : (isset($field['value']) ? $field['value'] : ''));
        
        // Properly handle quotes by unslashing stored values
        $value = wp_unslash($raw_value);
        
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
                // Determine input type based on validation
                $input_type = 'text';
                $css_class = 'wf-input-text';
                
                if (isset($field['validation'])) {
                    if ($field['validation']['type'] === 'email') {
                        $input_type = 'email';
                        $css_class = 'wf-input-email';
                    } elseif ($field['validation']['type'] === 'url') {
                        $input_type = 'url';
                        $css_class = 'wf-input-url';
                    }
                }
                
                echo '<input type="' . $input_type . '" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="' . $css_class . '" ' . $placeholder . ' ' . $required . ' />';
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
                echo '<div class="wf-file-upload-container">';
                echo '<input type="hidden" id="' . $id . '" name="wf_settings[' . $id . ']" value="' . esc_attr($value) . '" class="wf-file-attachment-id" />';
                echo '<div class="wf-file-preview" data-field-id="' . $id . '">';
                
                // Display current file if exists
                if ($value && $this->validate_attachment_id($value)) {
                    $attachment = wp_get_attachment_url($value);
                    $attachment_title = get_the_title($value);
                    if ($attachment) {
                        $file_type = wp_check_filetype($attachment);
                        if (strpos($file_type['type'], 'image/') === 0) {
                            // Use thumbnail if available, otherwise use full size
                            $image_url = wp_get_attachment_image_url($value, 'thumbnail') ?: $attachment;
                            echo '<div class="wf-file-preview-item">';
                            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($attachment_title) . '" class="wf-file-preview-image" />';
                            echo '<div class="wf-file-info">';
                            echo '<span class="wf-file-name">' . esc_html($attachment_title) . '</span>';
                            echo '<span class="wf-file-id">ID: ' . esc_html($value) . '</span>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            echo '<div class="wf-file-preview-item">';
                            echo '<div class="wf-file-icon">📄</div>';
                            echo '<div class="wf-file-info">';
                            echo '<span class="wf-file-name">' . esc_html($attachment_title) . '</span>';
                            echo '<span class="wf-file-id">ID: ' . esc_html($value) . '</span>';
                            echo '<span class="wf-file-type">' . esc_html($file_type['ext']) . '</span>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<div class="wf-file-placeholder">No file selected</div>';
                }
                
                echo '</div>';
                echo '<div class="wf-file-upload-buttons">';
                echo '<button type="button" class="button wf-media-button wf-select-media-button" data-field-id="' . $id . '" ' . $accept . '>Select File</button>';
                if ($value && $this->validate_attachment_id($value)) {
                    echo '<button type="button" class="button wf-media-button wf-remove-media-button" data-field-id="' . $id . '">Remove</button>';
                }
                echo '</div>';
                echo '</div>';
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
            // Rate limiting check
            $user_id = get_current_user_id();
            $rate_limit_key = "wf_settings_last_save_{$user_id}";
            $last_save = get_transient($rate_limit_key);
            
            if ($last_save && (time() - $last_save) < 5) {
                $this->add_notification('Please wait a few seconds before saving again.', 'error');
                return;
            }
            
            try {
                $fields = $this->fields;
                $new_values = [];
                $validation_errors = [];
                
                foreach ($fields as $field) {
                    $id = $field['id'];
                    
                    $val = isset($_POST['wf_settings'][$id]) ? $_POST['wf_settings'][$id] : null;
                    
                    // Unslash the incoming data to prevent quote escaping issues
                    if ($val !== null) {
                        $val = wp_unslash($val);
                    }
                    
                    // Special handling for file fields
                    if ($field['type'] === 'file') {
                        if ($val && !$this->validate_file_upload($val, isset($field['accept']) ? $field['accept'] : [])) {
                            $label = isset($field['label']) ? $field['label'] : $id;
                            $validation_errors[] = "Invalid file selected for '{$label}'. Please select a valid file.";
                            continue;
                        }
                    }
                    
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
                            
                            // Provide more specific error messages
                            if ($field['validation']['type'] === 'email') {
                                $validation_errors[] = "Field '{$label}' must contain a valid email address.";
                            } elseif ($field['validation']['type'] === 'url') {
                                $validation_errors[] = "Field '{$label}' must contain a valid URL starting with http:// or https://.";
                            } elseif ($field['validation']['type'] === 'textarea') {
                                if (isset($field['validation']['minLength'])) {
                                    $minLen = $field['validation']['minLength'];
                                    $currentLen = strlen($val);
                                    $validation_errors[] = "Field '{$label}' must be at least {$minLen} characters long. Currently {$currentLen} characters.";
                                } else {
                                    $validation_errors[] = "Field '{$label}' contains invalid data.";
                                }
                            } else {
                                $validation_errors[] = "Field '{$label}' contains invalid data.";
                            }
                            continue;
                        }
                        $val = $validated_val;
                    } else {
                        // Apply basic sanitization based on field type
                        $val = $this->sanitize_by_type($val, $field['type']);
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
                    // Set rate limit
                    set_transient($rate_limit_key, time(), 60);
                    // Log successful save
                    $this->log_security_event('settings_saved', 'Settings updated successfully');
                } else {
                    $this->add_notification('Settings may not have changed or failed to save.', 'error');
                }
                
            } catch (Exception $e) {
                $this->add_notification('An error occurred while saving settings: ' . esc_html($e->getMessage()), 'error');
                $this->log_security_event('settings_save_error', 'Exception: ' . $e->getMessage());
            }
        } else if (isset($_POST['wf_settings_nonce'])) {
            $this->add_notification('Security verification failed. Please try again.', 'error');
            $this->log_security_event('nonce_verification_failed', 'Invalid nonce for settings save');
        }
    }

    private function validate($val, $rules) {
        // Enhanced validation with proper sanitization
        if ($rules['type'] === 'string') {
            $val = sanitize_text_field($val);
            $len = strlen($val);
            if (isset($rules['minLength']) && $len < $rules['minLength']) {
                return false; // Validation failed
            }
            if (isset($rules['maxLength']) && $len > $rules['maxLength']) {
                $val = substr($val, 0, $rules['maxLength']);
            }
        } elseif ($rules['type'] === 'email') {
            $val = sanitize_email($val);
            if (!is_email($val)) {
                return false;
            }
        } elseif ($rules['type'] === 'url') {
            $val = esc_url_raw($val);
            if (!filter_var($val, FILTER_VALIDATE_URL)) {
                return false;
            }
        } elseif ($rules['type'] === 'number' || $rules['type'] === 'integer') {
            $val = intval($val);
            if (isset($rules['min']) && $val < $rules['min']) {
                return false;
            }
            if (isset($rules['max']) && $val > $rules['max']) {
                return false;
            }
        } elseif ($rules['type'] === 'float') {
            $val = floatval($val);
            if (isset($rules['min']) && $val < $rules['min']) {
                return false;
            }
            if (isset($rules['max']) && $val > $rules['max']) {
                return false;
            }
        } elseif ($rules['type'] === 'textarea') {
            $val = sanitize_textarea_field($val);
            $len = strlen($val);
            if (isset($rules['minLength']) && $len < $rules['minLength']) {
                return false;
            }
            if (isset($rules['maxLength']) && $len > $rules['maxLength']) {
                $val = substr($val, 0, $rules['maxLength']);
            }
        } else {
            // Default: treat as text
            $val = sanitize_text_field($val);
        }
        return $val;
    }
    
    public function get_attachment_details($attachment_id) {
        if (!$attachment_id) {
            return null;
        }
        
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return null;
        }
        
        $url = wp_get_attachment_url($attachment_id);
        $metadata = wp_get_attachment_metadata($attachment_id);
        $file_type = wp_check_filetype($url);
        
        return [
            'id' => $attachment_id,
            'title' => $attachment->post_title,
            'url' => $url,
            'filename' => basename($url),
            'type' => $file_type['type'],
            'ext' => $file_type['ext'],
            'metadata' => $metadata,
            'alt_text' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content
        ];
    }

    private function validate_attachment_id($attachment_id) {
        if (empty($attachment_id)) {
            return false;
        }
        
        // Check if the attachment exists and is valid
        $attachment = get_post($attachment_id);
        return $attachment && $attachment->post_type === 'attachment';
    }

    private function validate_file_upload($attachment_id, $allowed_types = []) {
        if (empty($attachment_id)) {
            return false;
        }
        
        // Check if the attachment exists and is valid
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return false;
        }
        
        // If allowed types are specified, validate file type
        if (!empty($allowed_types)) {
            $file_path = get_attached_file($attachment_id);
            if (!$file_path) {
                return false;
            }
            
            $file_type = wp_check_filetype($file_path);
            
            // Check if file type is in allowed types
            $type_allowed = false;
            foreach ($allowed_types as $allowed_type) {
                if (strpos($allowed_type, '*') !== false) {
                    // Handle wildcard types like "image/*"
                    $base_type = str_replace('*', '', $allowed_type);
                    if (strpos($file_type['type'], $base_type) === 0) {
                        $type_allowed = true;
                        break;
                    }
                } elseif ($file_type['type'] === $allowed_type) {
                    $type_allowed = true;
                    break;
                }
            }
            
            if (!$type_allowed) {
                return false;
            }
        }
        
        // Additional security checks
        $file_path = get_attached_file($attachment_id);
        if ($file_path && file_exists($file_path)) {
            // Check file size (WordPress already handles this, but we can add custom limits)
            $file_size = filesize($file_path);
            $max_size = wp_max_upload_size();
            
            if ($file_size > $max_size) {
                return false;
            }
        }
        
        return true;
    }

    private function sanitize_by_type($value, $type) {
        switch ($type) {
            case 'textbox':
            case 'hidden':
                // Custom sanitization that preserves special characters
                return $this->sanitize_text_preserve_chars($value);
            case 'textarea':
                // Custom sanitization that preserves special characters
                return $this->sanitize_textarea_preserve_chars($value);
            case 'email':
                // Value is already unslashed in handle_save(), just sanitize
                return sanitize_email($value);
            case 'url':
                return esc_url_raw($value);
            case 'number':
            case 'slider':
                return intval($value);
            case 'checkbox':
                return $value ? 1 : 0;
            case 'radio':
            case 'select':
                return sanitize_text_field($value);
            case 'date':
                // Validate date format
                $date = DateTime::createFromFormat('Y-m-d', $value);
                return ($date && $date->format('Y-m-d') === $value) ? $value : '';
            case 'color':
                // Validate hex color
                return preg_match('/^#[a-f0-9]{6}$/i', $value) ? $value : '';
            case 'file':
                return intval($value); // Attachment ID
            case 'range':
                // Validate range format (e.g., "10-50")
                if (preg_match('/^\d+-\d+$/', $value)) {
                    return sanitize_text_field($value);
                }
                return '';
            default:
                return sanitize_text_field($value);
        }
    }

    private function sanitize_text_preserve_chars($value) {
        // Remove script tags and other dangerous content but preserve special characters
        $value = strip_tags($value);
        
        // Remove null bytes and invalid UTF-8
        $value = str_replace(chr(0), '', $value);
        
        // Validate UTF-8
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        
        // Trim whitespace
        $value = trim($value);
        
        return $value;
    }
    
    private function sanitize_textarea_preserve_chars($value) {
        // Remove script tags and other dangerous content but preserve special characters and line breaks
        $value = strip_tags($value);
        
        // Remove null bytes and invalid UTF-8
        $value = str_replace(chr(0), '', $value);
        
        // Validate UTF-8
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        
        // Trim whitespace but preserve line breaks
        $value = trim($value);
        
        return $value;
    }

    private function log_security_event($event_type, $details = '') {
        // Optional: Log security-relevant events
        if (defined('WF_SETTINGS_AUDIT_LOG') && WF_SETTINGS_AUDIT_LOG) {
            $log_entry = [
                'timestamp' => current_time('mysql'),
                'user_id' => get_current_user_id(),
                'user_login' => wp_get_current_user()->user_login,
                'event_type' => $event_type,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];
            
            // Store in database or log file
            $log_option = get_option('wf_settings_audit_log', []);
            $log_option[] = $log_entry;
            
            // Keep only last 100 entries to prevent database bloat
            if (count($log_option) > 100) {
                $log_option = array_slice($log_option, -100);
            }
            
            update_option('wf_settings_audit_log', $log_option);
        }
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

// Helper function to get attachment details
function wf_settings_get_attachment($id) {
    $instance = WF_Settings_Framework::instance();
    return $instance->get_attachment_details($id);
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
