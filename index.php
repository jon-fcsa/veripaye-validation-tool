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
    <script src="./formatter/dist/json-formatter.umd.js"></script>
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

        // Format the input so highlighting makes sense
        var formatted_json = JSON.stringify(parseJSON, undefined, 4);

        $.ajax({
            type: "POST",
            url: "index.php?validate_json",
            cache: false,
            data:{
                "json_data": json_data
            },
            success: function(data){
                var formatted_json;
                data = JSON.parse(data);

                // Format the input so syntax highlighting makes sense - this happenes here so valid condition has something to work with
                formatted_json = JSON.stringify(parseJSON, undefined, 4);
                console.log('formatted_json', formatted_json);

                if(data.error_path == false){
                    output(data.error_msg);
                }else{

                    // Edge-case of ther being an issue with the root node, handle diffrently
                    // Just highlight the first line
                    if(data.error_path == "/"){

                      var json_lines_arr = split_lines(formatted_json)
                      json_lines_arr[0] = '<div class="highlight">'+json_lines_arr[0]+'</div>';

                    }else{

                        // Use the error path to highlight the JSON error location
                        var path_targets = data.error_path.split("/");

                        // converet html entites back to what they were so matching doesn't break.
                        for(var i = 0; i < path_targets.length; i++){
                            path_targets[i] = decodeURIComponent(path_targets[i]);
                        }
                        console.log('path_targets', path_targets);

                        // Figure out of the target were about to mark is a number, because if it is, it's an array index and marking it will destroy the object
                        // So instead mark its parent if thats not a number otherwise loop back till we find something we can mark
                        var target_offset = 1;
                        while(!isNaN(path_targets[path_targets.length-target_offset])){
                            path_targets.pop();
                            target_offset++;
                        }

                        // Mark the error ion the json obj - eval makes sense here...
                        //
                        var marked_path_targets = path_targets;
                        var target_str = path_targets.join('"]["').substring(2); // remove leading .

                        marked_path_targets[marked_path_targets.length-1] = marked_path_targets[marked_path_targets.length-1]+'¬';
                        var marked_target_str = marked_path_targets.join('"]["').substring(2); // remove leading .
                        var eval_str= 'parseJSON'+marked_target_str+'"] = parseJSON'+target_str+'"]';

                        // Mark the error
                        console.log("E1 ",eval_str);
                        eval(eval_str);

                        // Delete the origional
                        console.log("E2 ",'delete parseJSON'+target_str+'"]');
                        eval('delete parseJSON'+target_str+'"]');
                        //
                        //---------------------

                        // Need to format again since we've added the marker
                        formatted_json = JSON.stringify(parseJSON, undefined, 4);
                        var json_lines_arr = split_lines(formatted_json)

                        for(var i = 0; i < json_lines_arr.length; i++){
                            // console.log('Checking '+json_lines_arr[i]);
                            if(json_lines_arr[i].includes('¬')){
                                json_lines_arr[i] = '<div class="highlight">'+json_lines_arr[i]+'</div>';
                                break;
                            }
                        }
                    }

                    formatted_json = join_lines(json_lines_arr);
                    output(data.error_msg);
                }

                if(data.error_msg == "Valid"){
                    $('#json_container').css('background-color', '#284028');

                    // remove the error flag if it exists - were done
                    if(formatted_json.includes('¬')){
                        formatted_json = formatted_json.replace('¬','');
                    }
                }

                $('#json_data').empty();
                $('#json_data').html(formatted_json);

                // Scroll to the error
                if(data.error_msg != 'Valid' && document.getElementsByClassName("highlight")[0] != undefined){
                    var topPos = document.getElementsByClassName("highlight")[0].offsetTop;
                    document.getElementById('json_data').scrollTop = topPos - 20 - document.getElementById('json_data').offsetTop;
                }

            } // end success handler
        });


    });

    function split_lines(str){
      return str.split(/\r?\n/);
    }
    function join_lines(arr){
      return arr.join("\n");
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
