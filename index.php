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
$validator->resolver()->registerFile(
    'http://api.example.com/profile.json',
    './test_schema.json'
);




if(isset($_GET['validate_json'])){

  $data = $_POST['json_data'];

  // "location": {
  //     "country": "US",
  //     "address": "Sesame Street, no. 5"
  // },

  $data = <<<'JSON'
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
              "value": 75
          }
      ]
  }
  JSON;

  // Decode $data
  $data = json_decode($data);

  /** @var ValidationResult $result */
  $result = $validator->validate($data, 'http://api.example.com/profile.json');

  if ($result->isValid()) {
      $is_valid_result = "Valid";
  } else {
      // Print errors
      $is_valid_result = (new ErrorFormatter())->format($result->error());
  }

  $return_data['is_valid'] = $is_valid_result;
  // $return_data['formatted_json'] = json_decode($_POST['json_data']);

  echo json_encode($return_data, JSON_PRETTY_PRINT);

  die();
}
?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title>Veripaye JSON schema validation tool</title>

    <style>
        *{padding:0; margin:0;}
        body{background-color:1f1f1f; color-scheme: dark;}
        input{padding:5px 10px;}
        button{padding:5px 10px;}
        button:hover{cursor:pointer;}
        a{color:white;}

        .spacer{height:100px;}
        .main_content{margin:15px auto; width:80%; max-width:900px; border:1px solid grey; color:white;}
        .content_title{position:relative; top:-10px; left:10px; background-color:1f1f1f; color:grey;}
        .content_title:after { content:"]"; }
        .content_title:before { content:"["; }
        .content{padding:20px;}
        .content a{line-height:18px;}
        .encode_msg_container, .decode_msg_container{display:none;}
        .encode_msg, .decode_msg{padding:5px 10px;}
        .error_msg{background-color:#8a0101;}
        .warn_msg{background-color:#8a4301;}
        .ok_msg{background-color:#0c8a01;}
        .footer_content{margin:0 auto; width:80%; max-width:600px; text-align:right;}

        #json_container{
          border:1px solid white; background-color:#2b2a33; color:white; font-size:12px;
        }
        #json_data{
          /* white-space: nowrap; */
          overflow:scroll;
          width:100%; height:500px;
        }
        #short_url{ width:calc(100% - 160px); margin-right:10px; }
        #validate_json{ width:250px; }

        #encoded_short_url{ width:calc(100% - 160px); margin-right:10px; }
        #decode_url_btn{ width:150px; }
        #decoded_short_url{ width:100%; }
        #output{margin:0 auto; width:100%; height:200px; overflow-y:scroll; border-top:1px solid grey; border-bottom:1px solid grey;}
    </style>
</head>
<body>
<div class="spacer"></div>

<script>
$(function(){

    $('#validate_json').click(function(){
      var json_data = $('#json_data').text();

      // Always format the JSON
      var parseJSON = JSON.parse(json_data);
      var pretty = JSON.stringify(parseJSON, undefined, 4);
      var ugly = document.getElementById('json_data').innerHTML;

      console.log('json_data',pretty);

      $.ajax({
          type: "POST",
          url: "index.php?validate_json",
          cache: false,
          data: {
            "json_data": json_data
          },
          success: function(data){
              $("#output").empty();
              $("#output").append(data.is_valid);

              // Format the input so syntax highlighting makes sense
              document.getElementById('json_data').innerHTML = "";
              document.getElementById('json_data').innerHTML = pretty;
          }
      });
    });

});
</script>

<div class="main_content">
    <span class="content_title"> Veripaye JSON schema validation tool</span>
    <div class="content">
        <button id="validate_json">Validate JSON Sechema</button><br><br>
        <div id="json_container">
          <pre id="json_data" placeholder="Enter JSON data" contenteditable></pre>
        </div>
        <br><br><hr><br>
        Output
        <div id="output"></div>
    </div>
</div>

</body>
</html>
