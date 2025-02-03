<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("./vendor/autoload.php");
// use Swaggest\JsonSchema\Schema;

use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    Errors\ErrorFormatter,
};

// Create a new validator
$validator = new Validator();

// Register our schema
$validator->resolver()->registerFile( 'http://api.example.com/profile.json', './test_schema.json');


if(isset($_GET['validate_json'])){

  $data = $_POST['json_data'];

  // Decode $data
  $data = json_decode($data);

  // ValidationResult $result
  $result = $validator->validate($data, 'http://api.example.com/profile.json');

  if($result->isValid()){
      $return_val['error_path'] = false;
      $return_val['error_msg'] = 'Valid';
  }else{
      $is_valid_result = (new ErrorFormatter())->format($result->error());

      reset($is_valid_result);
      $return_val['error_path'] = key($is_valid_result);
      $return_val['error_msg'] = current($is_valid_result);
  }

  // echo "<pre>";
  // var_dump($is_valid_result);

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
        var json_data = $('#json_data').text();

        if(!json_data){
          console.log();
          output('No JSON data was entered');
          return;
        }

        // Format the input so syntax highlighting makes sense
        var parseJSON = JSON.parse(json_data);
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


                  // console.log();
                  // console.log(formatted_json);
                  // console.log();

                  var json_lines_arr = split_lines(formatted_json)
                  for(var i = 0; i < json_lines_arr.length; i++){

                      if(i == 0){
                        json_lines_arr[i] = '<div class="highlight">'+json_lines_arr[i]+'</mark>';
                      }

                      // console.log(json_lines_arr[i]);
                  }
                  // console.log();


                  var joined = join_lines(json_lines_arr);
                  // console.log();
                  // console.log(joined);

                  output(data.error_msg);
                }


                $('#json_data').empty();
                $('#json_data').html(joined);
            }
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
    <span class="content_title"> Veripaye JSON schema validation tool</span>
    <div class="content">
        <button id="validate_json">Validate JSON Sechema</button><br><br>
        <div id="json_container">
          <pre id="json_data" placeholder="Enter JSON data" contenteditable>
{
    "name": "John Doe",
    "age": 31,
    "email": "john@example.com",
    "website": null,
    "location": {
        "country": "US",
        "address": false
    },
    "available_for_hire": true,
    "interests": ["php", "html", "css", "javascript", "programming", "web design"],
    "skills": [
        {
            "name": "HTML",
            "value": 100
        },
        {
            "name": "PHP",
            "value": 55
        },
        {
            "name": "CSS",
            "value": 99.5
        },
        {
            "name": "JavaScript",
            "value": true
        }
    ]
}
          </pre>
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
