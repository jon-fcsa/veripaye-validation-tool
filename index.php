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

        // remove the error flag if it exists (reset for revalidation)
        if(json_data.includes('¬')){
            json_data = json_data.replace('¬','');
        }

        if(!json_data){
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

        // // Format the input so highlighting makes sense
        // var formatted_json = JSON.stringify(parseJSON, undefined, 4);

        $.ajax({
            type: "POST",
            url: "index.php?validate_json",
            cache: false,
            data:{
                "json_data": json_data
            },
            success: function(data){
                data = JSON.parse(data);

                if(data.error_path == false){
                    output(data.error_msg);
                }else{
                    // Use the error path to highlight the JSON error location
                    var path_targets = data.error_path.split("/");
                    console.log("PT1", path_targets);

                    // converet html entites back to what they were so matching doesn't break.
                    for(var i = 0; i < path_targets.length; i++){
                        path_targets[i] = decodeURIComponent(path_targets[i]);
                    }
                    console.log("PT2", path_targets);


                    // Mark the error ion the json obj
                    //
                    var marked_path_targets = path_targets;
                    var target_str = path_targets.join('"]["').substring(2); // remove leading .

                    marked_path_targets[marked_path_targets.length-1] = marked_path_targets[marked_path_targets.length-1]+'¬';
                    var marked_target_str = marked_path_targets.join('"]["').substring(2); // remove leading .
                    var eval_str= 'parseJSON'+marked_target_str+'"] = parseJSON'+target_str+'"]';

                    // Mark the error
                    eval(eval_str);

                    // Delete the origional
                    eval('delete parseJSON'+target_str+'"]');
                    //
                    //---------------------


                    // Format the input so syntax highlighting makes sense
                    var formatted_json = JSON.stringify(parseJSON, undefined, 4);
                    var json_lines_arr = split_lines(formatted_json)

                    for(var i = 0; i < json_lines_arr.length; i++){

                        // console.log('C Checking '+json_lines_arr[i]);

                        if(json_lines_arr[i].includes('¬')){
                            json_lines_arr[i] = '<div class="highlight">'+json_lines_arr[i]+'</div>';
                            break;
                        }
                    }

                    formatted_json = join_lines(json_lines_arr);
                    output(data.error_msg);
                }

                if(data.error_msg == "Valid"){
                  $('#json_container').css('background-color', '#284028');
                }

                $('#json_data').empty();
                $('#json_data').html(formatted_json);

                // Scroll to the error
                if(data.error_msg != 'Valid' && document.getElementsByClassName("highlight")[0] != undefined){
                    var topPos = document.getElementsByClassName("highlight")[0].offsetTop;
                    document.getElementById('json_data').scrollTop = topPos - 20 - document.getElementById('json_data').offsetTop;
                }

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
