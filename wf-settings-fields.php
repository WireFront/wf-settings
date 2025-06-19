<?php
/**
 * Settings fields configuration for Wirefront Settings Framework
 * Define your settings fields here as an array. This file is loaded by the main plugin.
 *
 */

// Include data sources for dynamic field options
require_once plugin_dir_path(__FILE__) . 'wf-settings-data-sources.php';

return [
    // Page configuration (special fields for page settings)
    "page_title" => "My Plugin Settings",
    "page_description" => "Configure your plugin options below. These settings will be saved and applied across your website.",
    
    // Regular fields
    [
        "id" => "textbox-id",
        "label" => "Textbox Label",
        "type" => "textbox",
        "value" => null,
        "placeholder" => "Enter at least 3 characters",
        "required" => true,
        "description" => "Enter a text value between 3 and 255 characters. This field is required.",
        "validation" => [
            "type" => "string",
            "minLength" => 3,
            "maxLength" => 255
        ]
    ],
    [
        "id" => "email-id",
        "label" => "Email Address",
        "type" => "textbox",
        "value" => null,
        "placeholder" => "Enter a valid email address",
        "required" => true,
        "description" => "Enter a valid email address. This field is required.",
        "validation" => [
            "type" => "email"
        ]
    ],
    [
        "id" => "url-id", 
        "label" => "Website URL",
        "type" => "textbox",
        "value" => null,
        "placeholder" => "https://example.com",
        "required" => false,
        "description" => "Enter a valid URL starting with http:// or https://",
        "validation" => [
            "type" => "url"
        ]
    ],
    [
        "id" => "checkbox-id",
        "label" => "Checkbox Label",
        "type" => "checkbox",
        "value" => false,
        "required" => false,
        "description" => "Check this option to enable or disable this feature."
    ],
    [
        "id" => "radio-id",
        "label" => "Radio Button Label",
        "type" => "radio",
        "options" => [
            [ "value" => "option1", "label" => "Option 1" ],
            [ "value" => "option2", "label" => "Option 2" ]
        ],
        "value" => null,
        "required" => true,
        "description" => "Select one of the available options. This selection is required."
    ],
    [
        "id" => "select-id",
        "label" => "Select Dropdown Label",
        "type" => "select",
        "options" => [
            [ "value" => "option1", "label" => "Option 1" ],
            [ "value" => "option2", "label" => "Option 2" ]
        ],
        "value" => "option1",
        "required" => true,
        "description" => "Choose an option from the dropdown menu. This field is required."
    ],
    [
        "id" => "textarea-id",
        "label" => "Textarea Label",
        "type" => "textarea",
        "value" => null,
        "placeholder" => "Enter text here",
        "required" => true,
        "description" => "Enter detailed text up to 500 characters. This field supports multiple lines.",
        "validation" => [
            "type" => "textarea",
            "minLength" => 3,
            "maxLength" => 500
        ]
    ],
    [
        "id" => "date-id",
        "label" => "Date Picker Label",
        "type" => "date",
        "value" => null,
        "required" => true,
        "description" => "Select a date using the date picker. This field is required."
    ],
    [
        "id" => "number-id",
        "label" => "Number Input Label",
        "type" => "number",
        "value" => null,
        "min" => 0,
        "max" => 100,
        "required" => false,
        "description" => "Enter a number between 0 and 100. This field is optional.",
        "validation" => [
            "type" => "number",
            "min" => 0,
            "max" => 100
        ]
    ],
    [
        "id" => "file-id",
        "label" => "Image Upload",
        "type" => "file",
        "value" => null,
        "required" => true,
        "accept" => ["image/*"],
        "description" => "Upload an image file using WordPress media library. The field will store the attachment ID which can be used to retrieve file details. Supported formats: JPG, PNG, GIF, etc."
    ],
    [
        "id" => "color-id",
        "label" => "Color Picker Label",
        "type" => "color",
        "value" => null,
        "required" => false,
        "description" => "Choose a color using the color picker. Click to open the color selection tool."
    ],
    [
        "id" => "hidden-id",
        "label" => "Hidden Input Label",
        "type" => "hidden",
        "value" => null,
        "required" => false,
        "description" => "This is a hidden field that stores data but is not visible to users."
    ],
    [
        "id" => "slider-id",
        "label" => "Slider Input Label",
        "type" => "slider",
        "value" => null,
        "min" => 0,
        "max" => 100,
        "step" => 1,
        "required" => false,
        "description" => "Drag the slider to select a value between 0 and 100."
    ],
    [
        "id" => "range-id",
        "label" => "Range Input Label",
        "type" => "range",
        "value" => null,
        "min" => 0,
        "max" => 100,
        "step" => 1,
        "required" => false,
        "description" => "Use the range slider to select a numeric value within the specified range."
    ],
    
    // Examples using dynamic data sources
    [
        "id" => "selected-page",
        "label" => "Select a Page",
        "type" => "select",
        "options" => wf_get_pages_data_source(),
        "value" => null,
        "required" => false,
        "description" => "Choose a page from your WordPress site."
    ],
    [
        "id" => "selected-posts",
        "label" => "Select Posts",
        "type" => "select",
        "options" => wf_get_posts_data_source('post', 10), // Get latest 10 posts
        "value" => null,
        "required" => false,
        "description" => "Choose from your latest blog posts."
    ],
    [
        "id" => "user-roles",
        "label" => "User Role Selection",
        "type" => "radio",
        "options" => wf_get_user_roles_data_source(),
        "value" => null,
        "required" => false,
        "description" => "Select a user role for this setting."
    ],
    [
        "id" => "categories",
        "label" => "Post Categories",
        "type" => "select",
        "options" => wf_get_categories_data_source(),
        "value" => null,
        "required" => false,
        "description" => "Choose a category from your site."
    ],
    [
        "id" => "menu-selection",
        "label" => "Navigation Menu",
        "type" => "select",
        "options" => wf_get_menus_data_source(),
        "value" => null,
        "required" => false,
        "description" => "Select a navigation menu from your site."
    ],
    [
        "id" => "post-type",
        "label" => "Post Type",
        "type" => "radio",
        "options" => wf_get_post_types_data_source(),
        "value" => null,
        "required" => false,
        "description" => "Choose a post type from your WordPress installation."
    ],
    [
        "id" => "country",
        "label" => "Country Selection",
        "type" => "select",
        "options" => wf_get_countries_data_source(),
        "value" => null,
        "required" => false,
        "description" => "Select your country from the list."
    ],
    [
        "id" => "document-upload",
        "label" => "Document Upload",
        "type" => "file",
        "value" => null,
        "required" => true,
        "accept" => ["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"],
        "description" => "Upload a document file (PDF, DOC, DOCX). Returns the WordPress attachment ID for use in your application."
    ],
    [
        "id" => "any-file-upload",
        "label" => "Any File Upload",
        "type" => "file",
        "value" => null,
        "required" => false,
        "description" => "Upload any type of file using WordPress media library. The attachment ID will be saved and can be used to retrieve file information."
    ],
];
