<?php
if(!defined('ABSPATH')){
    exit;
}

use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    Errors\ErrorFormatter,
};


//
// This lets us put [vvt_widget] in a post that will call this function
//
function show_vvt_widget(){
  //
  // Using output buffer because just including the file doesnt render it
  // The form also inclued JS and CSS
  //
  ob_start();
      include(VVT_PLUGIN_DIR.'/includes/vvt-widget-form.php');
  return ob_get_clean();
}
add_shortcode('vvt_widget', 'show_vvt_widget');


//
// Creating an endpoint for the frontend form to call
//
function create_vvt_rest_endpoint(){
    register_rest_route('vvt', 'validate_json', [
        'methods' => 'post',
        'callback' => 'validate_json'
    ]);
}
add_action('rest_api_init', 'create_vvt_rest_endpoint');

// The actual endpoint function
//
function validate_json($data){

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  $params = $data->get_params();
  $data = $params["json_data"];

  // Create a new validator
  $validator = new Validator();

  // Get the schema from the database - update current schema file
  file_put_contents(dirname(__FILE__).'/current_schema.json', get_option('vvt_json_schema_code'));

  // Register our schema
  $validator->resolver()->registerFile('http://api.example.com/profile.json', dirname(__FILE__).'/current_schema.json');

  // Handle POST request.
  //
  // $data = $_POST['json_data'];
  $data = json_decode($data);
  $result = $validator->validate($data, 'http://api.example.com/profile.json');

  if($result->isValid()){
      $return_val['error_path'] = false;
      $return_val['error_msg'] = 'Valid';
  }else{
      $is_valid_result = (new ErrorFormatter())->format($result->error()); // Get error details for the front-end to use

      reset($is_valid_result);
      $return_val['error_path'] = key($is_valid_result);
      $return_val['error_msg'] = current($is_valid_result);
  }

  echo json_encode($return_val);
  die();
}
