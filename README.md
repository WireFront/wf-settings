# wf-settings

A lightweight developer-first WordPress settings framework that lets you define plugin options using a structured array. Includes all essential input types—textbox, checkbox, radio, select, **file upload with WordPress media library integration**, sliders, and more. Designed for fast prototyping and reuse across projects. Easily retrieve saved values. Perfect for teams building modular, consistent, and scalable plugins.

## Changelog

### Version 0.2.0 (June 17, 2025)
- ✨ **NEW**: WordPress Media Library Integration for file uploads
- ✨ **NEW**: File type restrictions with `accept` parameter
- ✨ **NEW**: Visual file preview with thumbnails
- ✨ **NEW**: One-click file removal functionality
- ✨ **NEW**: Attachment ID storage for optimal WordPress integration
- ✨ **NEW**: Helper function `wf_settings_get_attachment()` to retrieve file details
- 🛠️ **IMPROVED**: Enhanced error handling and validation
- 🛠️ **IMPROVED**: Better responsive design for file upload fields
- 🛠️ **IMPROVED**: Added comprehensive documentation and examples

### Version 0.1.0 (Initial Release)
- 🎉 Initial release with basic field types
- ⚡ Support for textbox, checkbox, radio, select, textarea, date, number, color, slider, and range fields
- 🎨 Modern, responsive UI design
- 📝 Comprehensive validation system
- 🔧 Developer-friendly helper functions

## File Upload Functionality

The framework now includes enhanced file upload capabilities using WordPress's built-in media library. Here's how to use it:

### Basic File Upload Field

```php
[
    "id" => "my-file",
    "label" => "Upload File",
    "type" => "file",
    "value" => null,
    "required" => false,
    "accept" => ["image/*"], // Optional: restrict file types
    "description" => "Upload a file using WordPress media library."
]
```

### File Type Restrictions

You can restrict file types using the `accept` parameter:

```php
// Images only
"accept" => ["image/*"]

// Documents only
"accept" => ["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"]

// Multiple specific types
"accept" => ["image/jpeg", "image/png", "application/pdf"]
```

### Retrieving File Information

The file upload field stores the WordPress attachment ID. You can retrieve file details using:

```php
// Get just the attachment ID
$attachment_id = wf_settings_val('my-file');

// Get complete attachment details
$file_details = wf_settings_get_attachment($attachment_id);

// The file_details array contains:
// - id: Attachment ID
// - title: File title
// - url: Direct URL to the file
// - filename: Original filename
// - type: MIME type
// - ext: File extension
// - metadata: Additional file metadata
// - alt_text: Alt text (for images)
// - caption: File caption
// - description: File description
```

### Example Usage

```php
$logo_id = wf_settings_val('company-logo');
if ($logo_id) {
    $logo_details = wf_settings_get_attachment($logo_id);
    echo '<img src="' . esc_url($logo_details['url']) . '" alt="' . esc_attr($logo_details['alt_text']) . '">';
}
```

### Complete Example: Company Logo Upload

Here's a complete example showing how to create a logo upload field and display it on your website:

**1. Define the field in your settings:**

```php
[
    "id" => "company-logo",
    "label" => "Company Logo",
    "type" => "file",
    "value" => null,
    "required" => false,
    "accept" => ["image/*"],
    "description" => "Upload your company logo. Recommended size: 200x80 pixels."
]
```

**2. Display the logo in your theme or plugin:**

```php
function display_company_logo() {
    $logo_id = wf_settings_val('company-logo');
    
    if (!$logo_id) {
        return;
    }
    
    $logo_details = wf_settings_get_attachment($logo_id);
    
    if ($logo_details) {
        // You can choose different image sizes
        $logo_url = wp_get_attachment_image_url($logo_id, 'medium'); // or 'thumbnail', 'large', 'full'
        
        echo '<div class="company-logo">';
        echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($logo_details['alt_text'] ?: 'Company Logo') . '" />';
        echo '</div>';
    }
}

// Use it in your template
display_company_logo();
```

**3. Advanced usage with responsive images:**

```php
function display_responsive_logo() {
    $logo_id = wf_settings_val('company-logo');
    
    if ($logo_id) {
        // WordPress will automatically generate responsive image markup
        echo wp_get_attachment_image($logo_id, 'medium', false, [
            'alt' => 'Company Logo',
            'class' => 'company-logo responsive-logo'
        ]);
    }
}
```

### Features Summary

✅ **WordPress Media Library Integration** - Uses the native WordPress media selector  
✅ **File Type Restrictions** - Limit uploads to specific file types  
✅ **Attachment ID Storage** - Stores WordPress attachment IDs for optimal integration  
✅ **Preview Functionality** - Shows selected files with thumbnails  
✅ **Easy Removal** - One-click file removal  
✅ **Error Handling** - Robust error handling and validation  
✅ **Responsive Design** - Works on desktop and mobile devices  
✅ **Developer Friendly** - Easy to retrieve and use uploaded files
