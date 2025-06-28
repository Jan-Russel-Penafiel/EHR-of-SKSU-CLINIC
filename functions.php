<php?
function enqueue_form_styles_scripts() {
    wp_enqueue_style('form-style', get_template_directory_uri() . '/css/form-style.css', array(), '1.0.0', 'all');
    wp_enqueue_script('form-scripts', get_template_directory_uri() . '/js/form-scripts.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('image-preview', get_template_directory_uri() . '/js/image-preview.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_form_styles_scripts');

function load_async_scripts($tag, $handle) {
    $async_scripts = array('form-scripts'); // Add your script handles
    if (in_array($handle, $async_scripts)) {
        return str_replace(' src', ' async="true" src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'load_async_scripts', 10, 2);
