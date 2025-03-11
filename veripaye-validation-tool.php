<?php
 /*
  * Plugin Name: Veripaye JSON Validation Tool
  * Author:      Jon Wright
  * Description: Validate JSON data against a pre-defined schema
  */

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('VeripayeValidationToolPlugin')){

    class VeripayeValidationToolPlugin{
        public function __construct(){
              define('VVT_PLUGIN_DIR', plugin_dir_path(__FILE__));

              require_once(VVT_PLUGIN_DIR.'vendor/autoload.php');
        }

        public function init(){
            include_once(VVT_PLUGIN_DIR.'includes/vvt-admin-settings.php');
            include_once(VVT_PLUGIN_DIR.'includes/vvt-widget.php');
        }
    }

    $validation_plugin = new VeripayeValidationToolPlugin;
    $validation_plugin->init();
}
