<?php
// wf-settings-fields.php
// Define your settings fields here as an array. This file is loaded by the main plugin.

return [
    [
        "id" => "textbox-id",
        "label" => "Textbox Label",
        "type" => "textbox",
        "value" => null,
        "placeholder" => "Enter text here",
        "required" => true,
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
        "required" => false
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
        "required" => true
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
        "required" => true
    ],
    [
        "id" => "textarea-id",
        "label" => "Textarea Label",
        "type" => "textarea",
        "value" => null,
        "placeholder" => "Enter text here",
        "required" => false,
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
        "required" => true
    ],
    [
        "id" => "number-id",
        "label" => "Number Input Label",
        "type" => "number",
        "value" => null,
        "min" => 0,
        "max" => 100,
        "required" => false
    ],
    [
        "id" => "file-id",
        "label" => "File Upload Label",
        "type" => "file",
        "value" => null,
        "required" => false,
        "accept" => ["image/*"]
    ],
    [
        "id" => "color-id",
        "label" => "Color Picker Label",
        "type" => "color",
        "value" => null,
        "required" => false
    ],
    [
        "id" => "hidden-id",
        "label" => "Hidden Input Label",
        "type" => "hidden",
        "value" => null,
        "required" => false
    ],
    [
        "id" => "slider-id",
        "label" => "Slider Input Label",
        "type" => "slider",
        "value" => null,
        "min" => 0,
        "max" => 100,
        "step" => 1,
        "required" => false
    ],
    [
        "id" => "range-id",
        "label" => "Range Input Label",
        "type" => "range",
        "value" => null,
        "min" => 0,
        "max" => 100,
        "step" => 1,
        "required" => false
    ]
];
