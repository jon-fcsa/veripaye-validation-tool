<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("./vendor/autoload.php");
use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    Errors\ErrorFormatter,
};

// Create a new validator
$validator = new Validator();

// Register our schema
$validator->resolver()->registerFile( 'http://api.example.com/profile.json', dirname(__FILE__).'/veripaye_schema.json');


// Handle POST request.
//
if(isset($_GET['validate_json'])){

  $data = $_POST['json_data'];
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
?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title>Veripaye JSON schema validation tool</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
<div class="spacer"></div>

<script>
$(function(){

    $('#validate_json').click(function(){
        $('#json_container').css('background-color', '#2b2a33');
        var json_data = $('#json_data').text();

        if(!json_data){
          console.log();
          output('No JSON data was entered');
          return;
        }

        // Catch any syntax errors before looking for schema errors
        try{
          var parseJSON = JSON.parse(json_data);
        }catch(e){
          if((e instanceof SyntaxError)){
            $('#json_container').css('background-color', '#520e0e');
            output(e.name+"<br>"+e.message+"<br><br>Check for unclosed brackets, trailing commas and missing values.");
          }
          return;
        }

        // Format the input so syntax highlighting makes sense
        var formatted_json = JSON.stringify(parseJSON, undefined, 4);

        $.ajax({
            type: "POST",
            url: "index.php?validate_json",
            cache: false,
            data: {
              "json_data": json_data
            },
            success: function(data){
                data = JSON.parse(data);

                if(data.error_path == false){
                  output(data.error_msg);
                }else{

                  // Use the error path to highlight the JSON error location
                  console.log(data.error_path);
                  var path_targets = data.error_path.split("/");
                  console.log("PT", path_targets);

                  var path_target_count = path_targets.length - 1; // The target count at which we'll highlight an error
                  var current_target_count = 0;
                  var path_targets_index = 1; // start at 1 to skip the first empty string element split() creates

                  var detected_error_array_index;
                  var detected_error_array_count = 0;
                  var brackets_open = false;

                  var json_lines_arr = split_lines(formatted_json)
                  for(var i = 0; i < json_lines_arr.length; i++){

                      // Instead of constantly iterating over the target path
                      // Compare the current target path item against each line - looking for a match
                      // If one is found increase the count and delete the match
                      //
                      // If the path contains a number then its an element in an array that has an issue
                      // Handle this differently as opposed to the usual substring check
                      //
                      if(isNumeric(path_targets[path_targets_index])){

                          // Bracket counting for now - one level deep
                          detected_error_array_index = Number(path_targets[path_targets_index]);

                          if(json_lines_arr[i].includes("{")){
                            brackets_open = true;
                          }
                          if(brackets_open && json_lines_arr[i].includes("}")){
                            detected_error_array_count++;
                            brackets_open = false;
                          }

                          if(detected_error_array_index == detected_error_array_count){
                              console.log('ARR item located');

                              current_target_count++;
                              path_targets.splice(path_targets_index, 1);

                              i++; // because we want to highlight the next item as thats the actual opening bracket we care about
                          }

                          console.log('NUMBER', detected_error_array_index, 'Count', detected_error_array_count, ' brackets open =', brackets_open);
                          console.log('Checking '+json_lines_arr[i]+' for '+path_targets[path_targets_index]);

                      }else{

                          if(json_lines_arr[i].includes(path_targets[path_targets_index])){
                              console.log('MATCHED '+json_lines_arr[i]+' for '+path_targets[path_targets_index]);

                              current_target_count++;
                              path_targets.splice(path_targets_index, 1);
                              // path_targets_index++;
                          }
                          console.log('Checking '+json_lines_arr[i]+' for '+path_targets[path_targets_index]);
                      }


                      if(current_target_count == path_target_count){
                        json_lines_arr[i] = '<div class="highlight">'+json_lines_arr[i]+'</div>';
                        // json_lines_arr[i] = '<mark>'+json_lines_arr[i]+'</mark>';
                        break;
                      }
                  }


                  var joined = join_lines(json_lines_arr);
                  // console.log();
                  // console.log(joined);

                  formatted_json = joined;
                  output(data.error_msg);
                }

                if(data.error_msg == "Valid"){
                  $('#json_container').css('background-color', '#284028');
                }


                $('#json_data').empty();
                $('#json_data').html(formatted_json);
            }
        });
    });

    function split_lines(str){
      return str.split(/\r?\n/);
    }
    function join_lines(arr){
      return arr.join("\n");
    }

    function isNumeric(str) {
      if (typeof str != "string") return false // we only process strings!
      return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
             !isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
    }

    function output(msg){
        $("#output").empty();
        $("#output").append(msg);
    }

});
</script>

<div class="main_content">
    <span class="content_title"> Veripaye JSON validation tool</span>
    <div class="content">
        <button id="validate_json">Validate JSON syntax and schema</button><br><br>
        <div id="json_container">
          <pre id="json_data" placeholder="Enter JSON data" contenteditable></pre>
        </div>
        <br>
        Output
        <div id="output_container">
          <div id="output"></div>
        </div>
    </div>
</div>

</body>
</html>
