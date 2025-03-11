<?php
if (!defined('ABSPATH')) {
  exit;
}

// Adds plugin settings page to the WP admin menu
//
function vvt_add_admin_menu() {
  add_menu_page(
    'VeriPAYE JSON Validation Tool',
    'VeriPAYE JSON Validation Tool',
    'manage_options',
    'vvt-settings',
    'vvt_settings_page',
    'dashicons-editor-code'
  );
}
add_action('admin_menu', 'vvt_add_admin_menu');

// Allows the form below to store a variable in the DB settings table
//
function vvt_settings_init() {
  register_setting('vvt_settings', 'vvt_json_schema_code');
}
add_action('admin_init', 'vvt_settings_init');

// The admin settings UI for the plugin
//
function vvt_settings_page(){
?>
<div class="wrap">
  <h1>VeriPAYE JSON Validation Tool Settings</h1>
  <form action="options.php" method="post">
    <?php
    settings_fields('vvt_settings');
    do_settings_sections('vvt_settings');
    ?>
    <h2>JSON Schema</h2>
    <textarea name="vvt_json_schema_code" rows="10" cols="70" class="large-text code"><?php echo esc_textarea(get_option('vvt_json_schema_code')); ?></textarea>

    <p class="description">
      Note: This is the schema users will validate thier JSON data against.
    </p>
    <?php submit_button('Save Schema'); ?>
  </form>
</div>
<?php
}
