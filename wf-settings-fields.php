<?php
// wf-settings-fields.php
// Define your settings fields here as an array. This file is loaded by the main plugin.

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
        "placeholder" => "Enter text here",
        "required" => true,
        "description" => "Enter a text value between 1 and 255 characters. This field is required.",
        "validation" => [
            "type" => "string",
            "minLength" => 1,
            "maxLength" => 255
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
        "value" => null,
        "required" => true,
        "description" => "Choose an option from the dropdown menu. This field is required."
    ],
    [
        "id" => "textarea-id",
        "label" => "Textarea Label",
        "type" => "textarea",
        "value" => null,
        "placeholder" => "Enter text here",
        "required" => false,
        "description" => "Enter detailed text up to 500 characters. This field supports multiple lines.",
        "validation" => [
            "type" => "string",
            "minLength" => 0,
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
        "description" => "Enter a number between 0 and 100. This field is optional."
    ],
    [
        "id" => "file-id",
        "label" => "File Upload Label",
        "type" => "file",
        "value" => null,
        "required" => false,
        "accept" => ["image/*"],
        "description" => "Upload an image file. Supported formats: JPG, PNG, GIF, etc."
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
    ]
];
